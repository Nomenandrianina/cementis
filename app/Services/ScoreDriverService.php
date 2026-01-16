<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Importcalendar;
use App\Models\ImportExcel;
use App\Models\Vehicule;
use Carbon\Carbon;
use App\Models\Chauffeur;
use App\Models\Scoring;
use App\Models\ScoreDriver;

class ScoreDriverService
{
    /**
     * Générer le score par dirver pour un planning
     */
    public function generate_score_driver($id_planning){
        try {
            $results = "";
            $calendar = Importcalendar::where('id', $id_planning)->first();
            $month = Carbon::parse($calendar->date_debut)->format('m');
            if($id_planning !== "" && $id_planning !== null){
                $results = DB::select("
                    SELECT 
                        badge_calendar AS badge,
                        SUM(total_point) AS score
                    FROM (
                        SELECT 
                            final.badge_calendar,
                            final.imei,
                            final.camion,
                            final.rfid_calendar,
                            final.rfid_conducteur,
                            SUM(final.point) AS total_point
                        FROM (
                            SELECT 
                                c.badge_chauffeur AS badge_calendar,
                                c.imei,
                                c.camion,
                                c.rfid_chauffeur AS rfid_calendar,
                                i.rfid AS rfid_conducteur,
                                COALESCE(SUM(i.point), 0) AS point
                            FROM (
                                SELECT DISTINCT
                                    badge_chauffeur,
                                    imei,
                                    camion,
                                    rfid_chauffeur,
                                    date_debut,
                                    date_fin
                                FROM import_excel
                                WHERE import_calendar_id = $id_planning
                            ) c
                            LEFT JOIN infraction i 
                                ON i.imei = c.imei
                                AND i.event != 'Temps de repos hebdomadaire'
                                AND CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut
                                AND CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin
                            GROUP BY 
                                c.badge_chauffeur,
                                c.imei
                            
                            UNION ALL
                            
                            SELECT 
                                c.badge_chauffeur AS badge_calendar,
                                c.imei,
                                c.camion,
                                c.rfid_chauffeur AS rfid_calendar,
                                i.rfid AS rfid_conducteur,
                                COALESCE(SUM(i.point), 0) AS point
                            FROM (
                                SELECT DISTINCT
                                    badge_chauffeur,
                                    imei,
                                    camion,
                                    rfid_chauffeur
                                FROM import_excel
                                WHERE import_calendar_id = $id_planning
                            ) c
                            LEFT JOIN (
                                SELECT DISTINCT id, imei, rfid, point
                                FROM infraction
                                WHERE event = 'Temps de repos hebdomadaire' 
                                AND MONTH(date_debut) = $month
                                AND MONTH(date_fin) = $month
                            ) i ON i.imei = c.imei
                            GROUP BY 
                                c.badge_chauffeur,
                                c.imei
                        ) AS final
                        GROUP BY 
                            final.badge_calendar,
                            final.imei
                    ) AS sous_requete
                    GROUP BY badge
                    ORDER BY score DESC;
                ");
            }
            return $results;

        } catch (Exception $e) {
            Log::error("Erreur lors de la génération du score driver $id_planning: " . $e->getMessage());
            return 0;
        }
    }

    public function get_driver_transporteur($badge)
    {
        try {
            if (!empty($badge)) {
                $driver = Chauffeur::where('numero_badge', $badge)->first();

                if ($driver) {
                    return $driver->transporteur_id ?? '';
                }
            }

            // Si aucun chauffeur trouvé ou badge vide
            return '';
        } catch (\Exception $e) {
            // Tu peux enregistrer l'erreur dans les logs
            \Log::error('Erreur dans get_transporteur : ' . $e->getMessage(), [
                'badge' => $badge,
                'trace' => $e->getTraceAsString(),
            ]);

            // Retourne une valeur par défaut ou null selon ton besoin
            return '';
        }
    }

    // public function get_vehicule_transporteur($id_planning ,$badge)
    // {
    //     try {
    //         if (!empty($badge)) {
    //             $planning = ImportExcel::where('import_calendar_id', $id_planning)->where('badge_chauffeur', $badge)->first();

    //             $vehicle = Vehicule::where('nom', 'like' ,  "%{$planning?->camion}%")->where('id_planning', $id_planning)->first();
                
    //             if ($vehicle) {
    //                 return $vehicle->id_transporteur ?? '';
    //             }
    //         }

    //         // Si aucun chauffeur trouvé ou badge vide
    //         return '';
    //     } catch (\Exception $e) {
    //         // Tu peux enregistrer l'erreur dans les logs
    //         \Log::error('Erreur dans get_transporteur : ' . $e->getMessage(), [
    //             'badge' => $badge,
    //             'planning' => $id_planning,
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         // Retourne une valeur par défaut ou null selon ton besoin
    //         return '';
    //     }
    // }
    public function get_vehicule_transporteur(int $idPlanning, ?string $badge): ?int
    {
        if (empty($badge)) {
            \Log::error('Erreur dans get_transporteur : ' . $e->getMessage(), [
                'badge' => $badge,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }

        $planning = ImportExcel::where('import_calendar_id', $idPlanning)
            ->where('badge_chauffeur', $badge)
            ->first();

        if (! $planning || empty($planning->camion)) {
            \Log::error('Erreur dans get_transporteur : ' . $e->getMessage(), [
                'planning' => $id_planning,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }

        $vehicle = Vehicule::where('id_planning', $idPlanning)
            ->where('nom', 'like', '%' . $planning->camion . '%')
            ->first();

        return $vehicle?->id_transporteur;
    }


    public function detail_score_drive_per_truck($id_planning, $badge){
        try {
            
            $results = "";
            $calendar = Importcalendar::where('id', $id_planning)->first();
            $month = Carbon::parse($calendar->date_debut)->format('m');
            if($id_planning !== "" && $id_planning !== null){
                $results = DB::select("
                    SELECT 
                        final.badge_calendar,
                        final.imei,
                        final.camion,
                        final.rfid_calendar,
                        final.rfid_conducteur,
                        SUM(final.point) AS total_point
                    FROM (
                        
                        SELECT 
                            c.badge_chauffeur AS badge_calendar,
                            c.imei,
                            c.camion,
                            c.rfid_chauffeur AS rfid_calendar,
                            i.rfid AS rfid_conducteur,
                            COALESCE(SUM(i.point), 0) AS point
                        FROM (
                            SELECT DISTINCT
                                badge_chauffeur,
                                imei,
                                camion,
                                rfid_chauffeur,
                                date_debut,
                                date_fin
                            FROM import_excel
                            WHERE import_calendar_id = $id_planning
                        ) c
                        LEFT JOIN infraction i 
                            ON i.imei = c.imei
                            AND i.event != 'Temps de repos hebdomadaire'
                            AND CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut
                            AND CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin
                        GROUP BY 
                            c.badge_chauffeur,
                            c.imei
                        
                        UNION ALL

                        
                        SELECT 
                            c.badge_chauffeur AS badge_calendar,
                            c.imei,
                            c.camion,
                            c.rfid_chauffeur AS rfid_calendar,
                            i.rfid AS rfid_conducteur,
                            COALESCE(SUM(i.point), 0) AS point
                        FROM (
                            SELECT DISTINCT
                                badge_chauffeur,
                                imei,
                                camion,
                                rfid_chauffeur
                            FROM import_excel
                            WHERE import_calendar_id = $id_planning
                        ) c
                        LEFT JOIN (
                            SELECT DISTINCT id, imei, rfid, point
                            FROM infraction
                            WHERE event = 'Temps de repos hebdomadaire' 
                            AND MONTH(date_debut) = $month AND MONTH(date_fin)= $month

                        ) i ON i.imei = c.imei
                        GROUP BY 
                            c.badge_chauffeur,
                            c.imei
                    ) AS final
                    WHERE
                        finaL.badge_calendar = $badge
                    GROUP BY 
                        final.badge_calendar,
                        final.imei
                    ORDER BY 
                        total_point DESC;
                ");
            }
            return $results;
        } catch (Exception $e) {
            Log::error("Erreur lors de la génération du détaile score driver par planning $id_planning: " . $e->getMessage());
            return 0;
        }
    }

    public function updateAllMostInfractions($id_planning)
    {
        $score_drives = ScoreDriver::where('id_planning', $id_planning)->get();
        // Récupère tous les scores liés au planning
        
        foreach ($score_drives as $item) {
            
            $detail = Scoring::where('id_planning', $id_planning)->where('badge_calendar', $item->badge)->first();
            // Récupérer badge + IMEI
            $badge = $detail->badge_calendar;
            $imei  = $detail->imei;
            $most_infraction = null;
            // Choix entre conducteur ou camion
            if (!empty($detail->driver)) {
                $most_infraction = getDriverInfractionWithmaximumPoint(
                    $detail->driver->id,
                    $imei,
                    $id_planning
                );
            } else {
                $most_infraction = getTruckInfractionWithmaximumPoint(
                    $imei,
                    $id_planning
                );
            }

            if ($item->score != 0) {
                // Mise à jour du champ
                $item->most_infraction = $most_infraction;
            } else {
                $item->most_infraction = null;
            }

            $item->save();
        }

        return true;
    }
}