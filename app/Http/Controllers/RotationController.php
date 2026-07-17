<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\Rotation;
use App\Models\RotationObjective;
use App\Models\Rvehicule;
use App\Services\GpsApiService;
use App\Services\ReportService;
use App\Services\RotationCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Jobs\CalculateVehicleRotations;

class RotationController extends Controller
{
     public function __construct(
        private readonly RotationCalculatorService $calculator,
        private readonly GpsApiService $gpsApi
    ) {}

    public function dashboard(Request $request)
    {
        $circuits = Circuit::where('active', true)->orderBy('name')->get();

        // Période par défaut : mois courant
        $filterType  = $request->get('filter_type', 'month'); // month | week | range
        $circuitId   = $request->get('circuit_id');
        $year        = (int) $request->get('year', now()->year);
        $month       = (int) $request->get('month', now()->month);
        $dateFrom    = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo      = $request->get('date_to', now()->endOfMonth()->toDateString());

        // Construire la plage de dates selon le filtre
        [$periodStart, $periodEnd] = match($filterType) {
            'month' => [
                Carbon::createFromDate($year, $month, 1)->startOfMonth(),
                Carbon::createFromDate($year, $month, 1)->endOfMonth(),
            ],
            'range' => [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        // Query de base
        // $query = Rotation::with(['rvehicule', 'circuit'])
        //     ->whereBetween('counted_month', [$periodStart, $periodEnd]);
        $query = Rotation::with(['rvehicule', 'circuit'])
        ->whereBetween('counted_month', [
            $periodStart->format('Y-m'),
            $periodEnd->format('Y-m'),
        ]);

        if ($circuitId) {
            $query->where('circuit_id', $circuitId);
        }

        $rotations = $query->get();

        // Compteurs globaux
        $stats = [
            'total'       => $rotations->count(),
            'completed'   => $rotations->whereIn('status', ['completed', 'acceptable'])->count(),
            'cancelled'   => $rotations->where('status', 'cancelled')->count(),
            'in_progress' => $rotations->where('status', 'in_progress')->count(),
            'acceptable'  => $rotations->where('status', 'acceptable')->count(),
        ];

        // Évolution par jour
        $byDay = $rotations
            ->whereIn('status', ['completed', 'acceptable'])
            ->groupBy(fn($r) => Carbon::parse($r->started_at)->toDateString())
            ->map(fn($group) => $group->count())
            ->sortKeys();

        // Évolution par semaine
        $byWeek = $rotations
            ->whereIn('status', ['completed', 'acceptable'])
            ->groupBy(fn($r) => Carbon::parse($r->started_at)->weekOfYear)
            ->map(fn($group, $week) => [
                'week'  => 'S' . $week,
                'count' => $group->count(),
            ])
            ->values();

        // Par circuit (si vue globale)
        $byCircuit = $rotations
            ->whereIn('status', ['completed', 'acceptable'])
            ->groupBy('circuit_id')
            ->map(fn($group) => [
                'name'  => $group->first()->circuit->name ?? '?',
                'count' => $group->count(),
            ])
            ->values();

        return view('rotations.dashboard', compact(
            'circuits', 'stats', 'byDay', 'byWeek', 'byCircuit',
            'filterType', 'circuitId', 'year', 'month', 'dateFrom', 'dateTo',
            'periodStart', 'periodEnd'
        ));
    }
 
    public function index(Request $request)
    {
        $circuits = Circuit::where('active', true)->orderBy('name')->get();
        $vehicles = Rvehicule::orderBy('name')->get();

        $query = Rotation::with(['rvehicule', 'circuit'])
                    ->orderBy('circuit_id')
                    ->orderBy('duration_seconds', 'desc');
 
        if ($request->circuit_id) {
            $query->where('circuit_id', $request->circuit_id);
        }
        if ($request->vehicle_id) {
            $query->where('rvehicule_id', $request->vehicle_id);
        }
        if ($request->month) {
            $query->where('counted_month', $request->month);
        }
    
        // 2. Logique du Status (Par défaut : completed)
        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereIn('status', ['completed', 'acceptable']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $statsQuery = clone $query;
        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'completed'   => (clone $statsQuery)->whereIn('status', ['completed', 'acceptable'])->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
            'cancelled'   => (clone $statsQuery)->where('status', 'cancelled')->count(),
        ];    
        
        $rotations = $query->paginate(25)->withQueryString();
        
 
        return view('rotations.index', compact('rotations', 'circuits', 'vehicles', 'stats'));
    }
 

    public function show(Rotation $rotation)
    {
        $rotation->load(['rvehicule', 'circuit.legs', 'rotationLegs.circuitLeg']);
        $objective     = $rotation->circuit->currentObjective();
        $legObjectives = $objective?->leg_objectives ?? [];

        $allLegs       = $rotation->circuit->legs()->orderBy('order')->get();
        $completedLegs = $rotation->rotationLegs->groupBy('circuit_leg_id');

        // ── Construction des paires enter/leave par zone ──────────────────────────
        // Pour chaque enter_zone, on cherche le leave_zone correspondant
        // (même reference_id, ordre supérieur, pas encore pairé)
        $zonePairs     = [];  // [enter_leg_id => leave_leg_id]
        $pairedEnterIds = [];
        $pairedExitIds  = [];

        foreach ($allLegs as $leg) {
            if ($leg->event_type !== 'enter_zone') continue;
            if (in_array($leg->id, $pairedEnterIds))  continue;

            $leave = $allLegs->first(fn($l) =>
                $l->event_type === 'leave_zone' &&
                $l->reference_id == $leg->reference_id &&
                $l->order > $leg->order &&
                !in_array($l->id, $pairedExitIds)
            );

            if ($leave) {
                $zonePairs[$leg->id]  = $leave->id;
                $pairedEnterIds[]     = $leg->id;
                $pairedExitIds[]      = $leave->id;
            }
        }

        // ── Calcul durée réelle par zone (enter → leave) ──────────────────────────
        // $zoneActualSec = [];
        // foreach ($zonePairs as $enterId => $exitId) {
        //     $enterRl = $completedLegs->get($enterId);
        //     $exitRl  = $completedLegs->get($exitId);
        //     if ($enterRl && $exitRl) {
        //         $zoneActualSec[$enterId] = (int) $enterRl->occurred_at
        //             ->diffInSeconds($exitRl->occurred_at);
        //     }
        // }

        $zoneActualSec = [];
        foreach ($zonePairs as $enterId => $exitId) {
            $enterGroup = $completedLegs->get($enterId);
            $exitGroup  = $completedLegs->get($exitId);
            
            $firstEnter = $enterGroup?->first(fn($rl) => !$rl->wasSkippedByParent());
            $lastExit   = $exitGroup?->last(fn($rl) => !$rl->wasSkippedByParent());
            
            if ($firstEnter && $lastExit) {
                $zoneActualSec[$enterId] = (int) $firstEnter->occurred_at->diffInSeconds($lastExit->occurred_at);
            }
        }

        // ── Construction de l'arbre pour l'affichage ──────────────────────────────
        // Un "bloc zone" = zone parente avec ses sous-zones imbriquées.
        // On groupe les legs en blocs : chaque enter_zone principale ouvre un bloc,
        // les enter/leave de sous-zones (dont la zone BDD a un parent_id) sont imbriqués.

        $displayBlocks = $this->buildDisplayBlocks(
            $allLegs, $completedLegs, $zonePairs,
            $pairedEnterIds, $pairedExitIds,
            $zoneActualSec, $legObjectives
        );

        return view('rotations.show', compact(
            'rotation', 'objective', 'allLegs', 'completedLegs',
            'zonePairs', 'pairedEnterIds', 'pairedExitIds',
            'zoneActualSec', 'legObjectives', 'displayBlocks'
        ));
    }

    private function buildDisplayBlocks(
        $allLegs, $completedLegs, $zonePairs,
        $pairedEnterIds, $pairedExitIds,
        $zoneActualSec, $legObjectives
    ): array {
        $blocks          = [];
        $skipIds         = [];
        $processedGroups = []; // group_or déjà traités

        foreach ($allLegs as $leg) {
            if (in_array($leg->id, $skipIds)) continue;

            // ── Checkpoint ───────────────────────────────────────────────────────
            if ($leg->event_type === 'pass_checkpoint') {
                // $rl = $completedLegs->get($leg->id);
                $rl = $completedLegs->get($leg->id)?->first();
                $blocks[] = [
                    'type'    => 'checkpoint',
                    'leg'     => $leg,
                    'rl'      => ($rl && !$rl->wasSkippedByParent()) ? $rl : null,
                    'skipped' => $rl && $rl->wasSkippedByParent(),
                ];
                $skipIds[] = $leg->id;
                continue;
            }

            // ── leave_zone pairée → skip silencieux ──────────────────────────────
            if ($leg->event_type === 'leave_zone' && in_array($leg->id, $pairedExitIds)) {
                $skipIds[] = $leg->id;
                continue;
            }

            // ── enter_zone ───────────────────────────────────────────────────────
            if ($leg->event_type === 'enter_zone') {

                // Groupe OU déjà traité → skip
                if ($leg->group_or && isset($processedGroups[$leg->group_or])) {
                    $skipIds[] = $leg->id;
                    continue;
                }

                // Si ce leg fait partie d'un groupe OU, trouver celui qui a été validé
                $activeLeg = $leg;
                if ($leg->group_or) {
                    $slotLegs = $allLegs->where('group_or', $leg->group_or)
                                        ->where('event_type', 'enter_zone')
                                        ->values();

                    // Chercher le leg du slot qui a un RotationLeg réel (non skippé)
                    foreach ($slotLegs as $slotLeg) {
                        // $rl = $completedLegs->get($slotLeg->id);
                        $rl = $completedLegs->get($slotLeg->id)?->first();
                        if ($rl && !$rl->wasSkippedByParent()) {
                            $activeLeg = $slotLeg;
                            break;
                        }
                    }

                    // Marquer tous les legs du slot comme traités
                    foreach ($slotLegs as $slotLeg) {
                        $skipIds[] = $slotLeg->id;
                        // Marquer aussi leurs leave
                        $leaveId = $zonePairs[$slotLeg->id] ?? null;
                        if ($leaveId) $skipIds[] = $leaveId;
                    }

                    $processedGroups[$leg->group_or] = true;
                }

                // Construire le bloc avec le leg actif (validé ou premier du slot si aucun)
                $leaveLegId = $zonePairs[$activeLeg->id] ?? null;
                $leaveLeg   = $leaveLegId ? $allLegs->firstWhere('id', $leaveLegId) : null;

                // $enterRlRaw = $completedLegs->get($activeLeg->id);
                // $leaveRlRaw = $leaveLegId ? $completedLegs->get($leaveLegId) : null;

                // $enterRl = ($enterRlRaw && !$enterRlRaw->wasSkippedByParent()) ? $enterRlRaw : null;
                // $leaveRl = ($leaveRlRaw && !$leaveRlRaw->wasSkippedByParent()) ? $leaveRlRaw : null;
                $enterGroup  = $completedLegs->get($activeLeg->id);
                $leaveGroup  = $leaveLegId ? $completedLegs->get($leaveLegId) : null;

                $enterRlRaw  = $enterGroup?->first();
                $leaveRlRaw  = $leaveGroup?->first();

                $enterRl     = $enterGroup?->first(fn($rl) => !$rl->wasSkippedByParent());
                $leaveRl     = $leaveGroup?->last(fn($rl) => !$rl->wasSkippedByParent());

                $actualSec = $zoneActualSec[$activeLeg->id] ?? null;
                if ($enterRlRaw?->wasSkippedByParent() || $leaveRlRaw?->wasSkippedByParent()) {
                    $actualSec = null;
                }

                $rawTarget = $legObjectives[$activeLeg->id]
                    ?? $legObjectives[(string)$activeLeg->id]
                    ?? null;

                // Pour un slot OU, chercher la valeur sur n'importe quel leg du slot
                if ($rawTarget === null && $leg->group_or) {
                    $slotLegs = $allLegs->where('group_or', $leg->group_or)
                                        ->where('event_type', 'enter_zone')
                                        ->values();
                    foreach ($slotLegs as $slotLeg) {
                        $rawTarget = $legObjectives[$slotLeg->id]
                            ?? $legObjectives[(string)$slotLeg->id]
                            ?? null;
                        if ($rawTarget !== null) break;
                    }
                }

                $targetSec = ($rawTarget !== null && $rawTarget !== 'null') ? (int)$rawTarget : null;
                $ecart     = ($actualSec !== null && $targetSec !== null) ? $actualSec - $targetSec : null;

                $zone      = \App\Models\Zone::find($activeLeg->reference_id);
                $isSubZone = $zone && $zone->parent_id !== null;

                // ── Sous-zones ───────────────────────────────────────────────────
                $children = [];
                if ($leaveLeg) {
                    $innerLegs = $allLegs->filter(fn($l) =>
                        $l->order > $activeLeg->order &&
                        $l->order < $leaveLeg->order &&
                        $l->event_type === 'enter_zone' &&
                        !in_array($l->id, $skipIds)
                    );

                    foreach ($innerLegs as $innerLeg) {
                        $innerZone = \App\Models\Zone::find($innerLeg->reference_id);
                        $isChild   = $innerZone && (
                            $innerZone->parent_id === $activeLeg->reference_id ||
                            $innerZone->parent_id !== null
                        );
                        if (!$isChild) continue;

                        $innerLeaveId  = $zonePairs[$innerLeg->id] ?? null;
                        $innerLeaveLeg = $innerLeaveId ? $allLegs->firstWhere('id', $innerLeaveId) : null;

                        // $innerEnterRlRaw = $completedLegs->get($innerLeg->id);
                        // $innerLeaveRlRaw = $innerLeaveId ? $completedLegs->get($innerLeaveId) : null;

                        // $innerEnterRl = ($innerEnterRlRaw && !$innerEnterRlRaw->wasSkippedByParent()) ? $innerEnterRlRaw : null;
                        // $innerLeaveRl = ($innerLeaveRlRaw && !$innerLeaveRlRaw->wasSkippedByParent()) ? $innerLeaveRlRaw : null;
                        // $innerActual = $zoneActualSec[$innerLeg->id] ?? null;

                        $innerEnterGroup = $completedLegs->get($innerLeg->id);
                        $innerLeaveGroup = $innerLeaveId ? $completedLegs->get($innerLeaveId) : null;

                        $innerEnterRlRaw = $innerEnterGroup?->first();
                        $innerEnterRl    = $innerEnterGroup?->first(fn($rl) => !$rl->wasSkippedByParent());
                        $innerLeaveRl    = $innerLeaveGroup?->last(fn($rl) => !$rl->wasSkippedByParent());

                        // Durée : première entrée → dernière sortie
                        $innerActual = ($innerEnterRl && $innerLeaveRl)
                            ? (int) $innerEnterRl->occurred_at->diffInSeconds($innerLeaveRl->occurred_at)
                            : null;
                        if ($innerEnterRlRaw?->wasSkippedByParent()) $innerActual = null;

                        // if ($innerEnterRlRaw?->wasSkippedByParent() || $innerLeaveRlRaw?->wasSkippedByParent()) {
                        //     $innerActual = null;
                        // }

                        $innerRawT   = $legObjectives[$innerLeg->id] ?? $legObjectives[(string)$innerLeg->id] ?? null;
                        $innerTarget = ($innerRawT !== null && $innerRawT !== 'null') ? (int)$innerRawT : null;
                        $innerEcart  = ($innerActual !== null && $innerTarget !== null)
                            ? $innerActual - $innerTarget : null;

                        $children[] = [
                            'type'        => 'zone_block',
                            'enter_leg'   => $innerLeg,
                            'leave_leg'   => $innerLeaveLeg,
                            'enter_rl'    => $innerEnterRl,
                            'leave_rl'    => $innerLeaveRl,
                            'actual_sec'  => $innerActual,
                            'target_sec'  => $innerTarget,
                            'ecart'       => $innerEcart,
                            'children'    => [],
                            'is_subzone'  => true,
                            'was_skipped' => $innerEnterRlRaw?->wasSkippedByParent() ?? false,
                        ];

                        $skipIds[] = $innerLeg->id;
                        if ($innerLeaveId) $skipIds[] = $innerLeaveId;
                    }
                }

                $blocks[] = [
                    'type'        => 'zone_block',
                    'enter_leg'   => $activeLeg,
                    'leave_leg'   => $leaveLeg,
                    'enter_rl'    => $enterRl,
                    'leave_rl'    => $leaveRl,
                    'actual_sec'  => $actualSec,
                    'target_sec'  => $targetSec,
                    'ecart'       => $ecart,
                    'children'    => $children,
                    'is_subzone'  => $isSubZone,
                    'was_skipped' => $enterRlRaw?->wasSkippedByParent() ?? false,
                ];

                // Marquer le leg actif si pas encore fait (slot simple)
                if (!$leg->group_or) {
                    $skipIds[] = $activeLeg->id;
                    if ($leaveLegId) $skipIds[] = $leaveLegId;
                }

                continue;
            }
            

            // ── leave_zone non pairée → affiché seul ─────────────────────────────
            if ($leg->event_type === 'leave_zone') {
                // $rl = $completedLegs->get($leg->id);
                $rl = $completedLegs->get($leg->id)?->first();
                $blocks[] = [
                    'type'        => 'zone_block',
                    'enter_leg'   => $leg,
                    'leave_leg'   => null,
                    'enter_rl'    => ($rl && !$rl->wasSkippedByParent()) ? $rl : null,
                    'leave_rl'    => null,
                    'actual_sec'  => null,
                    'target_sec'  => null,
                    'ecart'       => null,
                    'children'    => [],
                    'is_subzone'  => false,
                    'was_skipped' => $rl?->wasSkippedByParent() ?? false,
                ];
                $skipIds[] = $leg->id;
            }
        }

        return $blocks;
    }

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'circuit_id' => 'required|exists:circuits,id',
            'vehicle_id' => 'nullable|exists:r_vehicules,id',
            'year'       => 'required|integer|min:2020|max:2099',
            'month'      => 'required|integer|min:1|max:12',
        ]);

        $circuit  = Circuit::findOrFail($data['circuit_id']);
        $vehicles = $data['vehicle_id']
            ? Rvehicule::where('id', $data['vehicle_id'])->get()
            : Rvehicule::all(); // ou filtrer par circuit si possible

        foreach ($vehicles as $vehicle) {
            CalculateVehicleRotations::dispatch(
                $vehicle->id,
                $circuit->id,
                $data['year'],
                $data['month']
            );
        }

        return back()->with('success', 
            count($vehicles) . ' véhicule(s) en cours de calcul en arrière-plan.'
        );
    }
 
    public function destroy(Rotation $rotation)
    {
        $rotation->rotationLegs()->delete();
        $rotation->delete();
        return back()->with('success', 'Rotation supprimée.');
    }
}
