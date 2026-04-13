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
    // private function extractRotations(
    //     array $rawEvents,
    //     Collection $legs,
    //     Rvehicule $vehicle,
    //     Circuit $circuit,
    //     string $countedMonth
    // ): array {
    //     $rotations    = [];
    //     $currentLegIdx = 0;
    //     $currentRotation = null;
    //     $legEvents    = []; // événements correspondant aux étapes de la rotation courante

    //     foreach ($rawEvents as $event) {
    //         $leg = $legs->get($currentLegIdx);
    //         if (!$leg) {
    //             continue;
    //         }

    //         if ($this->eventMatchesLeg($event, $leg)) {
    //             if ($currentLegIdx === 0) {
    //                 // Début d'une nouvelle rotation (T1)
    //                 $currentRotation = $this->createRotation($vehicle, $circuit, $event, $countedMonth);
    //                 $legEvents = [];
    //             }

    //             if ($currentRotation) {
    //                 $occurredAt = Carbon::parse($event['dt'] ?? now());
    //                 $prevOccurredAt = !empty($legEvents)
    //                     ? Carbon::parse(end($legEvents)['dt'])
    //                     : null;

    //                 $durationSincePrev = $prevOccurredAt
    //                     ? $prevOccurredAt->diffInMinutes($occurredAt)
    //                     : null;

    //                 RotationLeg::create([
    //                     'rotation_id'                    => $currentRotation->id,
    //                     'circuit_leg_id'                 => $leg->id,
    //                     'occurred_at'                    => $occurredAt,
    //                     'lat'                            => $event['lat'] ?? null,
    //                     'lng'                            => $event['lng'] ?? null,
    //                     'duration_since_previous_minutes'=> $durationSincePrev,
    //                     'raw_event'                      => $event,
    //                 ]);

    //                 $legEvents[] = $event;
    //                 $currentLegIdx++;

    //                 // Dernière étape atteinte → rotation complète
    //                 if ($currentLegIdx >= $legs->count()) {
    //                     $this->completeRotation($currentRotation, $event, $legs);
    //                     $rotations[]     = $currentRotation;
    //                     $currentRotation = null;
    //                     $currentLegIdx   = 0;
    //                     $legEvents       = [];
    //                 }
    //             }
    //         } elseif ($currentRotation && $this->eventInvalidatesRotation($event, $circuit, $legs)) {
    //             // Véhicule sort du circuit prévu → annuler la rotation
    //             $currentRotation->update([
    //                 'status'               => 'cancelled',
    //                 'is_valid'             => false,
    //                 'invalidation_reason'  => "Sortie du circuit prévue : événement {$event['normalized_type']} à {$event['dt']}",
    //             ]);
    //             $rotations[]     = $currentRotation;
    //             $currentRotation = null;
    //             $currentLegIdx   = 0;
    //             $legEvents       = [];
    //         }
    //     }

    //     // Rotation en cours non terminée
    //     if ($currentRotation) {
    //         $currentRotation->update([
    //             'status'   => 'in_progress',
    //             'is_valid' => false,
    //         ]);
    //         $rotations[] = $currentRotation;
    //     }

    //     return $rotations;
    // }
    private function extractRotations(
        array      $events,
        Collection $legs,
        $vehicle,
        Circuit    $circuit,
        string     $countedMonth
    ): array {
        $rotations       = [];
        $currentLegIdx   = 0;
        $currentRotation = null;
        $legEvents       = [];

        $i          = 0;
        $totalEvts  = count($events);
        $replayOnce = false; // garde-fou anti-boucle infinie

        while ($i < $totalEvts) {
            $event       = $events[$i];
            $expectedLeg = $legs->get($currentLegIdx);

            if ($expectedLeg === null) {
                $i++;
                $replayOnce = false;
                continue;
            }

            // ── MATCH : événement correspond à l'étape attendue ───────────────
            if ($this->eventMatchesLeg($event, $expectedLeg)) {
                $replayOnce = false;

                // Démarrage rotation sur T1
                if ($currentLegIdx === 0) {
                    $currentRotation = Rotation::create([
                        'rvehicule_id'  => $vehicle->id,
                        'circuit_id'    => $circuit->id,
                        'started_at'    => $event['dt'],
                        'status'        => 'in_progress',
                        'counted_month' => $countedMonth,
                        'is_valid'      => false,
                        'raw_events'    => [],
                    ]);
                    $legEvents = [];

                    Log::info('Rotation démarrée.', [
                        'rotation_id' => $currentRotation->id,
                        'dt'          => $event['dt'],
                        'leg'         => $expectedLeg->label,
                    ]);
                }

                if ($currentRotation) {
                    $occurredAt        = Carbon::parse($event['dt']);
                    $prevOccurredAt    = !empty($legEvents)
                        ? Carbon::parse(end($legEvents)['dt'])
                        : null;
                    $durationSincePrev = $prevOccurredAt
                        ? (int) $prevOccurredAt->diffInMinutes($occurredAt)
                        : null;

                    RotationLeg::create([
                        'rotation_id'                     => $currentRotation->id,
                        'circuit_leg_id'                  => $expectedLeg->id,
                        'occurred_at'                     => $occurredAt,
                        'lat'                             => $event['lat'] ?? null,
                        'lng'                             => $event['lng'] ?? null,
                        'duration_since_previous_minutes' => $durationSincePrev,
                        'raw_event'                       => $event['raw'] ?? $event,
                    ]);

                    $legEvents[] = $event;
                    $currentLegIdx++;

                    Log::debug('Étape validée.', [
                        'leg'   => $expectedLeg->label,
                        'idx'   => $currentLegIdx,
                        'total' => $legs->count(),
                        'dt'    => $event['dt'],
                    ]);

                    // ── Rotation complète ──────────────────────────────────────
                    if ($currentLegIdx >= $legs->count()) {
                        $completedAt = Carbon::parse($event['dt']);
                        $duration    = (int) Carbon::parse($currentRotation->started_at)
                                                ->diffInMinutes($completedAt);

                        $currentRotation->update([
                            'completed_at'     => $completedAt,
                            'duration_minutes' => $duration,
                            'status'           => 'completed',
                            'is_valid'         => true,
                        ]);

                        Log::info('Rotation complète.', [
                            'rotation_id'  => $currentRotation->id,
                            'duration_min' => $duration,
                        ]);

                        $rotations[]     = $currentRotation;
                        $currentRotation = null;
                        $currentLegIdx   = 0;
                        $legEvents       = [];

                        // Rejouer cet événement : il peut être T1 de la rotation suivante
                        // (ex: zone_in Andranomena = T5 et T1 en même temps)
                        // $replayOnce empêche de rejouer plus d'une fois le même event
                        if (!$replayOnce) {
                            $replayOnce = true;
                            // PAS de $i++ → on rejoue
                            continue;
                        }

                        // Déjà rejoué et toujours pas T1 → on avance
                        $replayOnce = false;
                        $i++;
                        continue;
                    }
                }

                $i++;
                continue;
            }

            // ── PAS DE MATCH ──────────────────────────────────────────────────

            if ($currentRotation !== null) {

                // Cas 1 : étape sautée → rotation annulée
                $skippedLeg = $this->eventSkipsExpectedLeg(
                    $event, $expectedLeg, $legs, $currentLegIdx
                );

                if ($skippedLeg !== null) {
                    $currentRotation->update([
                        'status'              => 'cancelled',
                        'is_valid'            => false,
                        'invalidation_reason' => sprintf(
                            'Étape manquée : "%s" (ordre %d) — non validée avant "%s" à %s',
                            $skippedLeg->label,
                            $skippedLeg->order,
                            $event['reference_name'] ?? '?',
                            $event['dt']             ?? '?'
                        ),
                    ]);

                    Log::warning('Rotation annulée — étape manquée.', [
                        'rotation_id'  => $currentRotation->id,
                        'missed_leg'   => $skippedLeg->label,
                        'missed_order' => $skippedLeg->order,
                    ]);

                    $rotations[]     = $currentRotation;
                    $currentRotation = null;
                    $currentLegIdx   = 0;
                    $legEvents       = [];

                    if (!$replayOnce) {
                        $replayOnce = true;
                        continue; // Rejouer : cet event peut être T1
                    }

                    $replayOnce = false;
                    $i++;
                    continue;
                }

                // Cas 2 : déviation hors circuit
                if ($this->eventInvalidatesRotation($event, $circuit, $legs)) {
                    $currentRotation->update([
                        'status'              => 'cancelled',
                        'is_valid'            => false,
                        'invalidation_reason' => sprintf(
                            'Déviation hors circuit : "%s" à %s',
                            $event['reference_name'] ?? '?',
                            $event['dt']             ?? '?'
                        ),
                    ]);

                    Log::warning('Rotation annulée — déviation.', [
                        'rotation_id' => $currentRotation->id,
                        'zone'        => $event['reference_name'] ?? '?',
                    ]);

                    $rotations[]     = $currentRotation;
                    $currentRotation = null;
                    $currentLegIdx   = 0;
                    $legEvents       = [];

                    if (!$replayOnce) {
                        $replayOnce = true;
                        continue; // Rejouer : cet event peut être T1
                    }

                    $replayOnce = false;
                    $i++;
                    continue;
                }
            }

            // Événement non pertinent (stopped, marker_out, zone inconnue…)
            $replayOnce = false;
            $i++;
        }

        // Rotation ouverte en fin de période → in_progress
        if ($currentRotation !== null) {
            $currentRotation->update([
                'status'   => 'in_progress',
                'is_valid' => false,
            ]);
            Log::info('Rotation en cours (non terminée).', [
                'rotation_id' => $currentRotation->id,
            ]);
            $rotations[] = $currentRotation;
        }

        return $rotations;
    }

    /**
     * Détecte si un événement correspond à une étape ULTÉRIEURE dans le circuit,
     * ce qui signifie que l'étape courante ($expectedLeg) a été sautée/manquée.
     *
     * Exemple :
     *   Circuit : T1 → T2 → CP-Ambodimita → CP-Ambohitrimanjaka → ...
     *   On attend CP-Ambodimita (index 2).
     *   On reçoit CP-Ambohitrimanjaka (index 3).
     *   → CP-Ambodimita a été manqué → retourne le leg manqué (CP-Ambodimita).
     *
     * @return CircuitLeg|null  Le leg manqué, ou null si pas de saut détecté.
     */
    private function eventSkipsExpectedLeg(
        array      $event,
        CircuitLeg $expectedLeg,
        Collection $legs,
        int        $currentLegIdx
    ): ?CircuitLeg {
        // Chercher si cet événement correspond à une étape APRÈS l'étape courante
        $matchingFutureLeg = null;
        $matchingFutureIdx = null;

        foreach ($legs as $idx => $leg) {
            // On ne regarde que les étapes après la courante
            if ($idx <= $currentLegIdx) {
                continue;
            }

            if ($this->eventMatchesLeg($event, $leg)) {
                $matchingFutureLeg = $leg;
                $matchingFutureIdx = $idx;
                break; // Premier match futur suffit
            }
        }

        // Pas de match futur → pas de saut
        if ($matchingFutureLeg === null) {
            return null;
        }

        // Un saut est confirmé : l'étape courante ($expectedLeg) a été manquée
        // et l'événement correspond à une étape plus loin dans le circuit.
        return $expectedLeg; // On retourne le leg qui a été manqué
    }

    /**
     * Vérifie si un événement GPS correspond à une étape du circuit.
     */
    // private function eventMatchesLeg(array $event, CircuitLeg $leg): bool
    // {
    //     $eventType = strtolower($event['normalized_type'] ?? '');
    //     $referenceName = strtolower($event['reference_name'] ?? '');
    //     $legLabel = strtolower($leg->label ?? '');

    //     $typeMatches = match ($leg->event_type) {
    //             'enter_zone'      => str_contains($eventType, 'enter'),
    //             'leave_zone'      => str_contains($eventType, 'leave') || str_contains($eventType, 'exit'),
    //             'pass_checkpoint' => str_contains($eventType, 'marker') || str_contains($eventType, 'checkpoint'),
    //             default           => false,
    //         };
    //     $labelMatches = empty($referenceName) || str_contains($legLabel, $referenceName);
    //     return $typeMatches && $labelMatches;
    // }

    /**
     * Matching événement ↔ étape.
     *
     * Niveau 1 : type normalisé (enter_zone / leave_zone / pass_checkpoint)
     * Niveau 2 : ID BDD local (résolu par nom dans GpsEventMapper)
     *            → fallback comparaison nom brut si zone non importée en BDD
     *
     * JAMAIS de comparaison via gps_zone_id / gps_marker_id.
     */
    private function eventMatchesLeg(array $event, CircuitLeg $leg): bool
    {
        // Niveau 1 : type
        if ($event['normalized_type'] !== $leg->event_type) {
            return false;
        }

        // Niveau 2 : référence zone
        if ($leg->reference_type === 'zone') {
            if (empty($leg->reference_id)) {
                return true; // étape sans référence → permissif
            }

            if (!empty($event['zone_id'])) {
                // Comparaison ID BDD local ↔ ID BDD local
                return (int) $event['zone_id'] === (int) $leg->reference_id;
            }

            // Fallback : zone non importée → comparaison par nom brut
            return $this->nameMatchesZoneId($event['reference_name'] ?? '', (int) $leg->reference_id);
        }

        // Niveau 2 : référence checkpoint
        if ($leg->reference_type === 'checkpoint') {
            if (empty($leg->reference_id)) {
                return true;
            }

            if (!empty($event['checkpoint_id'])) {
                // Comparaison ID BDD local ↔ ID BDD local
                return (int) $event['checkpoint_id'] === (int) $leg->reference_id;
            }

            // Fallback : checkpoint non importé → comparaison par nom brut
            return $this->nameMatchesCheckpointId($event['reference_name'] ?? '', (int) $leg->reference_id);
        }

        return true;
    }

    /**
     * Fallback : nom brut GPS ↔ nom de la zone en BDD (par ID BDD local).
     * Utilisé uniquement si GpsEventMapper n'a pas pu résoudre la zone.
     * JAMAIS via gps_zone_id.
     */
    private function nameMatchesZoneId(string $rawName, int $zoneBddId): bool
    {
        if (empty($rawName)) {
            return false;
        }

        $zone = \App\Models\Zone::find($zoneBddId);

        if (!$zone) {
            return false;
        }

        $a = strtolower(trim($zone->name));
        $b = strtolower(trim($rawName));

        return $a === $b
            || str_contains($a, $b)
            || str_contains($b, $a);
    }

    /**
     * Fallback : nom brut GPS ↔ nom du checkpoint en BDD (par ID BDD local).
     * JAMAIS via gps_marker_id.
     */
    private function nameMatchesCheckpointId(string $rawName, int $cpBddId): bool
    {
        if (empty($rawName)) {
            return false;
        }

        $cp = \App\Models\Checkpoint::find($cpBddId);

        if (!$cp) {
            return false;
        }

        $a = strtolower(trim($cp->name));
        $b = strtolower(trim($rawName));
        
        return $a === $b
            || str_contains($a, $b)
            || str_contains($b, $a);
    }

    /**
     * Vérifie si la référence (zone/checkpoint) de l'événement correspond à l'étape.
     */
    // private function referenceMatchesLeg(string $refId, CircuitLeg $leg): bool
    // {
    //     if (empty($refId)) {
    //         return false;
    //     }

    //     if ($leg->reference_type === 'zone') {
    //         $zone = \App\Models\Zone::find($leg->reference_id);
    //         return $zone && (string) $zone->gps_zone_id === $refId;
    //     }

    //     if ($leg->reference_type === 'checkpoint') {
    //         $cp = \App\Models\Checkpoint::find($leg->reference_id);
    //         return $cp && (string) $cp->gps_marker_id === $refId;
    //     }

    //     return false;
    // }

    /**
     * Détecte si un événement invalide la rotation (sortie du circuit prévu).
     */
    // private function eventInvalidatesRotation(array $event, Circuit $circuit, Collection $legs): bool
    // {
    //     $eventType = strtolower($event['normalized_type'] ?? '');
    //     if (!str_contains($eventType, 'enter')) {
    //         return false;
    //     }

    //     $zoneId = (string) ($event['zone_id'] ?? $event['geofence_id'] ?? '');
    //     if (empty($zoneId)) {
    //         return false;
    //     }

    //     $allowedZoneGpsIds = $legs
    //         ->where('reference_type', 'zone')
    //         ->map(fn($leg) => \App\Models\Zone::find($leg->reference_id)?->gps_zone_id)
    //         ->filter()
    //         ->map(fn($id) => (string) $id)
    //         ->toArray();

    //     return !empty($allowedZoneGpsIds) && !in_array($zoneId, $allowedZoneGpsIds);
    // }

    /**
     * Détecte si l'événement indique une déviation hors circuit.
     *
     * On invalide si :
     *  - C'est une entrée dans une zone (enter_zone) OU un passage checkpoint (pass_checkpoint)
     *  - La zone/checkpoint est connue en BDD (ID résolu par GpsEventMapper via le nom)
     *  - Cette zone/checkpoint N'EST PAS dans la liste des étapes du circuit
     *
     * Jamais d'invalidation si l'ID n'a pas pu être résolu (zone non importée en BDD)
     * → évite les faux positifs sur des zones/checkpoints non importés.
     */
    private function eventInvalidatesRotation(array $event, Circuit $circuit, Collection $legs): bool
    {
        $normalizedType = $event['normalized_type'] ?? '';

        // ── Cas zone : on vérifie les enter_zone ────────────────────────────────
        if ($normalizedType === GpsEventMapper::TYPE_ENTER_ZONE) {

            // Zone non résolue en BDD → on ignore, pas d'annulation
            if (empty($event['zone_id'])) {
                return false;
            }

            $allowedZoneIds = $legs
                ->where('reference_type', 'zone')
                ->pluck('reference_id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->toArray();

            // Aucune zone configurée dans le circuit → pas de restriction
            if (empty($allowedZoneIds)) {
                return false;
            }

            $inCircuit = in_array((int) $event['zone_id'], $allowedZoneIds, true);

            if (!$inCircuit) {
                Log::warning('Déviation zone détectée.', [
                    'zone_id'   => $event['zone_id'],
                    'zone_name' => $event['reference_name'] ?? '?',
                    'allowed'   => $allowedZoneIds,
                ]);
            }

            return !$inCircuit;
        }

        // ── Cas checkpoint : on vérifie les pass_checkpoint ─────────────────────
        if ($normalizedType === GpsEventMapper::TYPE_PASS_CHECKPOINT) {

            // Checkpoint non résolu en BDD → on ignore
            if (empty($event['checkpoint_id'])) {
                return false;
            }

            $allowedCheckpointIds = $legs
                ->where('reference_type', 'checkpoint')
                ->pluck('reference_id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->toArray();

            // Aucun checkpoint configuré → pas de restriction
            if (empty($allowedCheckpointIds)) {
                return false;
            }

            // Un checkpoint hors circuit ne déclenche PAS d'annulation automatique :
            // le véhicule peut passer près d'un checkpoint sans que ce soit une déviation.
            // On retourne false ici — seules les zones hors circuit invalident la rotation.
            return false;
        }

        return false;
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
            'complete'    => TestRawEvents::completeRotationAntonio(),
            'incomplete'  => TestRawEvents::incompleteRotation(),
            'cancelled'   => TestRawEvents::cancelledRotation(),
            'real_sample' => TestRawEvents::realApiSample(),
            default       => [],
        };
    }
}