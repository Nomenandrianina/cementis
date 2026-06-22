<?php

namespace App\Services;

use App\Models\Circuit;
use App\Models\CircuitLeg;
use App\Models\Rotation;
use App\Models\RotationLeg;
use App\Models\Rvehicule;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RotationCalculatorService
{
    // public const TEST_MODE = 'Tsiadino'; // 'complete' | 'incomplete' | 'cancelled' | 'real_sample' | false
    public const TEST_MODE = 'API'; 

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

        if(self::TEST_MODE === 'Tsiadino') {
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
     * Résout la séquence effective des legs en tenant compte des groupes OU.
     *
     * Les legs d'un même group_or sont regroupés en un seul "slot" :
     * le slot est validé dès que l'un des membres matche.
     *
     * Retourne une Collection de slots, chaque slot étant :
     *   ['legs' => Collection<CircuitLeg>, 'is_or' => bool, 'group_or' => string|null]
     *
     * Exemple pour un circuit T1(OU)→T2→CP→T5(OU) :
     *   slot 0 : is_or=true,  legs=[Andranomena, Ilanivato]
     *   slot 1 : is_or=false, legs=[leave_zone Andranomena]
     *   slot 2 : is_or=false, legs=[pass_checkpoint MBS]
     *   slot 3 : is_or=true,  legs=[enter_zone Andranomena, enter_zone Ilanivato]
     */
    private function resolveLegsSequence(\Illuminate\Support\Collection $legs): array
    {
        $slots    = [];
        $seenGroups = [];

        foreach ($legs as $leg) {
            if ($leg->hasOrGroup()) {
                $group = $leg->group_or;

                if (isset($seenGroups[$group])) {
                    // Ajouter au slot existant
                    $slots[$seenGroups[$group]]['legs']->push($leg);
                } else {
                    // Créer un nouveau slot OU
                    $slotIdx = count($slots);
                    $slots[] = [
                        'legs'     => collect([$leg]),
                        'is_or'    => true,
                        'group_or' => $group,
                    ];
                    $seenGroups[$group] = $slotIdx;
                }
            } else {
                // Slot simple (un seul leg)
                $slots[] = [
                    'legs'     => collect([$leg]),
                    'is_or'    => false,
                    'group_or' => null,
                ];
            }
        }

        return $slots;
    }

    /**
     * Vérifie si un événement correspond à AU MOINS UN leg du slot.
     * Pour les slots simples (is_or=false), identique à eventMatchesLeg.
     * Pour les slots OU (is_or=true), matche si l'un des legs matche.
     *
     * Retourne le CircuitLeg qui a matché, ou null.
     */
    private function slotMatchesEvent(array $slot, array $event): ?CircuitLeg
    {
        foreach ($slot['legs'] as $leg) {
            if ($this->eventMatchesLeg($event, $leg)) {
                return $leg;
            }
        }
        return null;
    }

    /**
     * Un slot est optionnel si TOUS ses legs sont optionnels.
     * (Pour un slot OU : si n'importe lequel est obligatoire → slot obligatoire)
     */
    private function slotIsOptional(array $slot): bool
    {
        foreach ($slot['legs'] as $leg) {
            if (!$leg->isOptional()) {
                return false;
            }
        }
        return true;
    }

    /**
     * extractRotations — version finale intégrant :
     * - Saut d'étapes optionnelles (checkpoint 'autre' ou leg->optional)
     * - Sous-zones couvertes par zone mère
     * - Replay T5 = T1
     * - Anti-boucle $replayOnce
     */
    /**
     * extractRotations — version slots OU
     * Utilise resolveLegsSequence() pour regrouper les legs en slots.
     * Un slot OU est validé dès qu'UN de ses legs matche.
     */
    // private function extractRotations(
    //     array      $events,
    //     Collection $legs,
    //     $vehicle,
    //     Circuit    $circuit,
    //     string     $countedMonth
    // ): array {
    //     $rotations       = [];
    //     $currentSlotIdx  = 0;
    //     $currentRotation = null;
    //     $legEvents       = [];

    //     $slots      = $this->resolveLegsSequence($legs);
    //     $totalSlots = count($slots);

    //     $i          = 0;
    //     $totalEvts  = count($events);
    //     $replayOnce = false;

    //     while ($i < $totalEvts) {
    //         $event        = $events[$i];
    //         $expectedSlot = $slots[$currentSlotIdx] ?? null;

    //         if ($expectedSlot === null) {
    //             $i++;
    //             $replayOnce = false;
    //             continue;
    //         }

    //         // ── MATCH ────────────────────────────────────────────────────────────
    //         $matchedLeg = $this->slotMatchesEvent($expectedSlot, $event);

    //         if ($matchedLeg !== null) {
    //             $replayOnce = false;

    //             // Début de rotation (slot 0)
    //             if ($currentSlotIdx === 0) {
    //                 $currentRotation = Rotation::create([
    //                     'rvehicule_id'  => $vehicle->id,
    //                     'circuit_id'    => $circuit->id,
    //                     'started_at'    => $event['dt'],
    //                     'status'        => 'in_progress',
    //                     'counted_month' => $countedMonth,
    //                     'is_valid'      => false,
    //                     'raw_events'    => [],
    //                 ]);
    //                 $legEvents = [];
    //                 Log::info('Rotation démarrée.', [
    //                     'rotation_id' => $currentRotation->id,
    //                     'dt'          => $event['dt'],
    //                 ]);
    //             }

    //             if ($currentRotation) {
    //                 $occurredAt        = Carbon::parse($event['dt']);
    //                 $prevOccurredAt    = !empty($legEvents)
    //                     ? Carbon::parse(end($legEvents)['dt'])
    //                     : null;
    //                 $durationSincePrev = $prevOccurredAt
    //                     ? (int) $prevOccurredAt->diffInSeconds($occurredAt)
    //                     : null;

    //                 RotationLeg::create([
    //                     'rotation_id'                     => $currentRotation->id,
    //                     'circuit_leg_id'                  => $matchedLeg->id,
    //                     'occurred_at'                     => $occurredAt,
    //                     'lat'                             => $event['lat'] ?? null,
    //                     'lng'                             => $event['lng'] ?? null,
    //                     'duration_since_previous_seconds' => $durationSincePrev,
    //                     'raw_event'                       => $event['raw'] ?? $event,
    //                 ]);

    //                 $legEvents[] = $event;
    //                 $currentSlotIdx++;

    //                 Log::debug('Slot validé.', [
    //                     'leg'        => $matchedLeg->label,
    //                     'slot_idx'   => $currentSlotIdx,
    //                     'total_slots'=> $totalSlots,
    //                     'is_or'      => $expectedSlot['is_or'],
    //                     'dt'         => $event['dt'],
    //                 ]);

    //                 // ── Rotation complète ────────────────────────────────────────
    //                 if ($currentSlotIdx >= $totalSlots) {
    //                     $completedAt = Carbon::parse($event['dt']);
    //                     $duration    = (int) Carbon::parse($currentRotation->started_at)
    //                                             ->diffInSeconds($completedAt);

    //                     $currentRotation->update([
    //                         'completed_at'     => $completedAt,
    //                         'duration_seconds' => $duration,
    //                         'status'           => 'completed',
    //                         'is_valid'         => true,
    //                     ]);

    //                     Log::info('Rotation complète.', [
    //                         'rotation_id'      => $currentRotation->id,
    //                         'duration_seconds' => $duration,
    //                     ]);

    //                     $rotations[]     = $currentRotation;
    //                     $currentRotation = null;
    //                     $currentSlotIdx  = 0;
    //                     $legEvents       = [];

    //                     if (!$replayOnce) {
    //                         $replayOnce = true;
    //                         continue;
    //                     }
    //                     $replayOnce = false;
    //                     $i++;
    //                     continue;
    //                 }
    //             }

    //             $i++;
    //             continue;
    //         }

    //         // ── PAS DE MATCH ─────────────────────────────────────────────────────

    //         if ($currentRotation !== null) {

    //             // Cas 1 : saut d'étape (slot)
    //             $skipResult = $this->slotSkipsExpectedSlot(
    //                 $event, $expectedSlot, $slots, $currentSlotIdx
    //             );

    //             if ($skipResult['leg'] !== null) {
    //                 // Sous-zones couvertes par zone mère ?
    //                 $coveringParentEvent = $this->getCoveringParentEventForSlot(
    //                     $skipResult['leg'], $legEvents
    //                 );

    //                 if ($coveringParentEvent !== null) {
    //                     $parentZoneId = \App\Models\Zone::find($skipResult['leg']->reference_id)?->parent_id;

    //                     while ($currentSlotIdx < $totalSlots) {
    //                         $nextSlot = $slots[$currentSlotIdx] ?? null;
    //                         if ($nextSlot === null) break;

    //                         // On ne couvre que les slots simples avec une zone fille
    //                         $nextLeg = $nextSlot['legs']->first();
    //                         $isSubzoneOfSameParent =
    //                             !$nextSlot['is_or']
    //                             && $nextLeg !== null
    //                             && $nextLeg->reference_type === 'zone'
    //                             && !empty($nextLeg->reference_id)
    //                             && \App\Models\Zone::find($nextLeg->reference_id)?->parent_id === $parentZoneId;

    //                         if (!$isSubzoneOfSameParent) break;

    //                         RotationLeg::create([
    //                             'rotation_id'                     => $currentRotation->id,
    //                             'circuit_leg_id'                  => $nextLeg->id,
    //                             'occurred_at'                     => Carbon::parse($coveringParentEvent['dt']),
    //                             'lat'                             => $coveringParentEvent['lat'] ?? null,
    //                             'lng'                             => $coveringParentEvent['lng'] ?? null,
    //                             'duration_since_previous_seconds' => null,
    //                             'raw_event'                       => $coveringParentEvent['raw'] ?? $coveringParentEvent,
    //                             'skipped_by_parent'               => true,
    //                         ]);

    //                         Log::info('Sous-zone couverte par zone mère.', [
    //                             'rotation_id'    => $currentRotation->id,
    //                             'skipped_leg'    => $nextLeg->label,
    //                             'parent_zone_id' => $parentZoneId,
    //                         ]);

    //                         $currentSlotIdx++;
    //                     }

    //                     if (!$replayOnce) { $replayOnce = true; continue; }
    //                     $replayOnce = false;
    //                     $i++;
    //                     continue;
    //                 }

    //                 // Étape obligatoire manquée → annulation
    //                 $currentRotation->update([
    //                     'status'              => 'cancelled',
    //                     'is_valid'            => false,
    //                     'invalidation_reason' => sprintf(
    //                         'Étape manquée : "%s" (ordre %d) — non validée avant "%s" à %s',
    //                         $skipResult['leg']->label,
    //                         $skipResult['leg']->order,
    //                         $event['reference_name'] ?? '?',
    //                         $event['dt']             ?? '?'
    //                     ),
    //                 ]);

    //                 Log::warning('Rotation annulée — étape obligatoire manquée.', [
    //                     'rotation_id' => $currentRotation->id,
    //                     'missed_leg'  => $skipResult['leg']->label,
    //                 ]);

    //                 $rotations[]     = $currentRotation;
    //                 $currentRotation = null;
    //                 $currentSlotIdx  = 0;
    //                 $legEvents       = [];

    //                 if (!$replayOnce) { $replayOnce = true; continue; }
    //                 $replayOnce = false;
    //                 $i++;
    //                 continue;
    //             }

    //             // Saut d'étapes optionnelles → avancer l'index de slot
    //             if ($skipResult['advance_to'] !== null) {
    //                 $currentSlotIdx = $skipResult['advance_to'];
    //                 $replayOnce = false;
    //                 continue;
    //             }

    //             // Cas 2 : déviation hors circuit
    //             if ($this->eventInvalidatesRotation($event, $circuit, $legs)) {
    //                 $currentRotation->update([
    //                     'status'              => 'cancelled',
    //                     'is_valid'            => false,
    //                     'invalidation_reason' => sprintf(
    //                         'Déviation hors circuit : "%s" à %s',
    //                         $event['reference_name'] ?? '?',
    //                         $event['dt']             ?? '?'
    //                     ),
    //                 ]);

    //                 Log::warning('Rotation annulée — déviation.', [
    //                     'rotation_id' => $currentRotation->id,
    //                     'zone'        => $event['reference_name'] ?? '?',
    //                 ]);

    //                 $rotations[]     = $currentRotation;
    //                 $currentRotation = null;
    //                 $currentSlotIdx  = 0;
    //                 $legEvents       = [];

    //                 if (!$replayOnce) { $replayOnce = true; continue; }
    //                 $replayOnce = false;
    //                 $i++;
    //                 continue;
    //             }
    //         }

    //         $replayOnce = false;
    //         $i++;
    //     }

    //     if ($currentRotation !== null) {
    //         $currentRotation->update(['status' => 'in_progress', 'is_valid' => false]);
    //         Log::info('Rotation en cours.', ['rotation_id' => $currentRotation->id]);
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
        $currentSlotIdx  = 0;
        $currentRotation = null;
        $legEvents       = [];

        $slots      = $this->resolveLegsSequence($legs);
        $totalSlots = count($slots);
        $delaySeconds = Setting::get('parent_zone_delay_hours', 24) * 3600;

        $i          = 0;
        $totalEvts  = count($events);
        $replayOnce = false;

        while ($i < $totalEvts) {
            $event        = $events[$i];
            $expectedSlot = $slots[$currentSlotIdx] ?? null;

            if ($expectedSlot === null) {
                $i++;
                $replayOnce = false;
                continue;
            }

            // ── MATCH ────────────────────────────────────────────────────────────
            $matchedLeg = $this->slotMatchesEvent($expectedSlot, $event);

            if ($matchedLeg !== null) {

                // ── Vérification 24h pour le dernier slot ────────────────────────
                if ($currentSlotIdx === $totalSlots - 1 && $currentRotation !== null) {
                    $parentEnteredAt = null;

                    foreach ($legEvents as $pastEvent) {
                        foreach ($expectedSlot['legs'] as $slotLeg) {
                            if ($slotLeg->reference_type !== 'zone') continue;
                            $zone = \App\Models\Zone::find($slotLeg->reference_id);
                            if (!$zone || empty($zone->parent_id)) continue;

                            if (
                                $pastEvent['normalized_type'] === GpsEventMapper::TYPE_ENTER_ZONE &&
                                !empty($pastEvent['zone_id']) &&
                                (int) $pastEvent['zone_id'] === (int) $zone->parent_id
                            ) {
                                $parentEnteredAt = Carbon::parse($pastEvent['dt']);
                                break 2;
                            }
                        }
                    }

                    if ($parentEnteredAt !== null) {
                        $eventTime   = Carbon::parse($event['dt']);
                        $diffSeconds = $parentEnteredAt->diffInSeconds($eventTime, false);

                        if ($diffSeconds > $delaySeconds) {
                            $completedAt = $parentEnteredAt;
                            $duration    = (int) Carbon::parse($currentRotation->started_at)
                                                    ->diffInSeconds($completedAt);

                            $currentRotation->update([
                                'completed_at'        => $completedAt,
                                'duration_seconds'    => $duration,
                                'status'              => 'acceptable',
                                'is_valid'            => true,
                                'invalidation_reason' => sprintf(
                                    'Fin acceptée via zone parent — sous-zone atteinte après 24h (%s)',
                                    $event['dt'] ?? '?'
                                ),
                            ]);

                            Log::info('Rotation acceptable — sous-zone après 24h, fin via zone parent.', [
                                'rotation_id'    => $currentRotation->id,
                                'parent_entered' => $parentEnteredAt->toDateTimeString(),
                                'subzone_at'     => $event['dt'],
                                'diff_seconds'   => $diffSeconds,
                            ]);

                            $rotations[]     = $currentRotation;
                            $currentRotation = null;
                            $currentSlotIdx  = 0;
                            $legEvents       = [];

                            if (!$replayOnce) { $replayOnce = true; continue; }
                            $replayOnce = false;
                            $i++;
                            continue;
                        }
                    }
                }
                // ── Fin vérification 24h ─────────────────────────────────────────

                $replayOnce = false;

                // Début de rotation (slot 0)
                if ($currentSlotIdx === 0) {
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
                    ]);
                }

                if ($currentRotation) {
                    $occurredAt        = Carbon::parse($event['dt']);
                    $prevOccurredAt    = !empty($legEvents)
                        ? Carbon::parse(end($legEvents)['dt'])
                        : null;
                    $durationSincePrev = $prevOccurredAt
                        ? (int) $prevOccurredAt->diffInSeconds($occurredAt)
                        : null;

                    RotationLeg::create([
                        'rotation_id'                     => $currentRotation->id,
                        'circuit_leg_id'                  => $matchedLeg->id,
                        'occurred_at'                     => $occurredAt,
                        'lat'                             => $event['lat'] ?? null,
                        'lng'                             => $event['lng'] ?? null,
                        'duration_since_previous_seconds' => $durationSincePrev,
                        'raw_event'                       => $event['raw'] ?? $event,
                    ]);

                    $legEvents[] = $event;
                    $currentSlotIdx++;

                    Log::debug('Slot validé.', [
                        'leg'         => $matchedLeg->label,
                        'slot_idx'    => $currentSlotIdx,
                        'total_slots' => $totalSlots,
                        'is_or'       => $expectedSlot['is_or'],
                        'dt'          => $event['dt'],
                    ]);

                    // ── Rotation complète ────────────────────────────────────────
                    if ($currentSlotIdx >= $totalSlots) {
                        $completedAt = Carbon::parse($event['dt']);
                        $duration    = (int) Carbon::parse($currentRotation->started_at)
                                                ->diffInSeconds($completedAt);

                        $currentRotation->update([
                            'completed_at'     => $completedAt,
                            'duration_seconds' => $duration,
                            'status'           => 'completed',
                            'is_valid'         => true,
                        ]);

                        Log::info('Rotation complète.', [
                            'rotation_id'      => $currentRotation->id,
                            'duration_seconds' => $duration,
                        ]);

                        $rotations[]     = $currentRotation;
                        $currentRotation = null;
                        $currentSlotIdx  = 0;
                        $legEvents       = [];

                        if (!$replayOnce) {
                            $replayOnce = true;
                            continue;
                        }
                        $replayOnce = false;
                        $i++;
                        continue;
                    }
                }

                $i++;
                continue;
            }

            // ── PAS DE MATCH ─────────────────────────────────────────────────────

            if ($currentRotation !== null) {

                // ── Cas spécial : dernier slot (end) non matché mais zone parent entrée ──
                if ($currentSlotIdx === $totalSlots - 1) {
                    $lastSlot      = $slots[$currentSlotIdx];
                    $parentMatched = false;

                    foreach ($lastSlot['legs'] as $slotLeg) {
                        if ($slotLeg->reference_type !== 'zone') continue;
                        $zone = \App\Models\Zone::find($slotLeg->reference_id);
                        if (!$zone || empty($zone->parent_id)) continue;

                        if (
                            $event['normalized_type'] === GpsEventMapper::TYPE_ENTER_ZONE &&
                            !empty($event['zone_id']) &&
                            (int) $event['zone_id'] === (int) $zone->parent_id
                        ) {
                            $parentMatched = true;
                            break;
                        }
                    }

                    if ($parentMatched) {
                        $found     = false;
                        $eventTime = Carbon::parse($event['dt']);

                        for ($j = $i + 1; $j < $totalEvts; $j++) {
                            $futureEvent = $events[$j];
                            $futureTime  = Carbon::parse($futureEvent['dt']);

                            if ($futureTime->diffInSeconds($eventTime, false) > $delaySeconds) break;

                            if ($this->slotMatchesEvent($lastSlot, $futureEvent) !== null) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $completedAt = Carbon::parse($event['dt']);
                            $duration    = (int) Carbon::parse($currentRotation->started_at)
                                                    ->diffInSeconds($completedAt);

                            $currentRotation->update([
                                'completed_at'        => $completedAt,
                                'duration_seconds'    => $duration,
                                'status'              => 'acceptable',
                                'is_valid'            => true,
                                'invalidation_reason' => sprintf(
                                    'Fin acceptée via zone parent "%s" — sous-zone non atteinte dans les 24h',
                                    $event['reference_name'] ?? '?'
                                ),
                            ]);

                            Log::info('Rotation acceptable — fin via zone parent.', [
                                'rotation_id' => $currentRotation->id,
                                'zone_parent' => $event['reference_name'] ?? '?',
                            ]);

                            $rotations[]     = $currentRotation;
                            $currentRotation = null;
                            $currentSlotIdx  = 0;
                            $legEvents       = [];

                            if (!$replayOnce) { $replayOnce = true; continue; }
                            $replayOnce = false;
                            $i++;
                            continue;
                        }
                        // Sous-zone trouvée dans les 24h → laisser le flux normal continuer
                        $replayOnce = false;
                        $i++;
                        continue;
                    }
                }

                // ── Cas 1 : saut d'étape (slot) ──────────────────────────────────
                $skipResult = $this->slotSkipsExpectedSlot(
                    $event, $expectedSlot, $slots, $currentSlotIdx
                );

                if ($skipResult['leg'] !== null) {
                    $coveringParentEvent = $this->getCoveringParentEventForSlot(
                        $skipResult['leg'], $legEvents
                    );

                    if ($coveringParentEvent !== null) {
                        $parentZoneId = \App\Models\Zone::find($skipResult['leg']->reference_id)?->parent_id;

                        while ($currentSlotIdx < $totalSlots) {
                            $nextSlot = $slots[$currentSlotIdx] ?? null;
                            if ($nextSlot === null) break;

                            $nextLeg = $nextSlot['legs']->first();
                            $isSubzoneOfSameParent =
                                !$nextSlot['is_or']
                                && $nextLeg !== null
                                && $nextLeg->reference_type === 'zone'
                                && !empty($nextLeg->reference_id)
                                && \App\Models\Zone::find($nextLeg->reference_id)?->parent_id === $parentZoneId;

                            if (!$isSubzoneOfSameParent) break;

                            RotationLeg::create([
                                'rotation_id'                     => $currentRotation->id,
                                'circuit_leg_id'                  => $nextLeg->id,
                                'occurred_at'                     => Carbon::parse($coveringParentEvent['dt']),
                                'lat'                             => $coveringParentEvent['lat'] ?? null,
                                'lng'                             => $coveringParentEvent['lng'] ?? null,
                                'duration_since_previous_seconds' => null,
                                'raw_event'                       => $coveringParentEvent['raw'] ?? $coveringParentEvent,
                                'skipped_by_parent'               => true,
                            ]);

                            Log::info('Sous-zone couverte par zone mère.', [
                                'rotation_id'    => $currentRotation->id,
                                'skipped_leg'    => $nextLeg->label,
                                'parent_zone_id' => $parentZoneId,
                            ]);

                            $currentSlotIdx++;
                        }

                        if (!$replayOnce) { $replayOnce = true; continue; }
                        $replayOnce = false;
                        $i++;
                        continue;
                    }

                    // Étape obligatoire manquée → annulation
                    $currentRotation->update([
                        'status'              => 'cancelled',
                        'is_valid'            => false,
                        'invalidation_reason' => sprintf(
                            'Étape manquée : "%s" (ordre %d) — non validée avant "%s" à %s',
                            $skipResult['leg']->label,
                            $skipResult['leg']->order,
                            $event['reference_name'] ?? '?',
                            $event['dt']             ?? '?'
                        ),
                    ]);

                    Log::warning('Rotation annulée — étape obligatoire manquée.', [
                        'rotation_id' => $currentRotation->id,
                        'missed_leg'  => $skipResult['leg']->label,
                    ]);

                    $rotations[]     = $currentRotation;
                    $currentRotation = null;
                    $currentSlotIdx  = 0;
                    $legEvents       = [];

                    if (!$replayOnce) { $replayOnce = true; continue; }
                    $replayOnce = false;
                    $i++;
                    continue;
                }

                // Saut d'étapes optionnelles → avancer l'index de slot
                if ($skipResult['advance_to'] !== null) {
                    $currentSlotIdx = $skipResult['advance_to'];
                    $replayOnce     = false;
                    continue;
                }

                // ── Cas 2 : déviation hors circuit ───────────────────────────────
                if ($this->eventInvalidatesRotation($event, $circuit, $legs)) {
                    // Chercher en avant si le slot attendu matche avant la fin des événements
                    // (les zones/checkpoints hors-circuit entre les deux sont ignorés comme bruit)
                    $found = false;
                    for ($j = $i + 1; $j < $totalEvts; $j++) {
                        if ($this->slotMatchesEvent($expectedSlot, $events[$j]) !== null) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        Log::info('Déviation tolérée — étape attendue trouvée plus loin dans le trajet.', [
                            'rotation_id' => $currentRotation->id,
                            'zone'        => $event['reference_name'] ?? '?',
                            'dt'          => $event['dt'] ?? '?',
                        ]);

                        $replayOnce = false;
                        $i++;
                        continue;
                    }

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
                    $currentSlotIdx  = 0;
                    $legEvents       = [];

                    if (!$replayOnce) { $replayOnce = true; continue; }
                    $replayOnce = false;
                    $i++;
                    continue;
                }
            }

            $replayOnce = false;
            $i++;
        }

        // Rotation non terminée en fin de période
        if ($currentRotation !== null) {
            $currentRotation->update(['status' => 'in_progress', 'is_valid' => false]);
            Log::info('Rotation en cours.', ['rotation_id' => $currentRotation->id]);
            $rotations[] = $currentRotation;
        }

        return $rotations;
    }

    /**
     * Version slot de getCoveringParentEvent.
     * Reçoit le CircuitLeg manqué (premier du slot) et les legEvents déjà validés.
     */
    private function getCoveringParentEventForSlot(
        CircuitLeg $missedLeg,
        array      $legEvents
    ): ?array {
        // Réutilise la logique existante — inchangée
        return $this->getCoveringParentEvent($missedLeg, $legEvents);
    }

    

    /**
     * Retourne l'événement GPS de la zone mère si le leg manqué est
     * une sous-zone dont le parent a déjà été validé dans cette rotation.
     *
     * Utilisé pour créer les RotationLeg skippés avec l'horodatage
     * du passage par la zone mère.
     *
     * Règle applicable uniquement aux zones (jamais aux checkpoints).
     *
     * @param  CircuitLeg $missedLeg   Étape qui a été manquée
     * @param  array      $legEvents   Événements GPS déjà validés dans la rotation
     * @return array|null              Événement GPS du passage par la zone mère, ou null
     */
    private function getCoveringParentEvent(
        CircuitLeg $missedLeg,
        array      $legEvents
    ): ?array {
        // Checkpoints : jamais couverts par cette règle
        if ($missedLeg->reference_type !== 'zone' || empty($missedLeg->reference_id)) {
            return null;
        }

        $zone = \App\Models\Zone::find($missedLeg->reference_id);

        if (!$zone || empty($zone->parent_id)) {
            return null; // Pas de zone mère → règle inapplicable
        }

        // Chercher dans les événements déjà validés si la zone mère y figure
        foreach ($legEvents as $pastEvent) {
            if (
                isset($pastEvent['zone_id'])
                && (int) $pastEvent['zone_id'] === (int) $zone->parent_id
            ) {
                return $pastEvent; // On retourne l'événement mère pour horodater
            }
        }

        return null;
    }

    /**
     * Détecte si un événement correspond à une étape ULTÉRIEURE dans le circuit.
     * 
     * NOUVEAU : si toutes les étapes sautées entre currentLegIdx et le match futur
     * sont optionnelles → on ne retourne pas le leg manqué (pas d'annulation),
     * mais on avance currentLegIdx jusqu'au match futur.
     *
     * @return array{leg: CircuitLeg|null, advance_to: int|null}
     *   - leg = null → pas de saut, ou saut optionnel
     *   - leg = CircuitLeg → étape OBLIGATOIRE manquée → annuler
     *   - advance_to = index futur à atteindre (quand saut optionnel)
     */
    /**
     * Détecte si un événement matche un slot ULTÉRIEUR dans la séquence.
     * Identique à eventSkipsExpectedLeg mais opère sur les slots.
     *
     * @return array{leg: CircuitLeg|null, advance_to: int|null}
     */
    private function slotSkipsExpectedSlot(
        array $event,
        array $expectedSlot,
        array $slots,
        int   $currentSlotIdx
    ): array {
        $matchingFutureSlotIdx = null;

        foreach ($slots as $idx => $slot) {
            if ($idx <= $currentSlotIdx) continue;
            if ($this->slotMatchesEvent($slot, $event) !== null) {
                $matchingFutureSlotIdx = $idx;
                break;
            }
        }

        if ($matchingFutureSlotIdx === null) {
            return ['leg' => null, 'advance_to' => null];
        }

        // Inspecter chaque slot sauté
        for ($idx = $currentSlotIdx; $idx < $matchingFutureSlotIdx; $idx++) {
            $skippedSlot = $slots[$idx] ?? null;
            if ($skippedSlot === null) continue;

            if (!$this->slotIsOptional($skippedSlot)) {
                // Retourner le premier leg obligatoire manqué
                return ['leg' => $skippedSlot['legs']->first(), 'advance_to' => null];
            }

            Log::info('Slot optionnel sauté (ignoré).', [
                'group_or' => $skippedSlot['group_or'] ?? 'n/a',
            ]);
        }

        return ['leg' => null, 'advance_to' => $matchingFutureSlotIdx];
    }

    /**
     * Un leg est optionnel si :
     * 1. Son champ optional = true (configuré dans le circuit)
     * 2. OU si le checkpoint associé est de type 'optionnel'
     * 3. OU si le zone associé est d'option 'optionnel'  (couvre enter ET leave de la même zone).
     */
    private function legIsOptional(CircuitLeg $leg): bool
    {
        return $leg->isOptional();
    }



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

            if ($inCircuit) {
                return false;
            }

            // ── NOUVEAU : zone hors-circuit mais sous-zone d'une zone du circuit ? ──
            $zone = \App\Models\Zone::find((int) $event['zone_id']);

            if ($zone && !empty($zone->parent_id)) {
                $parentIsInCircuit = in_array((int) $zone->parent_id, $allowedZoneIds, true);

                if ($parentIsInCircuit) {
                    Log::debug('Zone hors-circuit tolérée : sous-zone d\'une zone du circuit.', [
                        'zone_id'        => $event['zone_id'],
                        'zone_name'      => $event['reference_name'] ?? '?',
                        'parent_zone_id' => $zone->parent_id,
                    ]);
                    return false; // Pas de déviation
                }
            }
            // ── FIN NOUVEAU ──────────────────────────────────────────────────────────

            Log::warning('Déviation zone détectée.', [
                'zone_id'   => $event['zone_id'],
                'zone_name' => $event['reference_name'] ?? '?',
                'allowed'   => $allowedZoneIds,
            ]);

            return true;
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
        $duration    = Carbon::parse($rotation->started_at)->diffInSeconds($completedAt);

        $rotation->update([
            'completed_at'     => $completedAt,
            'duration_seconds' => $duration,
            'status'           => 'completed',
            'is_valid'         => true,
        ]);
    }

    private function getTestEvents(): array
    {
        return match (self::TEST_MODE) {
            'complete'    => TestRawEvents::completeRotationAntonio(),
            'Tsiadino'    => TestRawEvents::completeRotationTsiadino(),
            'incomplete'  => TestRawEvents::incompleteRotation(),
            'cancelled'   => TestRawEvents::cancelledRotation(),
            'real_sample' => TestRawEvents::realApiSample(),
            default       => [],
        };
    }
}