<?php

namespace App\Services;

use App\Models\Checkpoint;
use App\Models\Zone;
use Illuminate\Support\Collection;

/**
 * GpsEventMapper – basé sur le format réel de l'API M-TEC Tracking
 *
 * Format réel d'un événement (tableau indexé) :
 * [0]  type         : "zone_in", "zone_out", "marker_in", "marker_out", "stopped", ...
 * [1]  description  : "Entrée zone (Andranomena)", "Check point entrant (Check point MBS)", ...
 * [2]  imei         : "865135061356851"
 * [3]  name         : "Sorento_Tsihadino "
 * [4]  dt           : "2026-04-01 05:03:36"
 * [5]  lat          : "-18.855324"
 * [6]  lng          : "47.480787"
 * [7]  altitude     : "0"
 * [8]  angle        : "71"
 * [9]  speed        : "7"
 * [10] params       : {...}
 */
class GpsEventMapper
{
    public const TYPE_ENTER_ZONE      = 'enter_zone';
    public const TYPE_LEAVE_ZONE      = 'leave_zone';
    public const TYPE_PASS_CHECKPOINT = 'pass_checkpoint';

    // marker_out ignoré volontairement : on compte seulement l'entrée dans le checkpoint
    private const RAW_TYPE_MAP = [
        'zone_in'   => self::TYPE_ENTER_ZONE,
        'zone_out'  => self::TYPE_LEAVE_ZONE,
        'marker_in' => self::TYPE_PASS_CHECKPOINT,
    ];

    private ?Collection $zones       = null;
    private ?Collection $checkpoints = null;

    /**
     * Normalise le tableau brut retourné par OBJECT_GET_EVENTS.
     * Chaque item est un tableau indexé [type, desc, imei, name, dt, lat, lng, ...].
     */
    public function normalize(array $rawEvents): array
    {
        $this->loadReferences();
        $normalized = [];

        foreach ($rawEvents as $raw) {
            $mapped = $this->mapEvent($raw);
            if ($mapped !== null) {
                $normalized[] = $mapped;
            }
        }

        usort($normalized, fn($a, $b) => strcmp($a['dt'], $b['dt']));
        return $normalized;
    }

    private function mapEvent(array $raw): ?array
    {
        $rawType     = strtolower($raw[0] ?? '');
        $description = $raw[1] ?? '';
        $dt          = $raw[4] ?? null;
        $lat         = isset($raw[5]) ? (float) $raw[5] : null;
        $lng         = isset($raw[6]) ? (float) $raw[6] : null;
        $params      = $raw[10] ?? [];

        if (!isset(self::RAW_TYPE_MAP[$rawType])) {
            return null;
        }

        $normalizedType = self::RAW_TYPE_MAP[$rawType];
        $referenceName  = $this->extractNameFromDescription($description);

        $zoneId       = null;
        $checkpointId = null;

        if (in_array($normalizedType, [self::TYPE_ENTER_ZONE, self::TYPE_LEAVE_ZONE])) {
            $zoneId = $this->resolveZoneByName($referenceName);
        } else {
            $checkpointId = $this->resolveCheckpointByName($referenceName);
        }

        return [
            'normalized_type' => $normalizedType,
            'raw_type'        => $rawType,
            'reference_name'  => $referenceName,
            'zone_id'         => $zoneId,
            'checkpoint_id'   => $checkpointId,
            'dt'              => $dt,
            'lat'             => $lat,
            'lng'             => $lng,
            'params'          => $params,
            'raw'             => $raw,
        ];
    }

    /**
     * Extrait le nom entre parenthèses de la description.
     * "Entrée zone (Andranomena)"               → "Andranomena"
     * "Check point entrant (Check point MBS)"   → "Check point MBS"
     */
    public function extractNameFromDescription(string $description): string
    {
        if (preg_match('/\(([^)]+)\)/', $description, $m)) {
            return trim($m[1]);
        }
        return trim($description);
    }

    private function resolveZoneByName(string $name): ?int
    {
        if (empty($name)) return null;
        $zone = $this->zones?->first(fn($z) => strcasecmp(trim($z->name), trim($name)) === 0)
             ?? $this->zones?->first(fn($z) =>
                    str_contains(strtolower($z->name), strtolower($name)) ||
                    str_contains(strtolower($name), strtolower($z->name))
                );
        return $zone?->id;
    }

    private function resolveCheckpointByName(string $name): ?int
    {
        if (empty($name)) return null;
        $cp = $this->checkpoints?->first(fn($c) => strcasecmp(trim($c->name), trim($name)) === 0)
           ?? $this->checkpoints?->first(fn($c) =>
                  str_contains(strtolower($c->name), strtolower($name)) ||
                  str_contains(strtolower($name), strtolower($c->name))
              );
        return $cp?->id;
    }

    private function loadReferences(): void
    {
        if ($this->zones === null) {
            $this->zones       = Zone::where('active', true)->get(['id', 'name']);
            $this->checkpoints = Checkpoint::where('active', true)->get(['id', 'name']);
        }
    }

    public function reloadReferences(): void
    {
        $this->zones       = null;
        $this->checkpoints = null;
    }
}
