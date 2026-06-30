<?php

namespace App\Services;

use App\Models\Circuit;
use App\Models\Rotation;
use App\Models\RotationObjective;
use App\Models\Rvehicule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Génère le rapport mensuel consolidé pour tous les véhicules d'un circuit.
     */
    public function monthlyReport(Circuit $circuit, int $year, int $month): array
    {
        $countedMonth = sprintf('%04d-%02d', $year, $month);

        $objective = RotationObjective::where('circuit_id', $circuit->id)
            ->where('effective_from', '<=', "{$year}-{$month}-01")
            ->where(function ($q) use ($year, $month) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', "{$year}-{$month}-01");
            })
            ->latest('effective_from')
            ->first();

        $vehicles = Rvehicule::all();

        $vehicleReports = $vehicles->map(function (Rvehicule $vehicle) use ($circuit, $countedMonth, $objective) {
            return $this->vehicleMonthlyReport($vehicle, $circuit, $countedMonth, $objective);
        });

        $totalRotations = $vehicleReports->sum('rotation_count');
        $targetRotations = ($objective?->target_rotations_per_month ?? 0) * $vehicles->count();

        return [
            'circuit'          => $circuit,
            'year'             => $year,
            'month'            => $month,
            'month_label'      => Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
            'objective'        => $objective,
            'vehicle_reports'  => $vehicleReports,
            'total_rotations'  => $totalRotations,
            'target_rotations' => $targetRotations,
            'achievement_rate' => $targetRotations > 0 ? round($totalRotations / $targetRotations * 100, 1) : null,
        ];
    }

    /**
     * Rapport mensuel pour un véhicule sur un circuit.
     */
    public function vehicleMonthlyReport(
        Rvehicule $vehicle,
        Circuit $circuit,
        string $countedMonth,
        ?RotationObjective $objective
    ): array {
        $rotations = Rotation::with('rotationLegs.circuitLeg')
            ->where('rvehicule_id', $vehicle->id)
            ->where('circuit_id', $circuit->id)
            ->where('counted_month', $countedMonth)
            ->where(function ($query) {
                $query->where('status', 'completed')
                      ->orWhere('status', 'acceptable');
            })
            ->where('is_valid', true)
            ->orderBy('duration_seconds', 'desc')
            ->get();

        $rotationDetails = $rotations->map(fn($r) => $this->rotationDetail($r, $objective));

        $avgDuration = $rotations->avg('duration_seconds');

        return [
            'vehicle'           => $vehicle,
            'rotation_count'    => $rotations->count(),
            'target_rotations'  => $objective?->target_rotations_per_month,
            'target_duration'   => $this->formatSeconde($objective?->target_duration_seconds),
            'avg_duration'      => $avgDuration ? $this->formatSeconde(round($avgDuration)) : null,
            'total_duration'    => $this->formatSeconde($rotations->sum('duration_seconds')),
            'rotations'         => $rotationDetails,
            'cancelled_count'   => Rotation::where('rvehicule_id', $vehicle->id)
                                    ->where('circuit_id', $circuit->id)
                                    ->where('counted_month', $countedMonth)
                                    ->where('status', 'cancelled')
                                    ->count(),
            'max_duration'       => $rotations->max('duration_seconds'),
        ];
    }

    public function rotationDetail(Rotation $rotation, ?RotationObjective $objective): array
    {
        $allLegs       = $rotation->circuit->legs()->orderBy('order')->get();
        $completedLegs = $rotation->rotationLegs->keyBy('circuit_leg_id');
        $legObjectives = $objective?->leg_objectives ?? [];

        $zonePairs      = [];
        $pairedEnterIds = [];
        $pairedExitIds  = [];

        foreach ($allLegs as $leg) {
            if ($leg->event_type !== 'enter_zone') continue;
            if (in_array($leg->id, $pairedEnterIds)) continue;

            $leave = $allLegs->first(fn($l) =>
                $l->event_type === 'leave_zone' &&
                $l->reference_id == $leg->reference_id &&
                $l->order > $leg->order &&
                !in_array($l->id, $pairedExitIds)
            );

            if ($leave) {
                $zonePairs[$leg->id] = $leave->id;
                $pairedEnterIds[]    = $leg->id;
                $pairedExitIds[]     = $leave->id;
            }
        }

        $zoneActualSec = [];
        foreach ($zonePairs as $enterId => $exitId) {
            $enterRl = $completedLegs->get($enterId);
            $exitRl  = $completedLegs->get($exitId);
            if ($enterRl && $exitRl) {
                $zoneActualSec[$enterId] = (int) $enterRl->occurred_at
                    ->diffInSeconds($exitRl->occurred_at);
            }
        }

        $blocks  = [];
        $skipIds = [];

        foreach ($allLegs as $leg) {
            if (in_array($leg->id, $skipIds)) continue;
            if ($leg->event_type === 'pass_checkpoint') {
                $rl = $completedLegs->get($leg->id);
                $blocks[] = [
                    'type'        => 'checkpoint',
                    'leg_id'      => $leg->id,          // ← ajouté
                    'label'       => $leg->label,
                    'occurred_at' => $rl?->occurred_at?->timezone('GMT+3')->format('d/m H:i:s'),
                    'is_done'     => $rl !== null,
                ];
                $skipIds[] = $leg->id;
                continue;
            }

            if ($leg->event_type === 'leave_zone' && in_array($leg->id, $pairedExitIds)) {
                $skipIds[] = $leg->id;
                continue;
            }

            if ($leg->event_type === 'enter_zone') {
                [$block, $absorbed] = $this->buildZoneBlock(
                    $leg, $allLegs, $completedLegs,
                    $zonePairs, $pairedExitIds,
                    $zoneActualSec, $legObjectives,
                    $skipIds
                );
                $blocks[]  = $block;
                $skipIds   = array_merge($skipIds, $absorbed);
                continue;
            }

            if ($leg->event_type === 'leave_zone') {
                $rl = $completedLegs->get($leg->id);
                $blocks[] = [
                    'type'         => 'zone',
                    'leg_id'       => $leg->id,
                    'enter_leg_id' => null,
                    'leave_leg_id' => $leg->id,
                    'label'        => $leg->label,
                    'enter_at'     => null,                                                          // ← null, pas de date d'arrivée
                    'leave_at'     => $rl?->occurred_at?->timezone('GMT+3')->format('d/m H:i:s'),  // ← ajoute :s pour les secondes
                    'actual_sec'   => null,
                    'target_sec'   => null,
                    'ecart'        => null,
                    'is_done'      => $rl !== null,
                    'children'     => [],
                ];
                $skipIds[] = $leg->id;
            }
        }

        $targetDuration = $objective?->target_duration_seconds;
        $actualDuration = $rotation->duration_seconds;

        return [
            'id'              => $rotation->id,
            'status'          => $rotation->status,
            'started_at'      => \Carbon\Carbon::parse($rotation->started_at_local)->format('d/m/Y H:i:s'),
            'completed_at'    => $rotation->completed_at_local
                                    ? \Carbon\Carbon::parse($rotation->completed_at_local)->format('d/m/Y H:i:s')
                                    : '—',
            'duration_seconds'=> $actualDuration,
            'duration_label'  => $this->formatSeconde($actualDuration),
            'target_duration' => $targetDuration,
            'target_label'    => $this->formatSeconde($targetDuration),
            'vs_target'       => ($targetDuration && $actualDuration)
                                    ? $actualDuration - $targetDuration : null,
            'blocks'          => $blocks,
        ];
    }

    private function buildZoneBlock(
        $leg, $allLegs, $completedLegs,
        $zonePairs, $pairedExitIds,
        $zoneActualSec, $legObjectives,
        $currentSkipIds
    ): array {
        $leaveLegId = $zonePairs[$leg->id] ?? null;
        $leaveLeg   = $leaveLegId ? $allLegs->firstWhere('id', $leaveLegId) : null;

        $enterRlRaw = $completedLegs->get($leg->id);
        $leaveRlRaw = $leaveLegId ? $completedLegs->get($leaveLegId) : null;

        // Si skipped_by_parent → traiter comme non visité
        $enterRl = ($enterRlRaw && !$enterRlRaw->wasSkippedByParent()) ? $enterRlRaw : null;
        $leaveRl = ($leaveRlRaw && !$leaveRlRaw->wasSkippedByParent()) ? $leaveRlRaw : null;

        $actualSec  = $zoneActualSec[$leg->id] ?? null;

        if ($enterRlRaw?->wasSkippedByParent() || $leaveRlRaw?->wasSkippedByParent()) {
            $actualSec = null;
        }
        $rawTarget  = $legObjectives[$leg->id] ?? $legObjectives[(string)$leg->id] ?? null;
        $targetSec  = ($rawTarget !== null && $rawTarget !== 'null') ? (int)$rawTarget : null;
        $ecart      = ($actualSec !== null && $targetSec !== null)
                        ? $actualSec - $targetSec : null;

        $absorbedIds = [$leg->id];
        if ($leaveLegId) $absorbedIds[] = $leaveLegId;

        $children = [];

        if ($leaveLeg) {
            $innerLegs = $allLegs->filter(fn($l) =>
                $l->order > $leg->order &&
                $l->order < $leaveLeg->order &&
                $l->event_type === 'enter_zone' &&
                !in_array($l->id, $currentSkipIds) &&
                !in_array($l->id, $absorbedIds)
            );

            foreach ($innerLegs as $innerLeg) {
                $innerZone = \App\Models\Zone::find($innerLeg->reference_id);
                if (!$innerZone || $innerZone->parent_id === null) continue;

                $innerLeaveId  = $zonePairs[$innerLeg->id] ?? null;

                $innerEnterRlRaw = $completedLegs->get($innerLeg->id);
                $innerLeaveRlRaw = $innerLeaveId ? $completedLegs->get($innerLeaveId) : null;

                // ── CLE DU FIX : si skipped_by_parent → null pour l'affichage ──
                $innerEnterRl = ($innerEnterRlRaw && !$innerEnterRlRaw->wasSkippedByParent())
                ? $innerEnterRlRaw : null;
                $innerLeaveRl = ($innerLeaveRlRaw && !$innerLeaveRlRaw->wasSkippedByParent())
                ? $innerLeaveRlRaw : null;

                $innerActual   = $zoneActualSec[$innerLeg->id] ?? null;
                // Durée nulle si entrée ou sortie skippée
                if ($innerEnterRlRaw?->wasSkippedByParent() || $innerLeaveRlRaw?->wasSkippedByParent()) {
                    $innerActual = null;
                }
                $innerRawT     = $legObjectives[$innerLeg->id] ?? $legObjectives[(string)$innerLeg->id] ?? null;
                $innerTarget   = ($innerRawT !== null && $innerRawT !== 'null') ? (int)$innerRawT : null;
                $innerEcart    = ($innerActual !== null && $innerTarget !== null)
                                   ? $innerActual - $innerTarget : null;

                $children[] = [
                    'enter_leg_id' => $innerLeg->id,        // ← ajouté
                    'leave_leg_id' => $innerLeaveId,        // ← ajouté
                    'label'        => $innerLeg->label,
                    'enter_at'     => $innerEnterRl?->occurred_at?->timezone('GMT+3')->format('d/m H:i:s'),
                    'leave_at'     => $innerLeaveRl?->occurred_at?->timezone('GMT+3')->format('d/m H:i:s'),
                    'actual_sec'   => $innerActual,
                    'target_sec'   => $innerTarget,
                    'ecart'        => $innerEcart,
                    'is_done'      => $innerEnterRl !== null,
                    'was_skipped'=> $innerEnterRlRaw?->wasSkippedByParent() ?? false,
                ];

                $absorbedIds[] = $innerLeg->id;
                if ($innerLeaveId) $absorbedIds[] = $innerLeaveId;
            }
        }

        $block = [
            'type'         => 'zone',
            'leg_id'       => $leg->id,          // ← ajouté
            'enter_leg_id' => $leg->id,          // ← ajouté
            'leave_leg_id' => $leaveLegId,       // ← ajouté
            'label'        => $leg->label,
            'enter_at'     => $enterRl?->occurred_at?->timezone('GMT+3')->format('d/m H:i:s'),
            'leave_at'     => $leaveRl?->occurred_at?->timezone('GMT+3')->format('d/m H:i:s'),
            'actual_sec'   => $actualSec,
            'target_sec'   => $targetSec,
            'ecart'        => $ecart,
            'is_done'      => $enterRl !== null,
            'children'     => $children,
            'was_skipped'=> $enterRlRaw?->wasSkippedByParent() ?? false,
        ];

        return [$block, $absorbedIds];
    }

    private function formatDuration(?int $minutes): string
    {
        if ($minutes === null) return '—';
        return intdiv($minutes, 60) . 'h' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT) . 'm'. str_pad($minutes % 60,2,'0',STR_PAD_LEFT).'s';
    }

    private function formatSeconde(?int $seconds): string
    {
        if ($seconds === null || $seconds < 0) return '—';

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return $hours . ':' . 
            str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . 
            str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT);
    }
}
