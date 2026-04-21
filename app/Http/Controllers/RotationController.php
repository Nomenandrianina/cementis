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

class RotationController extends Controller
{
     public function __construct(
        private readonly RotationCalculatorService $calculator,
        private readonly GpsApiService $gpsApi
    ) {}
 
    public function index(Request $request)
    {
        $circuits = Circuit::where('active', true)->orderBy('name')->get();
        $vehicles = Rvehicule::orderBy('name')->get();
 
        $query = Rotation::with(['rvehicule', 'circuit'])->latest('started_at');
 
        if ($request->circuit_id) {
            $query->where('circuit_id', $request->circuit_id);
        }
        if ($request->vehicle_id) {
            $query->where('rvehicule_id', $request->vehicle_id);
        }
        if ($request->month) {
            $query->where('counted_month', $request->month);
        }
        // if ($request->status) {
        //     $query->where('status', $request->status);
        // }
        // 2. Logique du Status (Par défaut : completed)
        if ($request->filled('status')) {
            // Si l'utilisateur a choisi un statut spécifique (ex: 'pending', 'cancelled')
            // On filtre par ce statut
            $query->where('status', $request->status);
        } 
        // else {
        //     // Si aucune requête de statut n'est envoyée (chargement initial)
        //     // On applique le filtre par défaut
        //     $query->where('status', 'completed');
        // }
        
        $rotations = $query->paginate(25)->withQueryString();
        
 
        return view('rotations.index', compact('rotations', 'circuits', 'vehicles'));
    }
 
