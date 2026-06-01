<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\Rotation;
use App\Models\RotationObjective;
use App\Services\GpsApiService;
use App\Services\ReportService;
use App\Services\RotationCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;

// ============================================================
// RotationObjectiveController
// ============================================================
class RotationObjectiveController extends Controller
{
    // public function index(Circuit $circuit)
    // {
    //     $objectives = $circuit->objectives()->orderByDesc('effective_from')->get();
    //     return view('objectives.objectives', compact('circuit', 'objectives'));
    // }
    public function index(Circuit $circuit)
    {
        $objectives = $circuit->objectives()->orderByDesc('effective_from')->get();

        $legs            = $circuit->legs()->orderBy('order')->get();
        $durationInputs  = [];
        $processedLegs   = [];
        $processedGroups = [];

        // Détecter le group_or du slot de fin (dernier leg avec group_or)
        $endGroupOr = null;
        foreach ($legs->reverse() as $l) {
            if ($l->group_or) {
                $endGroupOr = $l->group_or;
                break;
            }
        }

        foreach ($legs as $leg) {
            if (isset($processedLegs[$leg->id])) continue;
            if ($leg->event_type === 'pass_checkpoint') {
                $processedLegs[$leg->id] = true;
                continue;
            }
            if ($leg->group_or && isset($processedGroups[$leg->group_or])) {
                $processedLegs[$leg->id] = true;
                continue;
            }

            if ($leg->event_type === 'enter_zone') {

                // Slot de fin → pas de saisie de durée
                if ($leg->group_or && $leg->group_or === $endGroupOr) {
                    $slotLegs = $legs->where('group_or', $leg->group_or)->values();
                    foreach ($slotLegs as $sl) { $processedLegs[$sl->id] = true; }
                    $processedGroups[$leg->group_or] = true;
                    continue;
                }

                $slotLegs = $leg->group_or
                    ? $legs->where('group_or', $leg->group_or)->where('event_type', 'enter_zone')->values()
                    : collect([$leg]);

                $matchingLeave   = null;
                $enterLegForPair = null;

                foreach ($slotLegs as $slotLeg) {
                    $candidate = $legs->first(fn($l) =>
                        $l->event_type === 'leave_zone' &&
                        $l->reference_id == $slotLeg->reference_id &&
                        $l->order > $slotLeg->order &&
                        !isset($processedLegs[$l->id])
                    );
                    if ($candidate) {
                        $matchingLeave   = $candidate;
                        $enterLegForPair = $slotLeg;
                        break;
                    }
                }

                // Collecter toutes les leave du groupe OU pour l'historique
                $allLeaveIds = [];
                if ($leg->group_or) {
                    foreach ($slotLegs as $slotLeg) {
                        $leave = $legs->first(fn($l) =>
                            $l->event_type === 'leave_zone' &&
                            $l->reference_id == $slotLeg->reference_id &&
                            $l->order > $slotLeg->order
                        );
                        if ($leave) $allLeaveIds[] = $leave->id;
                    }
                } elseif ($matchingLeave) {
                    $allLeaveIds[] = $matchingLeave->id;
                }

                $allSlotIds = array_merge(
                    $slotLegs->pluck('id')->toArray(),
                    $allLeaveIds
                );

                if ($leg->group_or) {
                    $names    = $slotLegs->map(fn($l) =>
                        \App\Models\Zone::find($l->reference_id)?->name ?? $l->label
                    )->join(' ou ');
                    $sublabel = 'Groupe OU : ' . $leg->group_or;
                } else {
                    $refLeg   = $enterLegForPair ?? $leg;
                    $names    = \App\Models\Zone::find($refLeg->reference_id)?->name ?? $refLeg->label;
                    $sublabel = $refLeg->label . ($matchingLeave ? '  →  ' . $matchingLeave->label : '');
                }

                $refLeg = $enterLegForPair ?? $leg;
                $legIds = $matchingLeave
                    ? [$refLeg->id, $matchingLeave->id]
                    : [$refLeg->id];

                $durationInputs[] = [
                    'label'        => "Durée dans la zone : {$names}",
                    'sublabel'     => $sublabel,
                    'leg_ids'      => $legIds,
                    'all_slot_ids' => $allSlotIds,
                    'is_or'        => (bool) $leg->group_or,
                    'key'          => "zone_pair_{$leg->id}",
                ];

                foreach ($slotLegs as $sl) { $processedLegs[$sl->id] = true; }
                foreach ($allLeaveIds as $lid) { $processedLegs[$lid] = true; }
                if ($leg->group_or) { $processedGroups[$leg->group_or] = true; }

            } else {
                $processedLegs[$leg->id] = true;
            }
        }

        return view('objectives.objectives', compact('circuit', 'objectives', 'durationInputs'));
    }
 
    public function store(Request $request, Circuit $circuit)
    {
        $data = $request->validate([
            'target_rotations_per_month' => 'nullable|integer|min:1',
            'target_duration_seconds'    => 'nullable|integer|min:1',
            'effective_from'             => 'required|date',
            'effective_until'            => 'nullable|date|after:effective_from',
            'notes'                      => 'nullable|string',
            'leg_objectives'             => 'nullable|array',
            'leg_objectives.*'           => 'nullable|integer|min:1',
        ]);

        $circuit->objectives()->create($data);
        return back()->with('success', 'Objectif créé.');
    }
 
    public function update(Request $request, Circuit $circuit, RotationObjective $objective)
    {
        $data = $request->validate([
            'target_rotations_per_month' => 'nullable|integer|min:1',
            'target_duration_seconds'    => 'nullable|integer|min:1',
            'effective_from'             => 'required|date',
            'effective_until'            => 'nullable|date|after:effective_from',
            'notes'                      => 'nullable|string',
            'leg_objectives'             => 'nullable|array',
            'leg_objectives.*'           => 'nullable|integer|min:1',
        ]);
 
        $objective->update($data);
        return back()->with('success', 'Objectif mis à jour.');
    }
 
    public function destroy(Circuit $circuit, RotationObjective $objective)
    {
        $objective->delete();
        return back()->with('success', 'Objectif supprimé.');
    }
}
