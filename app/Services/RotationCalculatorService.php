<?php

namespace App\Services;

use App\Models\Circuit;
use App\Models\CircuitLeg;
use App\Models\Rotation;
use App\Models\RotationLeg;
use App\Models\Rvehicule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RotationCalculatorService
{
    public const TEST_MODE = 'complete'; 

    public function __construct(
        private readonly GpsApiService $gpsApi,
        private readonly GpsEventMapper $mapper
    ) {}

    /**
     * Calcule les rotations d'un véhicule sur un circuit pour un mois donné.
     * Gère la règle de chevauchement de mois.
     *
     * @param  Rvehicule $vehicle
     * @param  Circuit $circuit
     * @param  int     $year
     * @param  int     $month
     * @return array   ['rotations' => Rotation[], 'count' => int, 'errors' => string[]]
     */
    public function calculateForMonth(Rvehicule $vehicle, Circuit $circuit, int $year, int $month): array
    {
        $countedMonth = sprintf('%04d-%02d', $year, $month);

        // On récupère les events sur le mois courant + débordement sur mois suivant
        // pour capturer les rotations qui commencent ce mois mais finissent le suivant
        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd   = $periodStart->copy()->addMonth()->endOfMonth();

        if(self::TEST_MODE === 'complete') {
            $rawEvents = $this->getTestEvents();
            Log::info("[TEST_MODE='" . self::TEST_MODE . "'] Données GPS statiques utilisées.", [
                'vehicle' => $vehicle->imei,
                'circuit' => $circuit->code,
                'count'   => count($rawEvents),
            ]);
        }else {
            $rawEvents = $this->gpsApi->getEventsForPeriod(
                $vehicle->imei,
                $periodStart->format('YmdHis'),
                $periodEnd->format('YmdHis')
            );
        }

        
        $rawEvents = $this->mapper->normalize($rawEvents);
        // dd($rawEvents);
        if (empty($rawEvents)) {
            return ['rotations' => [], 'count' => 0, 'errors' => ['Aucun événement GPS trouvé pour la période.']];
        }

        $legs   = $circuit->legs()->orderBy('order')->get();
        $errors = [];

        if ($legs->isEmpty()) {
            return ['rotations' => [], 'count' => 0, 'errors' => ['Aucune étape définie pour ce circuit.']];
        }

        // Supprimer les rotations existantes recalculées pour ce mois/véhicule/circuit
        Rotation::where('rvehicule_id', $vehicle->id)
            ->where('circuit_id', $circuit->id)
            ->where('counted_month', $countedMonth)
            ->delete();

        $rotations = $this->extractRotations($rawEvents, $legs, $vehicle, $circuit, $countedMonth);

        // Appliquer la règle de chevauchement :
        // - Si la rotation commence dans le mois courant → on la compte CE mois
        // - Si elle commence avant et se termine dans le mois → on la compte dans le mois de fin
        $validRotations = [];
        foreach ($rotations as $rotation) {
            $startMonth = Carbon::parse($rotation->started_at)->format('Y-m');
            $endMonth   = $rotation->completed_at
                ? Carbon::parse($rotation->completed_at)->format('Y-m')
                : null;

            if ($startMonth === $countedMonth) {
                // Rotation qui commence ce mois
                if ($endMonth === $countedMonth || $endMonth === null) {
                    // Complète dans le mois ou en cours → compte normalement si complète
                    if ($rotation->status === 'completed' && $rotation->is_valid) {
                        $rotation->counted_month = $countedMonth;
                    }
                } else {
                    // Chevauche sur le mois suivant → 0 pour ce mois, 1 pour le suivant
                    $rotation->counted_month = $endMonth;
                }
            }
            // Rotations qui commencent avant ce mois sont ignorées (déjà comptées)

            $rotation->save();
            $validRotations[] = $rotation;
        }

        $count = collect($validRotations)
            ->where('counted_month', $countedMonth)
            ->where('status', 'completed')
            ->where('is_valid', true)
            ->count();

        return [
            'rotations' => $validRotations,
            'count'     => $count,
            'errors'    => $errors,
        ];
    }

    /**
     * Extrait les rotations depuis la séquence d'événements GPS.
     * Utilise un automate à états basé sur l'ordre des étapes du circuit.
     */
    private function extractRotations(
        array $rawEvents,
        Collection $legs,
        Rvehicule $vehicle,
        Circuit $circuit,
        string $countedMonth
    ): array {
        $rotations    = [];
        $currentLegIdx = 0;
        $currentRotation = null;
        $legEvents    = []; // événements correspondant aux étapes de la rotation courante

        foreach ($rawEvents as $event) {
            $leg = $legs->get($currentLegIdx);
            if (!$leg) {
                continue;
            }
            // dd($leg, $event, $this->eventMatchesLeg($event, $leg));
            if ($this->eventMatchesLeg($event, $leg)) {
                if ($currentLegIdx === 0) {
                    // dd($currentRotation, $event);
                    // Début d'une nouvelle rotation (T1)
                    $currentRotation = $this->createRotation($vehicle, $circuit, $event, $countedMonth);
                    $legEvents = [];
                }

                if ($currentRotation) {
                    $occurredAt = Carbon::parse($event['dt'] ?? now());
                    $prevOccurredAt = !empty($legEvents)
                        ? Carbon::parse(end($legEvents)['dt'])
                        : null;

                    $durationSincePrev = $prevOccurredAt
                        ? $prevOccurredAt->diffInMinutes($occurredAt)
                        : null;

                    RotationLeg::create([
                        'rotation_id'                    => $currentRotation->id,
                        'circuit_leg_id'                 => $leg->id,
                        'occurred_at'                    => $occurredAt,
                        'lat'                            => $event['lat'] ?? null,
                        'lng'                            => $event['lng'] ?? null,
                        'duration_since_previous_minutes'=> $durationSincePrev,
                        'raw_event'                      => $event,
                    ]);

                    $legEvents[] = $event;
                    $currentLegIdx++;

                    // Dernière étape atteinte → rotation complète
                    if ($currentLegIdx >= $legs->count()) {
                        $this->completeRotation($currentRotation, $event, $legs);
                        $rotations[]     = $currentRotation;
                        $currentRotation = null;
                        $currentLegIdx   = 0;
                        $legEvents       = [];
                    }
                }
            } elseif ($currentRotation && $this->eventInvalidatesRotation($event, $circuit, $legs)) {
                // Véhicule sort du circuit prévu → annuler la rotation
                $currentRotation->update([
                    'status'               => 'cancelled',
                    'is_valid'             => false,
                    'invalidation_reason'  => "Sortie du circuit prévue : événement {$event['normalized_type']} à {$event['dt']}",
                ]);
                $rotations[]     = $currentRotation;
                $currentRotation = null;
                $currentLegIdx   = 0;
                $legEvents       = [];
            }
        }

        // Rotation en cours non terminée
        if ($currentRotation) {
            $currentRotation->update([
                'status'   => 'in_progress',
                'is_valid' => false,
            ]);
            $rotations[] = $currentRotation;
        }

        return $rotations;
    }

    /**
     * Vérifie si un événement GPS correspond à une étape du circuit.
     */
    private function eventMatchesLeg(array $event, CircuitLeg $leg): bool
    {
        $eventType = strtolower($event['normalized_type'] ?? '');
        $referenceName = strtolower($event['reference_name'] ?? '');
        $legLabel = strtolower($leg->label ?? '');

        $typeMatches = match ($leg->event_type) {
                'enter_zone'      => str_contains($eventType, 'enter'),
                'leave_zone'      => str_contains($eventType, 'leave') || str_contains($eventType, 'exit'),
                'pass_checkpoint' => str_contains($eventType, 'marker') || str_contains($eventType, 'checkpoint'),
                default           => false,
            };
        $labelMatches = empty($referenceName) || str_contains($legLabel, $referenceName);
        // return match ($leg->event_type) {
        //     'enter_zone' => str_contains($eventType, 'enter'),
        //     'leave_zone' => str_contains($eventType, 'leave') || str_contains($eventType, 'exit'),
        //                     // && $this->referenceMatchesLeg($zoneId, $leg),
        //     'pass_checkpoint' => str_contains($eventType, 'marker') || str_contains($eventType, 'checkpoint'),
        //                          // && $this->referenceMatchesLeg($markerId, $leg),
        //     default => false,
        // };
        return $typeMatches && $labelMatches;
    }

    /**
     * Vérifie si la référence (zone/checkpoint) de l'événement correspond à l'étape.
     */
    private function referenceMatchesLeg(string $refId, CircuitLeg $leg): bool
    {
        if (empty($refId)) {
            return false;
        }

        if ($leg->reference_type === 'zone') {
            $zone = \App\Models\Zone::find($leg->reference_id);
            return $zone && (string) $zone->gps_zone_id === $refId;
        }

        if ($leg->reference_type === 'checkpoint') {
            $cp = \App\Models\Checkpoint::find($leg->reference_id);
            return $cp && (string) $cp->gps_marker_id === $refId;
        }

        return false;
    }

    /**
     * Détecte si un événement invalide la rotation (sortie du circuit prévu).
     */
    private function eventInvalidatesRotation(array $event, Circuit $circuit, Collection $legs): bool
    {
        // On considère qu'un événement d'entrée dans une zone NON prévue dans le circuit
        // peut invalider la rotation — logique simplifiée, extensible
        $eventType = strtolower($event['normalized_type'] ?? '');
        if (!str_contains($eventType, 'enter')) {
            return false;
        }

        $zoneId = (string) ($event['zone_id'] ?? $event['geofence_id'] ?? '');
        if (empty($zoneId)) {
            return false;
        }

        // Récupérer toutes les zones autorisées dans ce circuit
        $allowedZoneGpsIds = $legs
            ->where('reference_type', 'zone')
            ->map(fn($leg) => \App\Models\Zone::find($leg->reference_id)?->gps_zone_id)
            ->filter()
            ->map(fn($id) => (string) $id)
            ->toArray();

        // Si la zone d'arrivée n'est pas dans le circuit → invalide
        return !empty($allowedZoneGpsIds) && !in_array($zoneId, $allowedZoneGpsIds);
    }

    private function createRotation(Rvehicule $vehicle, Circuit $circuit, array $event, string $countedMonth): Rotation
    {
        return Rotation::create([
            'rvehicule_id'    => $vehicle->id,
            'circuit_id'    => $circuit->id,
            'started_at'    => Carbon::parse($event['dt']),
            'status'        => 'in_progress',
            'counted_month' => $countedMonth,
            'is_valid'      => false,
            'raw_events'    => [],
        ]);
    }

    private function completeRotation(Rotation $rotation, array $lastEvent, Collection $legs): void
    {
        $completedAt = Carbon::parse($lastEvent['dt'] ?? now());
        $duration    = Carbon::parse($rotation->started_at)->diffInMinutes($completedAt);

        $rotation->update([
            'completed_at'     => $completedAt,
            'duration_minutes' => $duration,
            'status'           => 'completed',
            'is_valid'         => true,
        ]);
    }

    private function getTestEvents(): array
    {
        return match (self::TEST_MODE) {
            'complete'    => TestRawEvents::completeRotation(),
            'incomplete'  => TestRawEvents::incompleteRotation(),
            'cancelled'   => TestRawEvents::cancelledRotation(),
            'real_sample' => TestRawEvents::realApiSample(),
            default       => [],
        };
    }
}