    // public function show(Rotation $rotation)
    // {
    //     $rotation->load(['rvehicule', 'circuit.legs', 'rotationLegs.circuitLeg']);
    //     $objective = $rotation->circuit->currentObjective();
    //     return view('rotations.show', compact('rotation', 'objective'));
    // }
    public function show(Rotation $rotation)
    {
        $rotation->load(['rvehicule', 'circuit.legs', 'rotationLegs.circuitLeg']);
        $objective     = $rotation->circuit->currentObjective();
        $legObjectives = $objective?->leg_objectives ?? [];

        $allLegs       = $rotation->circuit->legs()->orderBy('order')->get();
        $completedLegs = $rotation->rotationLegs->keyBy('circuit_leg_id');

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
        $zoneActualSec = [];
        foreach ($zonePairs as $enterId => $exitId) {
            $enterRl = $completedLegs->get($enterId);
            $exitRl  = $completedLegs->get($exitId);
            if ($enterRl && $exitRl) {
                $zoneActualSec[$enterId] = (int) $enterRl->occurred_at
                    ->diffInSeconds($exitRl->occurred_at);
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

    /**
     * Construit les blocs d'affichage hiérarchiques.
     *
     * Retourne un tableau de blocs :
     * [
     *   'type'       => 'zone_block' | 'checkpoint',
     *   'enter_leg'  => CircuitLeg,
     *   'leave_leg'  => CircuitLeg|null,
     *   'enter_rl'   => RotationLeg|null,
     *   'leave_rl'   => RotationLeg|null,
     *   'actual_min' => int|null,
     *   'target_min' => int|null,
     *   'ecart'      => int|null,
     *   'children'   => [...sous-blocs zone...],    // pour zone_block seulement
     *   'leg'        => CircuitLeg,                 // pour checkpoint seulement
     *   'rl'         => RotationLeg|null,           // pour checkpoint seulement
     *   'is_subzone' => bool,
     * ]
     */
    private function buildDisplayBlocks(
        $allLegs, $completedLegs, $zonePairs,
        $pairedEnterIds, $pairedExitIds,
        $zoneActualSec, $legObjectives
    ): array {
        $blocks     = [];
        $skipIds    = []; // IDs de legs déjà traités (sub-zones absorbées dans leur parent)

        foreach ($allLegs as $leg) {
            if (in_array($leg->id, $skipIds)) continue;

            // ── Checkpoint ───────────────────────────────────────────────────────
            if ($leg->event_type === 'pass_checkpoint') {
                $blocks[] = [
                    'type' => 'checkpoint',
                    'leg'  => $leg,
                    'rl'   => $completedLegs->get($leg->id),
                ];
                $skipIds[] = $leg->id;
                continue;
            }

            // ── leave_zone seul (non pairé) → skip silencieux ───────────────────
            if ($leg->event_type === 'leave_zone' && in_array($leg->id, $pairedExitIds)) {
                $skipIds[] = $leg->id;
                continue;
            }

            // ── enter_zone ───────────────────────────────────────────────────────
            if ($leg->event_type === 'enter_zone') {
                $leaveLegId = $zonePairs[$leg->id] ?? null;
                $leaveLeg   = $leaveLegId ? $allLegs->firstWhere('id', $leaveLegId) : null;
                $enterRl    = $completedLegs->get($leg->id);
                $leaveRl    = $leaveLegId ? $completedLegs->get($leaveLegId) : null;
                $actualSec  = $zoneActualSec[$leg->id] ?? null;
                $rawTarget  = $legObjectives[$leg->id] ?? $legObjectives[(string)$leg->id] ?? null;
                $targetSec  = ($rawTarget !== null && $rawTarget !== 'null') ? (int)$rawTarget : null;
                $ecart      = ($actualSec !== null && $targetSec !== null) ? $actualSec - $targetSec : null;

                // Charger la zone BDD pour vérifier si elle a un parent
                $zone      = \App\Models\Zone::find($leg->reference_id);
                $isSubZone = $zone && $zone->parent_id !== null;

                // Chercher les sous-zones entre enter et leave de cette zone
                // = enter/leave dont la zone BDD a pour parent cette zone
                $children  = [];

                if ($leaveLeg) {
                    $innerLegs = $allLegs->filter(fn($l) =>
                        $l->order > $leg->order &&
                        $l->order < $leaveLeg->order &&
                        $l->event_type === 'enter_zone' &&
                        !in_array($l->id, $skipIds)
                    );

                    foreach ($innerLegs as $innerLeg) {
                        $innerZone = \App\Models\Zone::find($innerLeg->reference_id);

                        // C'est une sous-zone si sa zone parente = la zone du bloc courant
                        $isChild = $innerZone && (
                            $innerZone->parent_id === $leg->reference_id ||
                            // Ou si elle est entre enter et leave et a un parent quelconque
                            $innerZone->parent_id !== null
                        );

                        if (!$isChild) continue;

                        $innerLeaveId  = $zonePairs[$innerLeg->id] ?? null;
                        $innerLeaveLeg = $innerLeaveId ? $allLegs->firstWhere('id', $innerLeaveId) : null;
                        $innerEnterRl  = $completedLegs->get($innerLeg->id);
                        $innerLeaveRl  = $innerLeaveId ? $completedLegs->get($innerLeaveId) : null;
                        $innerActual   = $zoneActualSec[$innerLeg->id] ?? null;
                        $innerRawT     = $legObjectives[$innerLeg->id] ?? $legObjectives[(string)$innerLeg->id] ?? null;
                        $innerTarget   = ($innerRawT !== null && $innerRawT !== 'null') ? (int)$innerRawT : null;
                        $innerEcart    = ($innerActual !== null && $innerTarget !== null)
                                        ? $innerActual - $innerTarget : null;

                        $children[] = [
                            'type'       => 'zone_block',
                            'enter_leg'  => $innerLeg,
                            'leave_leg'  => $innerLeaveLeg,
                            'enter_rl'   => $innerEnterRl,
                            'leave_rl'   => $innerLeaveRl,
                            'actual_sec' => $innerActual,
                            'target_sec' => $innerTarget,
                            'ecart'      => $innerEcart,
                            'children'   => [],
                            'is_subzone' => true,
                        ];

                        $skipIds[] = $innerLeg->id;
                        if ($innerLeaveId) $skipIds[] = $innerLeaveId;
                    }
                }

                $blocks[] = [
                    'type'       => 'zone_block',
                    'enter_leg'  => $leg,
                    'leave_leg'  => $leaveLeg,
                    'enter_rl'   => $enterRl,
                    'leave_rl'   => $leaveRl,
                    'actual_sec' => $actualSec,
                    'target_sec' => $targetSec,
                    'ecart'      => $ecart,
                    'children'   => $children,
                    'is_subzone' => $isSubZone,
                ];

                $skipIds[] = $leg->id;
                if ($leaveLegId) $skipIds[] = $leaveLegId;
                continue;
            }

            // leave_zone non pairé → affiché seul
            if ($leg->event_type === 'leave_zone') {
                $enterRl   = $completedLegs->get($leg->id);
                $blocks[]  = [
                    'type'       => 'zone_block',
                    'enter_leg'  => $leg,
                    'leave_leg'  => null,
                    'enter_rl'   => $enterRl,
                    'leave_rl'   => null,
                    'actual_sec' => null,
                    'target_sec' => null,
                    'ecart'      => null,
                    'children'   => [],
                    'is_subzone' => false,
                ];
                $skipIds[] = $leg->id;
            }
        }

        return $blocks;
    }
 
    /**
     * Lance le calcul des rotations pour un circuit/véhicule/mois donné.
     */
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
            ? [Rvehicule::findOrFail($data['vehicle_id'])]
            : $circuit->vehicles->all();
 
        if (empty($vehicles)) {
            return back()->with('error', 'Aucun véhicule affecté à ce circuit.');
        }
 
        $totalCount = 0;
        $errors     = [];
 
        foreach ($vehicles as $vehicle) {
            $result      = $this->calculator->calculateForMonth($vehicle, $circuit, $data['year'], $data['month']);
            $totalCount += $result['count'];
            $errors      = array_merge($errors, $result['errors']);
        }
 
        $msg = "{$totalCount} rotation(s) calculée(s) pour " . count($vehicles) . " véhicule(s).";
        if (!empty($errors)) {
            $msg .= ' Avertissements : ' . implode(' | ', $errors);
        }
 
        return back()->with('success', $msg);
    }
 
    public function destroy(Rotation $rotation)
    {
        $rotation->rotationLegs()->delete();
        $rotation->delete();
        return back()->with('success', 'Rotation supprimée.');
    }
}
