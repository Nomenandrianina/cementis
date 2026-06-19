<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GpsApiService
{
    private string $baseUrl;
    private string $apiKey;
    private int $cacheTtl = 300; // 5 minutes

    public function __construct()
    {
        $this->baseUrl = config('gps.base_url', 'https://www.m-tectracking.mg/api/api.php');
        $this->apiKey  = config('gps.api_key', '60EBAD7DC468E6AB6D0DAB89CAAA1073');
    }

    // -------------------------------------------------------------------------
    // Véhicules
    // -------------------------------------------------------------------------

    /**
     * Récupère tous les objets (véhicules) depuis l'API GPS.
     */
    public function getObjects(): array
    {
        return Cache::remember('gps.objects', $this->cacheTtl, function () {
            $response = $this->call([
                'api'  => 'user',
                'ver'  => '1.0',
                'cmd'  => 'USER_GET_OBJECTS',
            ]);
            return is_array($response) ? $response : [];
        });
    }

    // -------------------------------------------------------------------------
    // Zones
    // -------------------------------------------------------------------------

    /**
     * Récupère toutes les zones définies dans la plateforme GPS.
     */
    public function getZones(): array
    {
        return Cache::remember('gps.zones', $this->cacheTtl, function () {
            $response = $this->call([
                'api' => 'user',
                'ver' => '1.0',
                'cmd' => 'USER_GET_ZONES',
            ]);
            return is_array($response) ? $response : [];
        });
    }

    // -------------------------------------------------------------------------
    // Marqueurs / Checkpoints
    // -------------------------------------------------------------------------

    /**
     * Récupère tous les marqueurs (checkpoints) depuis l'API GPS.
     */
    public function getMarkers(): array
    {
        return Cache::remember('gps.markers', $this->cacheTtl, function () {
            $response = $this->call([
                'api' => 'user',
                'ver' => '1.0',
                'cmd' => 'USER_GET_MARKERS',
            ]);
            return is_array($response) ? $response : [];
        });
    }

    // -------------------------------------------------------------------------
    // Événements
    // -------------------------------------------------------------------------

    /**
     * Récupère les événements (entrée/sortie zone, passage checkpoint) pour
     * un véhicule sur une période donnée.
     *
     * @param string $imei     IMEI du véhicule
     * @param string $dateFrom Format: YYYYMMDDHHmmss
     * @param string $dateTo   Format: YYYYMMDDHHmmss
     * @param int    $type     Type d'événements (1 = tous)
     */
    public function getEvents(string $imei, string $dateFrom, string $dateTo, int $type = 1): array
    {
        $cacheKey = "gps.events.{$imei}.{$dateFrom}.{$dateTo}.{$type}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($imei, $dateFrom, $dateTo, $type) {
            $response = $this->call([
                'api' => 'user',
                'cmd' => "OBJECT_GET_EVENTS,{$imei},{$dateFrom},{$dateTo},{$type}",
            ]);

            if (!is_array($response)) {
                return [];
            }

            // Normaliser : trier par date croissante
            usort($response, fn($a, $b) => strcmp($a['dt'] ?? '', $b['dt'] ?? ''));

            return $response;
        });
    }

    /**
     * Récupère les événements pour un véhicule sur un mois donné.
     *
     * @param string $imei
     * @param int    $year
     * @param int    $month
     */
    public function getEventsForMonth(string $imei, int $year, int $month): array
    {
        $from = sprintf('%04d%02d01000000', $year, $month);
        $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
        $to   = sprintf('%04d%02d%02d235959', $year, $month, $lastDay);

        return $this->getEvents($imei, $from, $to);
    }

    /**
     * Récupère les événements sur une plage étendue (pour gérer les rotations
     * qui chevauchent deux mois).
     */
    public function getEventsForPeriod(string $imei, string $startDate, string $endDate): array
    {
        $from = date('YmdHis', strtotime($startDate)) ;
        $to   = date('YmdHis', strtotime($endDate));
        return $this->getEvents($imei, $from, $to);
    }

    // -------------------------------------------------------------------------
    // HTTP
    // -------------------------------------------------------------------------

    private function call(array $params): mixed
    {
        try {
            $params['key'] = $this->apiKey;
            $response = Http::timeout(30)
                ->get($this->baseUrl, $params);

            if (!$response->successful()) {
                Log::error('GPS API error', [
                    'status' => $response->status(),
                    'params' => $params,
                ]);
                return null;
            }

            $data = $response->json();

            // L'API peut retourner {"error":"..."} en cas d'échec
            if (isset($data['error'])) {
                Log::warning('GPS API returned error', ['error' => $data['error'], 'params' => $params]);
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('GPS API exception', ['message' => $e->getMessage(), 'params' => $params]);
            return null;
        }
    }

    /**
     * Vide le cache GPS pour forcer le rechargement.
     */
    public function clearCache(?string $imei = null): void
    {
        if ($imei) {
            // Vider seulement le cache de ce véhicule (approximatif, on vide tout)
            Cache::forget("gps.events.{$imei}");
        }
        Cache::forget('gps.objects');
        Cache::forget('gps.zones');
        Cache::forget('gps.markers');
    }
}