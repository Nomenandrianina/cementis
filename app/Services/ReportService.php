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

        // $vehicles = $circuit->vehicles;
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
            ->where('status', 'completed')
            ->where('is_valid', true)
            ->orderBy('started_at')
            ->get();

        $rotationDetails = $rotations->map(fn($r) => $this->rotationDetail($r, $objective));

        $avgDuration = $rotations->avg('duration_minutes');

        return [
            'vehicle'           => $vehicle,
            'rotation_count'    => $rotations->count(),
            'target_rotations'  => $objective?->target_rotations_per_month,
            'target_duration'   => $objective?->target_duration_minutes,
            'avg_duration'      => $avgDuration ? round($avgDuration) : null,
            'total_duration'    => $rotations->sum('duration_minutes'),
            'rotations'         => $rotationDetails,
            'cancelled_count'   => Rotation::where('rvehicule_id', $vehicle->id)
                                    ->where('circuit_id', $circuit->id)
                                    ->where('counted_month', $countedMonth)
                                    ->where('status', 'cancelled')
                                    ->count(),
        ];
    }

    /**
     * Détail d'une rotation : dates/heures d'entrée et sortie par étape.
     */
    public function rotationDetail(Rotation $rotation, ?RotationObjective $objective): array
    {
        $legs = $rotation->rotationLegs->sortBy('occurred_at');
        $legObjectives = $objective?->leg_objectives ?? [];

        $legDetails = $legs->map(function ($rl) use ($legObjectives) {
            $targetMinutes = $legObjectives[$rl->circuit_leg_id] ?? null;
            return [
                'label'              => $rl->circuitLeg->label ?? '—',
                'event_type'         => $rl->circuitLeg->event_type ?? '',
                'occurred_at'        => Carbon::parse($rl->occurred_at)->format('d/m/Y H:i'),
                'duration_since_prev'=> $rl->duration_since_previous_minutes,
                'target_duration'    => $targetMinutes,
                'vs_target'          => $targetMinutes && $rl->duration_since_previous_minutes
                                            ? $rl->duration_since_previous_minutes - $targetMinutes
                                            : null,
            ];
        });

        $targetDuration = $objective?->target_duration_minutes;
        $actualDuration = $rotation->duration_minutes;

        return [
            'id'              => $rotation->id,
            'started_at'      => Carbon::parse($rotation->started_at)->format('d/m/Y H:i'),
            'completed_at'    => $rotation->completed_at
                                    ? Carbon::parse($rotation->completed_at)->format('d/m/Y H:i')
                                    : '—',
            'duration_minutes'=> $actualDuration,
            'duration_label'  => $this->formatDuration($actualDuration),
            'target_duration' => $targetDuration,
            'target_label'    => $this->formatDuration($targetDuration),
            'vs_target'       => ($targetDuration && $actualDuration)
                                    ? $actualDuration - $targetDuration
                                    : null,
            'legs'            => $legDetails,
        ];
    }

    private function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return "{$h}h{$m}m";
    }
}