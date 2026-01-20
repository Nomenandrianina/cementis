<?php

use App\Models\ImportExcel;
use App\Models\Rotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Chauffeur;
use App\Models\ChauffeurUpdateStory;
use App\Models\Vehicule;
use App\Models\Penalite;
use App\Models\PenaliteChauffeur;
use App\Models\GroupeEvent;
use App\Models\Transporteur;
use App\Models\Infraction;
use App\Models\Importcalendar;
use App\Models\User;
use App\Notifications\ValidationChauffeurNotification;
use Illuminate\Support\Facades\Notification;


if (!function_exists('fast_trans')) {

    function fast_trans($key, $replace, $default = null)
    {
        $value = __($key, $replace);
        if ($value == $key && $default != null) {
            $value = $default;
        }
        return $value;
    }

}


if (!function_exists('totalScoringCard')) {

    function totalScoringCard()
    {
        $result = DB::table('penalite_chauffeur as pc')
            ->join('chauffeur as ch', 'pc.id_chauffeur', '=', 'ch.id')
            ->join('penalite as p', 'pc.id_penalite', '=', 'p.id')
            ->join('transporteur as t', 'ch.transporteur_id', '=', 't.id')
            ->select(
                'ch.nom as driver','ch.id as id_driver','t.nom as transporteur',
                DB::raw('SUM(p.point_penalite) as total_penalty_point'),
                DB::raw('SUM(pc.distance) as total_distance'),
                DB::raw('(SUM(p.point_penalite) * 100) / SUM(pc.distance) as score_card')
            )
            ->groupBy('ch.nom', 'ch.id','t.nom')
            ->orderBy('ch.nom')
            ->orderBy('ch.id')
            ->get();

        return $result;
    }
}



if (!function_exists('convertMinuteHeure')) {
    function convertMinuteHeure($seconds) {
        if ($seconds < 60) {
            return number_format($seconds, 2) . " s";
        } elseif ($seconds < 3600) {
            $wholeMinutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return sprintf("%d min %02d s", $wholeMinutes, round($remainingSeconds));
        } else {
            $hours = floor($seconds / 3600);
            $remaining_seconds = $seconds % 3600;
            $minutes = $remaining_seconds / 60;
            return sprintf("%d h %02d min", $hours, round($minutes));
        }
    }
}

if (!function_exists('getDriverByRFID')){
    function getDriverByRFID($badge, $rfid, $id_planning){
        if(!empty($rfid)){
            $name = Chauffeur::where('rfid', $rfid)->where('id_planning', $id_planning)->first();
            if($badge){
                return $name->numero_badge ?? null;
            }else{
                return $name->nom ?? null;
            }
        }else{
            return null;
        }
    }
}

if (!function_exists('get_driver_by_rfid')){
    function get_driver_by_rfid($rfid){
        if(!empty($rfid)){
            $name = Chauffeur::where('rfid', $rfid)->first();
            if($name){
                return $name->numero_badge ?? '';
            }
        }else{
            return '';
        }
    }
}


if (!function_exists('get_transporteur_by_imei')){
    function get_transporteur_by_imei($id_planning, $imei, $camion){
        if(!empty($imei)){
            $truck = Vehicule::where('imei', $imei)->where('id_planning', $id_planning)->first();
            if($truck){
                return $truck->id_transporteur ?? null;
            }
        }else{
            $truck = Vehicule::where('nom', $camion)->where('id_planning', $id_planning)->first();
            if($truck){
                return $truck->id_transporteur ?? null;
            }
        }
    }
}



if (!function_exists('get_transporteur')){
    function get_transporteur($imei, $camion){
        if(!empty($imei)){
            $truck = Vehicule::where('imei', $imei)->first();
            if($truck){
                return $truck->related_transporteur->nom ?? '';
            }
        }else{
            $truck = Vehicule::where('nom', $camion)->first();
            if($truck){
                return $truck->related_transporteur->nom ?? '';
            }
        }
    }
}

if (!function_exists('getDriverByBadge')){
    function getDriverByBadge($badge, $rfid){
        $driver = Chauffeur::where('numero_badge', $badge)->first();
        if(empty($driver)){
            $result = [
                'driver_id' => null,
                'name' => null,
                'transporteur_id' => null,
                'commentaire' => "Chauffeur inexistant pour l'RFID : ".$rfid, 
            ];
        }
        else{
            $result = [
                'driver_id' => $driver->id,
                'name' => $driver->nom,
                'transporteur_id' => $driver->transporteur_id, 
            ];
        }

        return $result;
    }
}

use App\Models\ChauffeurUpdate;
if (!function_exists('getDriverByNumberBadge')){
    function getDriverByNumberBadge($badge, $id_planning){
        $driver = Chauffeur::where('numero_badge', trim($badge))->where('id_planning', $id_planning)->first();
        if(empty($driver)){
            // $driver = ChauffeurUpdate::where('numero_badge', $badge)->first();
            // if(empty($driver)){
                return null;
            // }
        }

        return $driver->nom;
    }
}


if(!function_exists('scoring')){
    // function scoring($id_planning){
    //     $results = "";
    //     $calendar = Importcalendar::where('id', $id_planning)->first();
    //     if($id_planning !== "" && $id_planning !== null){
    //         $results = DB::select("
    //             SELECT 
    //                 c.badge_chauffeur as badge_calendar,
    //                 c.imei,
    //                 c.camion,
    //                 c.rfid_chauffeur AS rfid_calendar,
    //                 i.rfid AS rfid_conducteur,
    //                 COALESCE(SUM(i.point), 0) AS total_point 
    //             FROM 
    //                 (
    //                     SELECT DISTINCT
    //                         badge_chauffeur,
    //                         imei,
    //                         camion,
    //                         rfid_chauffeur,
    //                         date_debut,
    //                         date_fin
    //                     FROM 
    //                         import_excel
    //                     WHERE 
    //                         import_calendar_id = $id_planning
    //                 ) c
    //             LEFT JOIN 
    //                 infraction i 
    //                 ON i.imei = c.imei 
    //                 AND (
    //                         ( 
    //                             i.event != 'Temps de repos hebdomadaire'
    //                             AND
    //                             CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut 
    //                             AND 
    //                             CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin
    //                         )
    //                         OR 
    //                         i.event = 'Temps de repos hebdomadaire'
    //                     )
    //             GROUP BY 
    //                 c.badge_chauffeur, c.imei
    //             ORDER BY 
    //                 total_point DESC;
    //         ");
    //     }
    //     return $results;
    // }
    function scoring($id_planning){
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
                GROUP BY 
                    final.badge_calendar,
                    final.imei
                ORDER BY 
                    total_point DESC;
            ");
        }
        return $results;
    }
}


// Detail driver scoring
if (!function_exists('driver_detail_scoring_card')) {
    function driver_detail_scoring_card($imei, $badge, $id_planning)
    {
        $calendar = Importcalendar::where('id', $id_planning)->first();
        $month = Carbon::parse($calendar->date_debut)->format('m');

        $normalInfractions = DB::table('import_excel as c')
            ->select([
                'c.badge_chauffeur AS badge_calendar',
                'i.imei',
                'c.camion',
                'c.rfid_chauffeur AS rfid_calendar',
                'i.rfid AS rfid_conducteur',
                'i.event AS infraction',
                'i.date_debut',
                'i.heure_debut',
                'i.date_fin',
                'i.heure_fin',
                'i.gps_debut',
                'i.gps_fin',
                'i.duree_infraction',
                'i.insuffisance',
                'i.point',
            ])
            ->join('infraction as i', function ($join) {
                $join->on('i.imei', '=', 'c.imei')
                    ->where('i.event', '!=', 'Temps de repos hebdomadaire')
                    ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut")
                    ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin");
            })
            ->where('c.import_calendar_id', $id_planning)
            ->where('c.badge_chauffeur', $badge)
            ->where('c.imei', $imei);
            // ->distinct();

        // --- Partie 2 : repos hebdomadaire
        $reposHebdo = DB::table('import_excel as c')
            ->select([
                'c.badge_chauffeur AS badge_calendar',
                'i.imei',
                'c.camion',
                'c.rfid_chauffeur AS rfid_calendar',
                'i.rfid AS rfid_conducteur',
                'i.event AS infraction',
                'i.date_debut',
                'i.heure_debut',
                'i.date_fin',
                'i.heure_fin',
                'i.gps_debut',
                'i.gps_fin',
                'i.duree_infraction',
                'i.insuffisance',
                'i.point',
            ])
            ->joinSub(
                DB::table('infraction')
                    ->select(
                        DB::raw('DISTINCT id, imei, rfid, event, date_debut, heure_debut, date_fin, heure_fin, gps_debut, gps_fin, duree_infraction, insuffisance, point')
                    )
                    ->where('event', 'Temps de repos hebdomadaire')
                    ->whereMonth('date_debut', '=', $month)
                    ->whereMonth('date_fin', '=', $month)
                    ->where('imei', $imei),
                'i',
                'i.imei',
                '=',
                'c.imei'
            )
            ->where('c.import_calendar_id', $id_planning)
            ->where('c.badge_chauffeur', $badge)
            ->where('c.imei', $imei)
            ->distinct();
        $resultats = $normalInfractions
            ->unionAll($reposHebdo)
            ->get();
                
        return $resultats;
    }

}

// Detail driver scoring
if (!function_exists('truck_detail_scoring_card')) {
    // function truck_detail_scoring_card($immatricule, $id_planning)
    // {
    //     $imei = ImportExcel::where('camion', $immatricule)->pluck('imei')->first();
    //     $resultats = DB::table('import_excel as c')
    //         ->leftJoin('infraction as i', function ($join) {
    //             $join->on('i.imei', '=', 'c.imei')
    //                 ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut")
    //                 ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin");
    //         })
    //         ->select(
    //             'c.badge_chauffeur AS badge_calendar',
    //             'i.imei',
    //             'c.camion',
    //             'c.rfid_chauffeur AS rfid_calendar',
    //             'i.event AS infraction',
    //             'i.date_debut AS date_debut',
    //             'i.heure_debut AS heure_debut',
    //             'i.date_fin AS date_fin',
    //             'i.heure_fin AS heure_fin',
    //             'i.rfid AS rfid_conducteur',
    //             'i.gps_debut',
    //             'i.gps_fin',
    //             'i.duree_infraction',
    //             'i.insuffisance',
    //             'i.point AS point'
    //         )
    //         ->where('c.import_calendar_id', $id_planning)
    //         ->where('i.imei', $imei)
    //         ->get();


    //     return $resultats;
    // }
    function truck_detail_scoring_card($immatricule, $id_planning)
    {
        $calendar = Importcalendar::where('id', $id_planning)->first();
        $month = Carbon::parse($calendar->date_debut)->format('m');
        $imei = ImportExcel::where('camion', $immatricule)->pluck('imei')->first();
        $normalInfractions = DB::table('import_excel as c')
            ->select([
                'c.badge_chauffeur AS badge_calendar',
                'i.imei',
                'c.camion',
                'c.rfid_chauffeur AS rfid_calendar',
                'i.rfid AS rfid_conducteur',
                'i.event AS infraction',
                'i.date_debut',
                'i.heure_debut',
                'i.date_fin',
                'i.heure_fin',
                'i.gps_debut',
                'i.gps_fin',
                'i.duree_infraction',
                'i.insuffisance',
                'i.point',
            ])
            ->join('infraction as i', function ($join) {
                $join->on('i.imei', '=', 'c.imei')
                    ->where('i.event', '!=', 'Temps de repos hebdomadaire')
                    ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut")
                    ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin");
            })
            ->where('c.import_calendar_id', $id_planning)
            ->where('c.imei', $imei)
            ->distinct();

        // --- Partie 2 : repos hebdomadaire
        // $reposHebdo = DB::table('import_excel as c')
        //     ->select([
        //         'c.badge_chauffeur AS badge_calendar',
        //         'i.imei',
        //         'c.camion',
        //         'c.rfid_chauffeur AS rfid_calendar',
        //         'i.rfid AS rfid_conducteur',
        //         'i.event AS infraction',
        //         'i.date_debut',
        //         'i.heure_debut',
        //         'i.date_fin',
        //         'i.heure_fin',
        //         'i.gps_debut',
        //         'i.gps_fin',
        //         'i.duree_infraction',
        //         'i.insuffisance',
        //         'i.point',
        //     ])
        //     ->joinSub(
        //         DB::table('infraction')
        //             ->select(
        //                 DB::raw('DISTINCT id, imei, rfid, event, date_debut, heure_debut, date_fin, heure_fin, gps_debut, gps_fin, duree_infraction, insuffisance, point')
        //             )
        //             ->where('event', 'Temps de repos hebdomadaire')
        //             ->whereMonth('date_debut', '=', $month)
        //             ->whereMonth('date_fin', '=', $month)
        //             ->where('imei', $imei),
        //         'i',
        //         'i.imei',
        //         '=',
        //         'c.imei'
        //     )
        //     ->where('c.import_calendar_id', $id_planning)
        //     ->where('c.imei', $imei)
        //     ->distinct();
        $reposHebdo = DB::table('infraction as i')
            ->select([
                DB::raw('NULL AS badge_calendar'),
                'i.imei',
                DB::raw('NULL AS camion'),
                DB::raw('NULL AS rfid_calendar'),
                'i.rfid AS rfid_conducteur',
                'i.event AS infraction',
                'i.date_debut',
                'i.heure_debut',
                'i.date_fin',
                'i.heure_fin',
                'i.gps_debut',
                'i.gps_fin',
                'i.duree_infraction',
                'i.insuffisance',
                'i.point',
            ])
            ->where('i.event', 'Temps de repos hebdomadaire')
            ->whereMonth('i.date_debut', $month)
            ->whereMonth('i.date_fin', $month)
            ->where('i.imei', $imei);

        $resultats = $normalInfractions
            ->unionAll($reposHebdo)
            ->get();


        return $resultats;
    }

}



if (!function_exists('tabScoringCard_new')) {
    function tabScoringCard_new($chauffeur, $id_planning)
    {
        $results = DB::table('infraction as i')
        ->join('chauffeur as ch', 'i.rfid', '=', 'ch.rfid')
        ->join('import_excel as ie', 'i.calendar_id', '=', 'ie.id')
        ->join('transporteur as t', 'ch.transporteur_id', '=', 't.id')
        ->select(
            'ch.nom as driver',
            't.nom as transporteur_nom',
            'i.gps_debut as latitude',
            'i.gps_fin as longitude',
            'i.duree_infraction as duree',
            DB::raw("CONCAT(i.date_debut, ' ', i.heure_debut) as date_event"),
            'i.event as event',
            'i.point as penalty_point',
            'i.distance',
            'i.distance_calendar',
            DB::raw("(i.point * 100) / i.distance_calendar as score_card")
            )
        ->where('ch.nom',$chauffeur)
        ->where('ie.import_calendar_id', $id_planning)
        ->groupBy('t.nom','ch.nom', 'i.duree_infraction','i.heure_debut','i.heure_fin', 'i.gps_debut', 'i.date_debut', 'i.gps_fin', 'i.event', 'i.point', 'i.distance','i.distance_calendar')
        ->orderBy('ch.nom')
        ->orderBy('t.nom')
        ->get();

            // DB::raw("DATE_FORMAT(pc.date, '%Y-%m')")
        
        return $results;
    }

}


if (!function_exists('driverTop')){
    function driverTop()
    {
        $driverTop = DB::table('penalite_chauffeur')
            ->select(DB::raw('MAX(chauffeur.id) AS drive_id'), 'chauffeur.nom AS nom_chauffeur', DB::raw('SUM(penalite.point_penalite) as total_penalite'))
            ->join('chauffeur', 'penalite_chauffeur.id_chauffeur', '=', 'chauffeur.id')
            ->join('penalite', 'penalite_chauffeur.id_penalite', '=', 'penalite.id')
            ->groupBy('penalite_chauffeur.id_chauffeur', 'chauffeur.nom')
            ->orderByRaw('SUM(penalite.point_penalite) ASC')
            ->limit(1)
            ->first();

        return $driverTop;
    }
}


if (!function_exists('driverWorst')){
    function driverWorst(){
        $driverWorst = DB::table('penalite_chauffeur')
        ->select(DB::raw('MAX(chauffeur.id) AS drive_id'), 'chauffeur.nom AS nom_chauffeur', DB::raw('SUM(penalite.point_penalite) as total_penalite'))
        ->join('chauffeur', 'penalite_chauffeur.id_chauffeur', '=', 'chauffeur.id')
        ->join('penalite', 'penalite_chauffeur.id_penalite', '=', 'penalite.id')
        ->groupBy('penalite_chauffeur.id_chauffeur', 'chauffeur.nom')
        ->orderByRaw('SUM(penalite.point_penalite) DESC')
        ->limit(1)
        ->first();

        return $driverWorst;
    }
}

if (!function_exists('scoringCard')) {

    function scoringCard()
    {
        // $data = null;
        $data = Chauffeur::select('chauffeur.id AS id_chauffeur', 'chauffeur.nom',
            DB::raw('COALESCE((SUM(penalite.point_penalite) * 100) / NULLIF(SUM(penalite_chauffeur.distance), 0), 0) AS scoring_card'))
            ->leftJoin('penalite_chauffeur', 'chauffeur.id', '=', 'penalite_chauffeur.id_chauffeur')
            ->leftJoin('penalite', 'penalite.id', '=', 'penalite_chauffeur.id_penalite')
            ->leftJoin('import_excel', 'penalite_chauffeur.id_calendar', '=', 'import_excel.id')
            ->groupBy('chauffeur.id', 'chauffeur.nom')
            ->orderBy('scoring_card', 'asc')
            ->get();
        
        return $data;
    }

}

if (!function_exists('topDriver')) {
    function topDriver()
    {
        $topChauffeur = PenaliteChauffeur::select('chauffeur.nom', 'penalite_chauffeur.id_chauffeur', DB::raw('SUM(penalite.point_penalite) as total_penalite'))
            ->join('penalite', 'penalite.id', '=', 'penalite_chauffeur.id_penalite')
            ->join('chauffeur', 'chauffeur.id', '=', 'penalite_chauffeur.id_chauffeur')
            ->groupBy('penalite_chauffeur.id_chauffeur', 'chauffeur.nom')
            ->orderBy ('total_penalite')
            ->get();
        
        return $topChauffeur;
    }

}

if(!function_exists('TotalScoringbyDriver')){
    
    function TotalScoringbyDriver()
    {
        $results = DB::table('penalite_chauffeur as pc')
        ->join('chauffeur as ch', 'pc.id_chauffeur', '=', 'ch.id')
        ->join('penalite as p', 'pc.id_penalite', '=', 'p.id')
        ->join('transporteur as t', 'ch.transporteur_id', '=', 't.id')
        ->select(
            'ch.nom as driver',
            't.nom as transporteur_nom',
            DB::raw('SUM(p.point_penalite) as total_penalty_point'),
            DB::raw('SUM(DISTINCT pc.distance) as total_distance'), 
            DB::raw('ROUND((SUM(p.point_penalite) * 100) / SUM(DISTINCT pc.distance), 2) as score_card')
        )
        ->groupBy('ch.nom', 't.nom')
        ->orderBy('t.nom')
        ->orderBy('ch.nom')
        ->get();

        return $results;

    }

}

if(!function_exists('getAllGoodScoring')){
    function getAllGoodScoring($lastmonth, $transporteur_id = null){
        $query = DB::table('import_excel as c')
            ->leftJoin('infraction as i', function ($join) {
                $join->on('i.imei', '=', 'c.imei')
                    ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut")
                    ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin");
            })
            ->select(
                'c.badge_chauffeur as badge_calendar',
                'c.imei',
                'c.camion',
                'c.rfid_chauffeur as rfid_calendar',
                'i.rfid as rfid_conducteur',
                DB::raw('COALESCE(SUM(i.point), 0) as total_point')
            )
            ->where('c.import_calendar_id', $lastmonth);

        // Si transporteur_id est fourni, on joint vehicule + transporteur et on filtre
        if (!is_null($transporteur_id)) {
            $query->leftJoin('chauffeur as ch', function ($join) {
                $join->on('ch.rfid', '=', 'c.rfid_chauffeur')
                    ->orOn('ch.numero_badge', '=', 'c.badge_chauffeur');
            })
            ->addSelect('ch.transporteur_id')
            ->where('ch.transporteur_id', $transporteur_id);
        }

        return $query
            ->groupBy(
                'c.badge_chauffeur',
                'c.imei',
                'c.camion',
                'c.rfid_chauffeur',
                'i.rfid'
            )
            ->orderBy('total_point', 'asc')
            ->limit(3)
            ->get();
    }
}

if(!function_exists('getAllBadScoring')){
    function getAllBadScoring($lastmonth, $transporteur_id = null) {
        $query = DB::table('import_excel as c')
            ->leftJoin('infraction as i', function ($join) {
                $join->on('i.imei', '=', 'c.imei')
                    ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= c.date_debut")
                    ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= c.date_fin");
            })
            ->select(
                'c.badge_chauffeur as badge_calendar',
                'c.imei',
                'c.camion',
                'c.rfid_chauffeur as rfid_calendar',
                'i.rfid as rfid_conducteur',
                DB::raw('COALESCE(SUM(i.point), 0) as total_point')
            )
            ->where('c.import_calendar_id', $lastmonth);

        // Si transporteur_id est fourni, on joint vehicule + transporteur et on filtre
        if (!is_null($transporteur_id)) {
            $query->leftJoin('chauffeur as ch', function ($join) {
                $join->on('ch.rfid', '=', 'c.rfid_chauffeur')
                    ->orOn('ch.numero_badge', '=', 'c.badge_chauffeur');
            })
            ->addSelect('ch.transporteur_id')
            ->where('ch.transporteur_id', $transporteur_id);
        }

        return $query
            ->groupBy(
                'c.badge_chauffeur',
                'c.imei',
                'c.camion',
                'c.rfid_chauffeur',
                'i.rfid'
            )
            ->orderBy('total_point', 'desc')
            ->limit(3)
            ->get();
    }

}

if(!function_exists('topAndWorstChauffeur')){
    
    function topAndWorstChauffeur()
    {

        $results = TotalScoringbyDriver();

        // $results = tabScoringCard(); // Appel de votre fonction pour obtenir les résultats de la requête

        $topAndWorstDrivers = [];


        // Groupement des résultats par transporteur
        $resultsByTransporteur = $results->groupBy('transporteur_nom');

        // Pour chaque transporteur
        foreach ($resultsByTransporteur as $transporteur => $resultats) {
            // Trier les résultats par score_card (du plus petit au plus grand)
            $sortedResults = $resultats->sortBy('score_card');

            // Obtenir les 3 meilleurs chauffeurs
            $topChauffeurs = $sortedResults->take(3);

            // Obtenir les 3 pires chauffeurs
            $worstChauffeurs = $sortedResults->reverse()->take(3);

            // Collecter les résultats dans un tableau
            $topAndWorstDrivers[] = [
                'transporteur' => $transporteur,
                'top_chauffeurs' => $topChauffeurs,
                'worst_chauffeurs' => $worstChauffeurs,
            ];
        }

        return $topAndWorstDrivers;
    }

   
  
}

if (!function_exists('driverChart')) {
    function driverChart()
    {
        $labels = [];
        $data = [];

        $chartScoring = Chauffeur::select('chauffeur.id AS id_chauffeur', 'chauffeur.nom',
            DB::raw('COALESCE((SUM(penalite.point_penalite) * 100) / NULLIF(SUM(penalite_chauffeur.distance), 0), 0) AS scoring_card'))
            ->leftJoin('penalite_chauffeur', 'chauffeur.id', '=', 'penalite_chauffeur.id_chauffeur')
            ->leftJoin('penalite', 'penalite.id', '=', 'penalite_chauffeur.id_penalite')
            ->leftJoin('import_excel', 'penalite_chauffeur.id_calendar', '=', 'import_excel.id')
            ->groupBy('chauffeur.id', 'chauffeur.nom')
            ->orderBy('scoring_card', 'asc')
            ->get();

        // dd($chartScoring);
        foreach ($chartScoring as $chart) {
            $labels[] = $chart->nom;
            $data[] = $chart->scoring_card;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}

if (!function_exists('createExistingDriverInEvent')) {
    function createExistingDriverInEvent(){
        $existingDrivers = Event::distinct()
                            ->where(function ($query) {
                                $query->whereNotNull('chauffeur')
                                    ->where('chauffeur','<>', '');
                            })
                            ->pluck('chauffeur');
        
        $existingDrivers->each(function ($name) {
            // Utilisez firstOrCreate pour éviter les doublons
            Chauffeur::firstOrCreate(['nom' => $name]);
        });
    }
}

// Récupérer les évènements d'un chauffeur par mois
if (!function_exists('getEventMonthly')) {
    function getEventMonthly($rfid_chauffeur){
        $moisActuel = Carbon::now()->month;
        $events = Event::where('chauffeur', $rfid_chauffeur)
            ->whereMonth('date', $moisActuel)
            ->get();
        
        return $events;
    }
}

// Récupération d'un chauffeur par son nom
if (!function_exists('getDriverByName')) {
    function getDriverByName($name){
        $existingDrivers = Chauffeur::where('nom','=', $name)
                            ->first();

        return $existingDrivers;
    }
}

if (!function_exists('getNameByRFID')) {
    function getNameByRFID($rfid)
    {
            $chauffeur = Chauffeur::where('rfid', $rfid)->first();

            if ($chauffeur) {
                return $chauffeur->nom;
            } else {
                return null;
            }
    }
}

if (!function_exists('getIdByRFID')) {
    function getIdByRFID($rfid)
    {
            $chauffeur = Chauffeur::where('rfid', $rfid)->first();

            if ($chauffeur) {
                return $chauffeur->id;
            } else {
                return null;
            }
    }
}

//Récupération du somme totale d'un point de pénalité d'un chauffeur par mois
if (!function_exists('getPointPenaliteTotalMonthly')) {

    function getPointPenaliteTotalMonthly($id_chauffeur){
        $moisActuel = Carbon::now()->month;
        $result = DB::table('penalite_chauffeur as pc')
            ->join('penalite as p', 'pc.id_penalite', '=', 'p.id')
            ->join('event as e', 'pc.id_event', '=', 'e.id')
            ->join('import_excel as c', 'pc.id_calendar', '=', 'c.id')
            ->join('chauffeur as ch', 'pc.id_chauffeur', '=', 'ch.id')
            ->select('pc.id_chauffeur', 'ch.nom', DB::raw('SUM(p.point_penalite) AS total_point_penalite'))
            ->where('pc.id_chauffeur', $id_chauffeur)
            ->whereMonth('e.date', '=', $moisActuel)
            ->whereYear('e.date', '=', 2024)
            ->groupBy('pc.id_chauffeur', 'ch.nom')
            ->first();
        if($result){
            return $result;
        }else{
            return 0;
        }
    } 

}

//Récuperation des calendriers d'un chauffeur par mois
if (!function_exists('getCalendarOfDriverMonthly')) {

    function getCalendarOfDriverMonthly(){
        $moisActuel = Carbon::now()->month;
        $livraisons = ImportExcel::whereMonth('date_debut', $moisActuel)
            ->get();

        return $livraisons;
    }
}

if (!function_exists('getImeiOfCalendarTruck')) {

    function getImeiOfCalendarTruck($data, $truck){
        foreach($data as $arrayItem) {
            if($arrayItem["plate_number"] === $truck) {
                return  $arrayItem["imei"];
            }
        }
        return null;
    }

}

if (!function_exists('getUserVehicule')) {
    function getUserVehicule(){
        // Formatage des dates au format YYYYMMDD

        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=USER_GET_OBJECTS";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);

        return $data;

    }

}

if (!function_exists('insertPenaliteDrive')) {
    function insertPenaliteDrive($event, $calendar, $penalite){
            PenaliteChauffeur::updateOrCreate([
                'id_chauffeur' => getIdByRFID($event->chauffeur),
                'id_calendar' => $calendar->id,
                'id_event' => $event->id,
                'id_penalite' => $penalite->id,
                'duree' => $event->duree,
                'date' => $event->date,
            ]);
    }
}



if (!function_exists('getDistanceWithImeiAndPeriod')) {

    function getDistanceWithImeiAndPeriod($rfid_chauffeur, $imei_vehicule, $start_date, $end_date){
        // Formatage des dates au format YYYYMMDD
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_ROUTE,".$imei_vehicule.",".$start_date->format('YmdHis').",".$end_date->format('YmdHis').",20";
        $response = Http::timeout(300)->get($url);
        $data = $response->json();

        $firstItem = reset($data['route']);
        $lastItem = end($data['route']);
        
        $firstOdo = null;
        $lastOdo = null;
        if ($firstItem[6]['rfid'] === $rfid_chauffeur && $lastItem[6]['rfid'] === $rfid_chauffeur) {
            $firstOdo = (float) $firstItem[6]['odo'];
            $lastOdo = (float) $lastItem[6]['odo'];
        }
        $drive_duration = $data['drives_duration_time'];
        $hour = $drive_duration / 3600;

        $distance = $lastOdo - $firstOdo;
        // $result =  [
        //     'distance' => $distance,
        //     'drive_duration' => (int) $hour
        // ];
        
        return $distance;
    }
}

if (!function_exists('updateLatAndLongExistingEvent')) {
    function updateLatAndLongExistingEvent($event){
        $formattedDate = $event->date->format('YmdHis');
        
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_EVENTS,{$event->imei},{$formattedDate},{$formattedDate}";
        $response = Http::timeout(600)->get($url);
        $data = $response->json();


        $latitude = $data[0][5];
        $longitude = $data[0][6];
        
        // Mettre à jour les enregistrements correspondants dans la base de données
        DB::table('event')
            ->where('imei', $event->imei)
            ->where('date', $event->date)
            ->whereNull('latitude')
            ->whereNull('longitude')
            ->update([
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
    }
}


if (!function_exists('updateOdometer')) {
    function updateOdometer($event){
        $formattedDate = $event->date->format('YmdHis');
        
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_EVENTS,{$event->imei},{$formattedDate},{$formattedDate}";
        $response = Http::timeout(600)->get($url);
        $data = $response->json();
        $odo = (float) $data[0][10]['odo'];
        

        
        // Mettre à jour les enregistrements correspondants dans la base de données
        DB::table('event')
            ->where('imei', $event->imei)
            ->where('date', $event->date)
            ->whereNull('odometer')
            ->update([
                'odometer' => $odo
            ]);
    }
}

if (!function_exists('updateVitesse')) {
    function updateVitesse($event){
        $formattedDate = $event->date->format('YmdHis');
        
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_EVENTS,{$event->imei},{$formattedDate},{$formattedDate}";
        $response = Http::timeout(600)->get($url);
        $data = $response->json();
        $vitesse =  $data[0][9];
        

        
        // Mettre à jour les enregistrements correspondants dans la base de données
        DB::table('event')
            ->where('imei', $event->imei)
            ->where('date', $event->date)
            ->where('vitesse','=', 0)
            ->update([
                'vitesse' => $vitesse
            ]);
    }
}

if(!function_exists('insertGroupedEventsDetails')){
    function insertGroupedEventsDetails($key, $groupedEvents, $duration)
    {
        // Vérifier si une entrée avec la même clé existe déjà
        $existingEntry = GroupeEvent::where('key', $key)->first();

        // Si aucune entrée avec la même clé n'existe, insérer les données
        if (!$existingEntry) {
            foreach ($groupedEvents as $eventData) {
                GroupeEvent::create([
                    'key' => $key,
                    'imei' => $eventData[2],
                    'type' => $eventData[1],
                    'chauffeur' => $eventData[10]['rfid'],
                    'vehicule' => $eventData[3],
                    'latitude' => $eventData[5],
                    'longitude' => $eventData[6],
                    'duree' => $duration,
                    'description' => $eventData[1],
                ]);
            }
        }
    }
}

if(!function_exists('processEvents')) {
    
    function processEvents($data, $allowedTypes)
    {
        $groupedEvents = [];
        $infractions = [];
        // Parcours du tableau de données
        foreach ($data as $event) {
            // Génération de la clé de groupe
            $groupKey = $event[1] . '_' . $event[2] . '_' . $event[10]['rfid'] . '_' . $event[3] . '_' . substr($event[4], 0, 13);
            // Vérification si un groupe existe déjà pour cet événement
            if (!isset($groupedEvents[$groupKey])) {
                // Création d'un nouveau groupe pour cet événement
                $groupedEvents[$groupKey] = [
                    'events' => [],
                    'lastTimestamp' => strtotime($event[4]),
                ];
            } else {
                // Récupération du dernier timestamp du groupe
                $lastTimestamp = $groupedEvents[$groupKey]['lastTimestamp'];
                // Récupération du timestamp de l'événement actuel
                $currentTimestamp = strtotime($event[4]);

                // Vérification si les événements sont dans la même minute (ou différence de 60 secondes)
                if (abs($currentTimestamp - $lastTimestamp) <= 60) {
                    // Ajout de l'événement au groupe existant
                    $groupedEvents[$groupKey]['events'][] = $event;
                    $groupedEvents[$groupKey]['lastTimestamp'] = $currentTimestamp;
                    continue;
                }
            }
            // Création d'un nouveau groupe pour cet événement
            $groupedEvents[$groupKey]['events'][] = $event;
            $groupedEvents[$groupKey]['lastTimestamp'] = strtotime($event[4]);
            // Si l'événement n'a pas été regroupé, ajouter à la liste des infractions
            $infractions[] = $event;
        }
        // Traitement des groupes pour obtenir les résultats finaux
        $results = [];
        foreach ($groupedEvents as $groupeKey => $group) {
            if (count($group['events']) > 1) { 
                insertGroupedEventsDetails($groupeKey,$group['events'], $allowedTypes[$group['events'][0][1]]);
                // Si le groupe contient plus d'un événement, fusionner les événements en un seul avec la durée appropriée
                $type = $group['events'][0][1];
                $imei = $group['events'][0][2];
                $chauffeur = $group['events'][0][10]['rfid'];
                $vehicule = $group['events'][0][3];
                $odo = $group['events'][0][10]['odo'];
                $latitude = $group['events'][0][5];
                $longitude = $group['events'][0][6];
                $description = $group['events'][0][1];
                // Calcul de la durée totale du groupe
                $duration = (count($group['events']) * 60) + $allowedTypes[$group['events'][0][1]]; 

                // Ajout de l'événement fusionné aux résultats
                $results[] = [
                    'imei' => $imei,
                    'chauffeur' => $chauffeur,
                    'vehicule' => $vehicule,
                    'type' => $type,
                    'date' => $group['events'][0][4], // Utiliser la date du premier événement
                    'odometer' => $odo,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'duree' => $duration,
                    'description' => $description,
                ];
            } else {
                // Si le groupe contient un seul événement, ajouter cet événement aux résultats sans fusion
                $results[] = [
                    'imei' => $group['events'][0][2],
                    'chauffeur' => $group['events'][0][10]['rfid'],
                    'vehicule' => $group['events'][0][3],
                    'type' => $group['events'][0][1],
                    'odometer' => $group['events'][0][10]['odo'],
                    'date' => $group['events'][0][4],
                    'latitude' => $group['events'][0][5],
                    'longitude' => $group['events'][0][6],
                    'duree' => $allowedTypes[$group['events'][0][1]], // Durée par défaut si un seul événement
                    'description' => $group['events'][0][1],
                ];
            }
        }
        return $results;
    }
}

if(!function_exists('checkMissingEvent')) {
    function checkMissingEvent(){
        $trucks = Vehicule::all();
        $startDate = Carbon::parse("2024-08-01 00:00:00");
        $endDate = Carbon::parse("2024-09-01 00:00:00");
        
        foreach($trucks as $truck){
            getMissingEventFromApi($truck->imei, $startDate, $endDate);
        }
    }
}

// Récupération des évènements absentes dans l'API M-TEC Tracking et enregistrer dans la table Event
if (!function_exists('getMissingEventFromApi')) {

    function getMissingEventFromApi($imei_truck, $start_date, $end_date){

        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_EVENTS,".$imei_truck.",".$start_date->format('YmdHis').",".$end_date->format('YmdHis')."";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $allowedEventTypes = [
            'Accélération brusque', 
            'Freinage brusque', 
            'Excès de vitesse en agglomération', 
            'Excès de vitesse hors agglomération', 
            'Survitesse excessive',
            'Survitesse sur la piste de Tritriva',
            'Survitesse sur la piste d\'Ibity',
            'TEMPS DE CONDUITE CONTINUE JOUR',
            'TEMPS DE CONDUITE CONTINUE NUIT',
        ];

        $filteredData = [];
        // Parcourir les données de l'API
        if(!empty($data)){
            foreach ($data as $event) {
                if (in_array($event[1], $allowedEventTypes) && isset($event[10]['rfid'])) {
                    $filteredData[] = $event;
                }
            }
        }
        

        if (!empty($filteredData)) {
            foreach ($filteredData as $item) {
                // Vérifiez si une entrée identique existe déjà dans la table Event
                $existingEvent = Event::where('imei', trim($item[2]))
                ->where('date', $item['4'])
                ->where('type', trim($item['1']))
                ->first();

                // Si aucune entrée identique n'existe, insérez les données dans la table Event
                if (!$existingEvent) {
                
                    if(isset($item[10]['rfid']) && $item[10]['rfid'] != "0000000000" && trim($item[10]['rfid']) != trim("u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0	")){
                        Event::create([
                            'imei' => $item[2],
                            'chauffeur' => $item[10]['rfid'],
                            'vehicule' => $item[3],
                            'type' => trim($item[1]),
                            'date' => $item[4],
                            'odometer' => $item[10]['odo'] ?? 0,
                            'vitesse' => $item[9] ?? 0,
                            'latitude' => $item[5] ?? 0,
                            'longitude' => $item[6] ?? 0,
                            'duree' => 1,
                            'description' => trim($item[1]) ?? 0,
                        ]);
                    }
                }
            }
        }
    }    
}

// Récupération des derniers évènements dans l'API M-TEC Tracking et enregistrer dans la table Event
if (!function_exists('getEventFromApi')) {

    function getEventFromApi(){

        $url = 'www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_LAST_EVENTS_7D';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
       
        $penalitesAllowed = Penalite::all()->toArray();
        $allowedTypes = array_column($penalitesAllowed, 'duree','event');
        $filteredData = [];

        // Parcourir les données de l'API
        foreach ($data as $event) {
            if (in_array($event[1], array_keys($allowedTypes)) && isset($event[10]['rfid'])) {
                $filteredData[] = $event;
            }
        }
        // $eventsInsert = processEvents($filteredData, $allowedTypes);
        if (!empty($filteredData)) {
            foreach ($filteredData as $item) {
                // Vérifiez si une entrée identique existe déjà dans la table Rotation
                $existingEvent = Event::where('imei', trim($item[2]))
                ->where('date', $item['4'])
                ->first();

                // Si aucune entrée identique n'existe, insérez les données dans la table Rotation
                if (!$existingEvent) {
                
                    if(isset($item[10]['rfid']) && $item[10]['rfid'] != "0000000000"){
                        Event::create([
                            'imei' => $item[2],
                            'chauffeur' => $item[10]['rfid'],
                            'vehicule' => $item[3],
                            'type' => trim($item[1]),
                            'date' => $item[4],
                            'odometer' => $item[10]['odo'] ?? 0,
                            'vitesse' => $item[9],
                            'latitude' => $item[5],
                            'longitude' => $item[6],
                            'duree' => $allowedTypes[$item[1]],
                            'description' => trim($item[1]),
                        ]);
                    }
                }
                else {
                    Event::where('id', $existingEvent->id)
                    ->update([
                        'duree' => $allowedTypes[$existingEvent->type],
                    ]);
                }
            }
        }
    }    
}

if (!function_exists('RapportPenaliteChauffeurMonthly')){
    function RapportPenaliteChauffeurMonthly(){
        $importExcelRows = ImportExcel::where(function ($query) {
            $query->whereBetween('date_debut', [now()->startOfMonth(), now()->endOfMonth()])
                  ->whereNull('date_fin');
        })
        ->orWhere(function ($query) {
            $query->whereBetween('date_debut', [now()->startOfMonth(), now()->endOfMonth()])
                  ->whereNotNull('date_fin')
                  ->whereBetween('date_fin', [now()->startOfMonth(), now()->endOfMonth()]);
        })
        ->get();
        $events = Event::whereMonth('date', now()->month)->get();
        $distance = 0;

        foreach ($importExcelRows as $importRow) {
                $dateDebut = Carbon::parse($importRow->date_debut);
                $dateFin = $importRow->date_fin ? Carbon::parse($importRow->date_fin) : null;

                if ($dateFin === null) {
                    // Convertir la durée en heures
                    $dureeEnHeures = floatval($importRow->delais_route);
                    // Calculer la date de fin en fonction de la durée
                    if ($dureeEnHeures <= 1) {
                        // Durée inférieure à une journée
                        $dateFin = $dateDebut->copy()->endOfDay();
                    } else {
                        $dureeEnJours = ceil($dureeEnHeures / 24);
                        // Durée d'une journée ou plus
                        $dateFin = $dateDebut->copy()->addDays($dureeEnJours);
                    }
                }
                // Récupérer les événements déclenchés pendant cette livraison
                $eventsDuringDelivery = $events->filter(function ($event) use ($dateDebut, $dateFin, $importRow) {
                    $eventDate = Carbon::parse($event->date);
                    // Vérifier si l'événement se trouve dans la plage de dates du début et de fin de livraison
                    $isInfractionInCalendarPeriod = ($dateFin === null) ? $eventDate->eq($dateDebut) : $eventDate->between($dateDebut, $dateFin);
                    
                    // Vérifier si l'IMEI et le camion correspondent à ceux de la ligne d'importation
                    $isMatchingCamion =  strpos($event->vehicule, $importRow->camion) !== false;
                    // Retourner vrai si l'événement est dans la période de livraison et correspond aux IMEI et camion
                    return $isInfractionInCalendarPeriod && $isMatchingCamion;
                });
        
                foreach ($eventsDuringDelivery as $event){
                    $typeEvent = $event->type;
                    // $distance = getDistanceWithImeiAndPeriod($event->chauffeur, $event->imei, $dateDebut, $dateFin);
                    $penalite = Penalite::where('event', $typeEvent)->first();

                    $existingPenalty = PenaliteChauffeur::where([
                        'id_chauffeur' => getIdByRFID($event->chauffeur),
                        'id_calendar' => $importRow->id,
                        'id_event' => $event->id,
                        'id_penalite' => $penalite->id,
                        'date' => $event->date
                    ])->first();

                    // Enregistrer dans la table Penalité chauffeur
                    if(!$existingPenalty &&  strpos($event->vehicule, $importRow->camion) !== false && $event->chauffeur) {
                        insertPenaliteDrive($event, $importRow, $penalite);
                    }  
                }
        }
    }
}
//Etape 1
if(!function_exists('saveInfraction')){
    function saveInfraction(){
        $infractions = checkInfraction();
        foreach($infractions as $item){
            $existingInfraction = Infraction::where('imei', $item['imei'])
                    ->where('rfid', $item['chauffeur'])
                    ->where('event', $item['type'])
                    ->where('date_debut', $item['date_debut'])
                    ->where('date_fin', $item['date_fin'])
                    ->where('heure_debut', $item['heure_debut'])
                    ->where('heure_fin', $item['heure_fin'])
                    ->first();
    
            if (!$existingInfraction) {
            
                if(isset($item['chauffeur']) && $item['chauffeur'] != "0000000000"){
                    Infraction::create([
                        'imei' => $item['imei'],
                        'rfid' => $item['chauffeur'],
                        'vehicule' => $item['vehicule'],
                        'event' => trim($item['type']),
                        'distance' => $item['distance'],
                        'odometer' => $item['odometer'],
                        'duree_infraction' => $item['duree_infraction'],
                        'duree_initial' => $item['duree_initial'],
                        'vitesse' => $item['vitesse'],
                        'date_debut' => $item['date_debut'],
                        'date_fin' => $item['date_fin'],
                        'heure_debut' => $item['heure_debut'],
                        'heure_fin' => $item['heure_fin'],
                        'gps_debut' => $item['gps_debut'],
                        'gps_fin' => $item['gps_fin'],
                        'point' => $item['point'],
                        'insuffisance' => $item['insuffisance']
                    ]);
                }
            }
        }
    }
}

//Etape 2
if(!function_exists('checkCalendar')){
    function checkCalendar(){
        $lastmonth = DB::table('import_calendar')->latest('id')->value('id');
        $startDate = Carbon::now()->subMonths(2)->endOfMonth();
        $endDate = Carbon::now()->startOfMonth();

        $calendars = ImportExcel::where('import_calendar_id', $lastmonth)->get();
        
        $infractions = Infraction::whereBetween('date_debut', [$startDate, $endDate])->whereBetween('date_fin', [$startDate, $endDate])->get();
        
        $calendarsInInfractions = [];

        foreach ($calendars as $calendar) {
            $dateDebut = Carbon::parse($calendar->date_debut);
            $dateFin = $calendar->date_fin ? Carbon::parse($calendar->date_fin) : null;

            if ($dateFin === null) {
                // Convertir la durée en heures
                $dureeEnHeures = floatval($calendar->delais_route);
                // Calculer la date de fin en fonction de la durée
                if ($dureeEnHeures <= 1) {
                    // Durée inférieure à une journée
                    $dateFin = $dateDebut->copy()->endOfDay();
                } else {
                    $dureeEnJours = ceil($dureeEnHeures / 24);
                    // Durée d'une journée ou plus
                    $dateFin = $dateDebut->copy()->addDays($dureeEnJours);
                }
            }
            $infractionsDuringCalendar = $infractions->filter(function ($infraction) use ($dateDebut, $dateFin, $calendar) {
                $infractionDateDebut = Carbon::parse($infraction->date_debut ." ". $infraction->heure_debut);
                $infractionDateFin = Carbon::parse($infraction->date_fin ." ". $infraction->heure_fin);

                // Vérifier si l'événement se trouve dans la plage de dates du début et de fin de livraison
                $isInfractionInCalendarPeriod = ($dateFin === null) ? $infractionDateDebut->eq($dateDebut) : 
                    ($infractionDateDebut->between($dateDebut, $dateFin) || $infractionDateFin->between($dateDebut, $dateFin));
                
                // Vérifier si l'IMEI et le véhicule correspondent à ceux de la ligne d'importation
                $isMatchingVehicule = strpos($infraction->vehicule, $calendar->camion) !== false;
            
                // Retourner vrai si l'événement est dans la période de livraison et correspond au véhicule
                return $isInfractionInCalendarPeriod && $isMatchingVehicule;
            });
            
            foreach ($infractionsDuringCalendar as $infraction) {
                $infraction->update([
                    'calendar_id' => $calendar->id,
                ]);
            }
        }
    }
}

//Etape 3
if(!function_exists('distance_calendar')) {
    function distance_calendar(){
        $infractions = Infraction::with('related_calendar')->whereNotNull('calendar_id')->get();
        foreach($infractions as $item){
            $calendar_start_date = Carbon::parse($item->related_calendar->date_debut);
            $calendar_end_date = $item->related_calendar->date_fin ? Carbon::parse($item->related_calendar->date_fin) : null;

            if ($calendar_end_date === null) {
                $dureeEnHeures = floatval($item->related_calendar->delais_route);
                if ($dureeEnHeures <= 1) {
                    $calendar_end_date = $calendar_start_date->copy()->endOfDay();
                } else {
                    $dureeEnJours = ceil($dureeEnHeures / 24);
                    $calendar_end_date = $calendar_start_date->copy()->addDays($dureeEnJours);
                }
            }
            
            $distance = getDistanceWithImeiAndPeriod($item->rfid, $item->imei, $calendar_start_date, $calendar_end_date);

            $item->distance_calendar = $distance;
            $item->save();
        }
    }
}

if(!function_exists('checkDistance')){
    function checkDistance(){
        // Récupérer le nombre total d'enregistrements avec une distance de 0
        $totalRecords = PenaliteChauffeur::where('distance', '=', 0.00)->where('drive_duration', '=', 0.00)->count();
        $processedRecords = 0;
    
        while ($processedRecords < $totalRecords) {
            // Récupérer les enregistrements où la distance est égale à 0, par lots de 10
            PenaliteChauffeur::where('distance', '=', 0.00)->where('drive_duration', '=', 0.00)->skip($processedRecords)->take(5)->chunk(5, function ($penalites_drivers) use (&$processedRecords) {
                foreach ($penalites_drivers as $item) {
                    $start_date = Carbon::parse($item->related_calendar->date_debut);
                    $end_date = $item->related_calendar->date_fin ? Carbon::parse($item->related_calendar->date_fin) : null;
                    $imei = $item->related_event->imei;
                    $rfid = $item->related_event->chauffeur;
                    $event_date = Carbon::parse($item->related_event->date);
    
                    if ($end_date === null) {
                        $dureeEnHeures = floatval($item->related_calendar->delais_route);
                        if ($dureeEnHeures <= 1) {
                            $end_date = $start_date->copy()->endOfDay();
                        } else {
                            $dureeEnJours = ceil($dureeEnHeures / 24);
                            $end_date = $start_date->copy()->addDays($dureeEnJours);
                        }
                    }
    
                    $itemDistance = getDistanceWithImeiAndPeriod($rfid, $imei, $start_date, $end_date);
    
                    // Mettre à jour la distance seulement si elle est différente de 0
                    if ($itemDistance != 0) {
                        DB::table('penalite_chauffeur')
                            ->where('id', $item->id)
                            ->update(['distance' => $itemDistance['distance'], 'drive_duration' => $itemDistance['drive_duration']]);
                    }
                    
                    // Incrémenter le nombre d'enregistrements traités
                    $processedRecords++;
                }
            });
        }
    }
}

if (!function_exists('getDriveDuration')) {

    function getDriveDuration($imei_vehicule, $start_date, $end_date){
        // Formatage des dates au format YYYYMMDD
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_ROUTE,".$imei_vehicule.",".$start_date->format('YmdHis').",".$end_date->format('YmdHis').",20";
        
        $response = Http::timeout(3000)->get($url);
        $data = $response->json();

        $drive_duration_second = 0;
      
        if (isset($data['drives_duration_time'])) {
            $drive_duration_second = $data['drives_duration_time'];
        }

        return $drive_duration_second;
    }
}

if (!function_exists('getDriveDurationCached')) {
    function getDriveDurationCached($imei, $dateDebut, $dateFin) {
        // $cacheKey = "drive_duration_{$imei}_{$dateDebut}_{$dateFin}";
        $cacheKey = "drive_duration_{$imei}_" . $dateDebut->format('Y-m-d H:i:s') . '_' . $dateFin->format('Y-m-d H:i:s');
        $cacheDuration = now()->addMinutes(60); // Durée de mise en cache, par exemple 60 minutes
    
        return Cache::remember($cacheKey, $cacheDuration, function () use ($imei, $dateDebut, $dateFin) {
            return getDriveDuration($imei, $dateDebut, $dateFin);
        });
    }
}

if (!function_exists('getStopDuration')) {

    function getStopDuration($imei_vehicule, $start_date, $end_date){
        // Formatage des dates au format YYYYMMDD
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_ROUTE,".$imei_vehicule.",".$start_date->format('YmdHis').",".$end_date->format('YmdHis').",20";
        $response = Http::timeout(3000)->get($url);
        $data = $response->json();
        
        $stop_duration_second = 0;
        

        if (isset($data['stops_duration_time'])) {
            $stop_duration_second =  $data['stops_duration_time'];
        }


        return $stop_duration_second;
    }
}


if (!function_exists('getStopDurationCached')) {
    function getStopDurationCached($imei, $dateDebut, $dateFin) {
        $cacheKey = "stop_duration_{$imei}_{$dateDebut}_{$dateFin}";
        $cacheDuration = now()->addMinutes(60); // Durée de mise en cache, par exemple 60 minutes
    
        return Cache::remember($cacheKey, $cacheDuration, function () use ($imei, $dateDebut, $dateFin) {
            return getStopDuration($imei, $dateDebut, $dateFin);
        });
    }
}
//-------------------------------------------------------------------------------------------
if (!function_exists('getImeiOfTruck')){
    function getImeiOfTruck(){
        $apiTrucks = getUserVehicule();
        $trucks = Vehicule::all();
        
        foreach ($trucks as $truck) {
            foreach ($apiTrucks as $apiTruck) {
                if (trim($truck->nom) === trim($apiTruck['plate_number'])) {
                    $truck->imei = $apiTruck['imei'];
                    $truck->save();
                }
            }
        }
    }
}

if (!function_exists('getRfidWithImeiAndPeriod')) {

    // function getRfidWithImeiAndPeriod($imei_vehicule, $start_date, $end_date){
    //     $rfid = "";
    //     $distance = 0;
    //     // Formatage des dates au format YYYYMMDD
    //     $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_ROUTE,".$imei_vehicule.",".$start_date->format('YmdHis').",".$end_date->format('YmdHis').",20";
    //     $response = Http::timeout(300)->get($url);
    //     $data = $response->json();
    //     foreach($data['route'] as $item){
    //         if(isset($item[6]['rfid']) && $item[6]['rfid'] !== null){
    //             $rfid = $item[6]['rfid'];
    //             $distance = $data['route_length'];
    //             break;
    //         }
    //     }
    //     $result =  [
    //             'rfid' =>$rfid,
    //             'distance' => $distance
    //         ];
    //     return $result;
    // }
    function getRfidWithImeiAndPeriod($imei_vehicule, $start_date, $end_date) {
        $rfid = "";
        $distance = 0;
    
        // Formatage des dates au format YYYYMMDDHHMMSS
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_ROUTE," . $imei_vehicule . "," . $start_date->format('YmdHis') . "," . $end_date->format('YmdHis') . ",20";
    
        try {
            $response = Http::timeout(300)->get($url);
            $data = $response->json();
    
            // Vérifier si $data est null ou ne contient pas la clé 'route'
            if (!isset($data['route']) || !is_array($data['route'])) {
                return [
                    'rfid' => null,
                    'distance' => null
                ];
            }
    
            foreach ($data['route'] as $item) {
                if (isset($item[6]['rfid']) && $item[6]['rfid'] !== null) {
                    $rfid = $item[6]['rfid'];
                    $distance = isset($data['route_length']) ? $data['route_length'] : 0;
                    break;
                }
            }
        } catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            // Vous pouvez enregistrer le message d'erreur ou retourner des valeurs par défaut
            return [
                'rfid' => null,
                'distance' => null
            ];
        }
    
        $result = [
            'rfid' => $rfid,
            'distance' => $distance
        ];
    
        return $result;
    }
}


if(!function_exists('getDistanceTotalDriverInCalendar')){
    function getDistanceTotalDriverInCalendar($nom, $id_calendar){
        $distance = 0;
        $driver = Chauffeur::where('nom', $nom)->first();
        if(isset($driver->rfid)){
            $distance = ImportExcel::where('rfid_chauffeur', $driver->rfid)->where('import_calendar_id', $id_calendar)->sum('distance');
        }
        return $distance;
    }
}

//Function pour identifier le chauffeur, distance parcouru du calendrier et update la calendrier driver et distance et rfid
// if (!function_exists('checkDriverInCalendar')){
//     function checkDriverInCalendar(){
//         $lastmonth = DB::table('import_calendar')->latest('id')->value('id');
//         $existingTrucks = Vehicule::all(['nom', 'imei']);
//         $truckData = $existingTrucks->pluck('imei', 'nom');
//         $calendars = ImportExcel::whereIn('camion', $truckData->keys())->where('import_calendar_id', $lastmonth)->get();
        

//         $calendars->each(function ($calendar) use ($truckData) {
//             $calendar->imei = $truckData->get(trim($calendar->camion));
//             $calendar_start_date = Carbon::parse($calendar->date_debut);
//             $calendar_end_date = $calendar->date_fin ? Carbon::parse($calendar->date_fin) : null;

//             if ($calendar_end_date === null) {
//                 $dureeEnHeures = floatval($calendar->delais_route);
//                 if ($dureeEnHeures <= 1) {
//                     $calendar_end_date = $calendar_start_date->copy()->endOfDay();
//                 } else {
//                     $dureeEnJours = ceil($dureeEnHeures / 24);
//                     $calendar_end_date = $calendar_start_date->copy()->addDays($dureeEnJours);
//                 }
//             }
//             $api = getRfidWithImeiAndPeriod($calendar->imei, $calendar_start_date , $calendar_end_date);
//             $calendar->rfid_chauffeur = $api['rfid'];
//             $calendar->distance = $api['distance'];
//         });

//         foreach($calendars as $item){
//             ImportExcel::where('id', $item->id)->update([
//                 'distance' => $item->distance,
//                 'imei' => $item->imei,
//                 'rfid_chauffeur' => $item->rfid_chauffeur,
//             ]);
//         }
//     }
// }

// if (!function_exists('checkDriverInCalendar')) {
//     function checkDriverInCalendar()
//     {
//         $lastmonth = DB::table('import_calendar')->latest('id')->value('id');
//         $existingTrucks = Vehicule::all(['nom', 'imei']);
//         $truckData = $existingTrucks->pluck('imei', 'nom');
//         $truckNames = $truckData->keys();

//         ImportExcel::whereIn('camion', $truckNames)
//             ->where('import_calendar_id', $lastmonth)
//             ->chunk(10, function ($calendars) use ($truckData) {
//                 $calendars->each(function ($calendar) use ($truckData) {
//                     $calendar->imei = $truckData->get(trim($calendar->camion));
//                     $calendar_start_date = Carbon::parse($calendar->date_debut);
//                     $calendar_end_date = $calendar->date_fin ? Carbon::parse($calendar->date_fin) : null;

//                     if ($calendar_end_date === null) {
//                         $dureeEnHeures = floatval($calendar->delais_route);
//                         if ($dureeEnHeures <= 1) {
//                             $calendar_end_date = $calendar_start_date->copy()->endOfDay();
//                         } else {
//                             $dureeEnJours = ceil($dureeEnHeures / 24);
//                             $calendar_end_date = $calendar_start_date->copy()->addDays($dureeEnJours);
//                         }
//                     }
//                     $api = getRfidWithImeiAndPeriod($calendar->imei, $calendar_start_date, $calendar_end_date);
//                     $calendar->rfid_chauffeur = $api['rfid'];
//                     $calendar->distance = $api['distance'];
//                 });

//                 // Mise à jour en batch dans la base de données
//                 DB::transaction(function () use ($calendars) {
//                     foreach ($calendars as $item) {
//                         ImportExcel::where('id', $item->id)->update([
//                             'distance' => $item->distance,
//                             'imei' => $item->imei,
//                             'rfid_chauffeur' => $item->rfid_chauffeur,
//                         ]);
//                     }
//                 });
//             });
//     }
// }

// Temps de repos minimum apès une journée de travail (8h -> jour, 10 -> nuit, Si chevauchement, prendre nuit)
if(!function_exists('checkTempsReposMinApresJourneeTravail')){
    function checkTempsReposMinApresJourneeTravail(){
        //Get Infraction by chauffeur
        $infractions = Infraction::whereNotNull('calendar_id')
                                   ->where('event', '!=' , 'Temps de repos hebdomadaire')
                                   ->where('event', '!=' , 'Temps de conduite maximum dans une journée de travail')
                                   ->where('event', '!=' , 'Temps de repos minimum après une journée de travail')
                                   ->orderBy('date_debut')
                                   ->orderBy('heure_debut')
                                   ->get();
        $condition = 0;
        $dataInfraction = [];
        foreach($infractions as $infraction){
            
            $calendar_date_debut = Carbon::parse($infraction->related_calendar->date_debut);
            $calendar_date_fin = $infraction->related_calendar->date_fin ? Carbon::parse($infraction->related_calendar->date_fin) : null;
            $calendar_delais_route = $infraction->related_calendar->delais_route;

            $endingJourney = $calendar_date_debut->copy()->addDay();
            $debutSecondJourney = $calendar_date_debut->copy()->addDays(2);
            $stop_duration_second = getStopDurationCached($infraction->imei, $endingJourney, $debutSecondJourney);

            if (is_null($calendar_date_fin)) {
                if ($calendar_delais_route <= 1) {
                    // Si la date de début est pendant la journée, ajouter le délai de route à 22h, sinon ajouter à 4h pour la nuit
                    $heureDebut = $calendar_date_debut->hour;
                    if ($heureDebut >= 4 && $heureDebut < 22) {
                        $calendar_date_fin = $calendar_date_debut->copy()->setHour(22)->startOfHour(); // Fin de la journée à 22h
                    } else {
                        $calendar_date_fin = $calendar_date_debut->copy()->addDay()->setHour(4)->startOfHour(); // Début de la journée suivante à 4h
                    }
                } else {
                    $calendar_date_fin = $calendar_date_debut->copy()->addHours($calendar_delais_route)->startOfHour(); // Ajouter le délai de route à la date de début
                }
            }


            $calendar_heure_debut = $calendar_date_debut->format('H:i:s');
            $calendar_heure_fin = $calendar_date_fin->format('H:i:s');

            if (($calendar_heure_debut >= '04:00:00' && $calendar_heure_fin <= '22:00:00')) {
                // Règle de jour
                $condition = 8 * 3600;
            } elseif ($calendar_heure_debut >= '22:00:00' || $calendar_heure_fin <= '04:00:00') {
                // Règle de nuit
                $condition = 10 * 3600;
            } elseif (($calendar_heure_debut < '04:00:00' && $calendar_heure_fin > '22:00:00') || ($calendar_heure_debut < '04:00:00' && $calendar_heure_fin < '22:00:00')) {
                // Le trajet chevauche la journée et la nuit
                $condition = 10 * 3600;
            } 

            if(intval($stop_duration_second) < $condition){
                $entryExists = false;
                foreach ($dataInfraction as $entry) {
                    if ($entry['calendar_id'] == $infraction->calendar_id &&
                        $entry['imei'] == $infraction->imei &&
                        $entry['rfid'] == $infraction->rfid &&
                        $entry['date_debut'] == $infraction->date_debut &&
                        $entry['date_fin'] == $infraction->date_fin) {
                        // Une entrée similaire existe déjà, marquez l'existence de l'entrée
                        $entryExists = true;
                        break;
                    }
                }
                if (!$entryExists) {
                    $dataInfraction[] = [
                        'calendar_id' => $infraction->calendar_id,
                        'imei' => $infraction->imei,
                        'rfid' => $infraction->rfid,
                        'vehicule' => $infraction->vehicule,
                        'event' => 'Temps de repos minimum après une journée de travail',
                        'distance' => $infraction->distance,
                        'distance_calendar' => $infraction->distance_calendar,
                        'odometer' => $infraction->odometer,
                        'duree_initial' => $condition,
                        'duree_infraction' => intval($stop_duration_second),
                        'date_debut' => $endingJourney->toDateString(),
                        'date_fin' => $debutSecondJourney->toDateString(),
                        'heure_debut' => $endingJourney->toTimeString(),
                        'heure_fin' => $debutSecondJourney->toTimeString(),
                        'gps_debut' => $infraction->gps_debut,
                        'gps_fin' => $infraction->gps_fin,
                        'point' => (($condition) - (intval($stop_duration_second))) / 600,
                        'insuffisance' => (($condition) - (intval($stop_duration_second))) 
                    ];
                }
            }
        }
        
        return $dataInfraction;

    }
}

// Enregistrer l'infraction
if(!function_exists('saveReposMinimumApesJourneeTravail')){
    function saveReposMinimumApresJourneeTravail(){
        $infractions = checkTempsReposMinApresJourneeTravail();
        foreach($infractions as $item){
            $existingInfraction = Infraction::where('imei', $item['imei'])
                    ->where('rfid', $item['rfid'])
                    ->where('event', $item['event'])
                    ->where('date_debut', $item['date_debut'])
                    ->where('date_fin', $item['date_fin'])
                    ->where('heure_debut', $item['heure_debut'])
                    ->where('heure_fin', $item['heure_fin'])
                    ->first();
    
            if (!$existingInfraction) {
            
                if(isset($item['rfid']) && $item['rfid'] != "0000000000"){
                    Infraction::create([
                        'calendar_id' => $item['calendar_id'],
                        'imei' => $item['imei'],
                        'rfid' => $item['rfid'],
                        'vehicule' => $item['vehicule'],
                        'event' => trim($item['event']),
                        'distance' => $item['distance'],
                        'distance_calendar' => $item['distance_calendar'],
                        'odometer' => $item['odometer'],
                        'duree_infraction' => $item['duree_infraction'],
                        'duree_initial' => $item['duree_initial'],
                        'date_debut' => $item['date_debut'],
                        'date_fin' => $item['date_fin'],
                        'heure_debut' => $item['heure_debut'],
                        'heure_fin' => $item['heure_fin'],
                        'gps_debut' => $item['gps_debut'],
                        'gps_fin' => $item['gps_fin'],
                        'point' => $item['point'],
                        'insuffisance' => $item['insuffisance']
                    ]);
                }
            }
        }
    }
}


if (!function_exists('getMaxStopDurationTimeForPeriod')) {

    function getMaxStopDurationTimeForPeriod($imei, $calendar_date_debut) {
        // Créer une collection pour stocker les max stop_duration_time de chaque période de 24 heures
        $dailyMaxStopDurations = collect();

        // Itérer de J à J+7
        for ($i = 0; $i <= 7; $i++) {
            // Définir la date de début et de fin pour chaque période de 24 heures
            $currentStartDate = $calendar_date_debut->copy()->addDays($i);
            $currentEndDate = $currentStartDate->copy()->addHours(24);

            // Appel à la fonction getStopDurationCached pour obtenir le stop_duration_time pour cette période
            $stop_duration_time = getStopDurationCached($imei, $currentStartDate, $currentEndDate);
        
            // Ajouter le stop_duration_time à la collection
            if ($stop_duration_time !== null) {
                $dailyMaxStopDurations->push($stop_duration_time);
            }
        }
        // Trouver le maximum parmi les stop_duration_time des périodes de 24 heures
        $maxStopDurationTime = $dailyMaxStopDurations->max();

        return $maxStopDurationTime;
    }
}

// Temps de repos hebdomadaire (24h -> jour et nuit)
if(!function_exists('checkTempsReposHebdomadaire')){
    function checkTempsReposHebdomadaire(){
        //Get Infraction by chauffeur
        $infractions = Infraction::whereNotNull('calendar_id')
                                   ->where('event', '!=' , 'Temps de repos hebdomadaire')
                                   ->where('event', '!=' , 'Temps de conduite maximum dans une journée de travail')
                                   ->where('event', '!=' , 'Temps de repos minimum après une journée de travail')
                                   ->orderBy('date_debut')
                                   ->orderBy('heure_debut')
                                   ->get();
        $condition = 24;
        $conditionSecond = 24 * 3600;
        $dataInfraction = [];
        foreach($infractions as $infraction){
            $calendar_date_debut = Carbon::parse($infraction->related_calendar->date_debut);
            $calendar_date_fin = $infraction->related_calendar->date_fin ? Carbon::parse($infraction->related_calendar->date_fin) : null;
            $calendar_delais_route = $infraction->related_calendar->delais_route;

            // $j6_calendar_debut = $calendar_date_debut->copy()->addDays(6);
            // $j7_calendar_debut = $calendar_date_debut->copy()->addDays(7);
            $stop_duration_seconde = getMaxStopDurationTimeForPeriod($infraction->imei, $calendar_date_debut);
            

            if (is_null($calendar_date_fin)) {
                if ($calendar_delais_route <= 1) {
                    // Si la date de début est pendant la journée, ajouter le délai de route à 22h, sinon ajouter à 4h pour la nuit
                    $heureDebut = $calendar_date_debut->hour;
                    if ($heureDebut >= 4 && $heureDebut < 22) {
                        $calendar_date_fin = $calendar_date_debut->copy()->setHour(22)->startOfHour(); // Fin de la journée à 22h
                    } else {
                        $calendar_date_fin = $calendar_date_debut->copy()->addDay()->setHour(4)->startOfHour(); // Début de la journée suivante à 4h
                    }
                } else {
                    $calendar_date_fin = $calendar_date_debut->copy()->addHours($calendar_delais_route)->startOfHour(); // Ajouter le délai de route à la date de début
                }
            }

            $calendar_heure_debut = $calendar_date_debut->format('H:i:s');
            $calendar_heure_fin = $calendar_date_fin->format('H:i:s');

            if(intval($stop_duration_seconde) < $conditionSecond){
                $entryExists = false;
                foreach ($dataInfraction as $entry) {
                    if ($entry['calendar_id'] == $infraction->calendar_id &&
                        $entry['imei'] == $infraction->imei &&
                        $entry['rfid'] == $infraction->rfid &&
                        $entry['date_debut'] == $infraction->date_debut &&
                        $entry['date_fin'] == $infraction->date_fin) {
                        // Une entrée similaire existe déjà, marquez l'existence de l'entrée
                        $entryExists = true;
                        break;
                    }
                }
                if (!$entryExists) {
                    $dataInfraction[] = [
                        'calendar_id' => $infraction->calendar_id,
                        'imei' => $infraction->imei,
                        'rfid' => $infraction->rfid,
                        'vehicule' => $infraction->vehicule,
                        'event' => 'Temps de repos hebdomadaire',
                        'distance' => $infraction->distance,
                        'distance_calendar' => $infraction->distance_calendar,
                        'odometer' => $infraction->odometer,
                        'duree_initial' => $conditionSecond,
                        'duree_infraction' => intval($stop_duration_seconde),
                        'date_debut' => $infraction->date_debut,
                        'date_fin' => $infraction->date_fin,
                        'heure_debut' => $infraction->heure_debut,
                        'heure_fin' => $infraction->heure_fin,
                        'gps_debut' => $infraction->gps_debut,
                        'gps_fin' => $infraction->gps_fin,
                        'point' => (($conditionSecond) - (intval($stop_duration_seconde))) / 600,
                        'insuffisance' => (($conditionSecond) - (intval($stop_duration_seconde))) 
                    ];
                }
            }
        }
        return $dataInfraction;
    }
}

if(!function_exists('unique_array')){
    function unique_array($data){
        $unique_data = [];

        foreach ($data as $key => $value) {
            $unique_key = $value['imei'] . '|' . $value['rfid'] . '|' . $value['duree_initial'] . '|' . $value['duree_infraction'] . '|' . $value['point'];
            if (!isset($unique_data[$unique_key])) {
                $unique_data[$unique_key] = $value;
            }
        }

        $unique_data = array_values($unique_data);
        return $unique_data;
    }
}

if(!function_exists('SaveTempsReposHebdomadaire')){
    function SaveTempsReposHebdomadaire(){
        $infractions = checkTempsReposHebdomadaire();
        $unique_infraction = unique_array($infractions);
        
        foreach($unique_infraction as $item){
            $existingInfraction = Infraction::where('imei', $item['imei'])
                    ->where('rfid', $item['rfid'])
                    ->where('event', $item['event'])
                    ->where('date_debut', $item['date_debut'])
                    ->where('date_fin', $item['date_fin'])
                    ->where('heure_debut', $item['heure_debut'])
                    ->where('heure_fin', $item['heure_fin'])
                    ->first();
    
            if (!$existingInfraction) {
            
                if(isset($item['rfid']) && $item['rfid'] != "0000000000"){
                    Infraction::create([
                        'calendar_id' => $item['calendar_id'],
                        'imei' => $item['imei'],
                        'rfid' => $item['rfid'],
                        'vehicule' => $item['vehicule'],
                        'event' => trim($item['event']),
                        'distance' => $item['distance'],
                        'distance_calendar' => $item['distance_calendar'],
                        'odometer' => $item['odometer'],
                        'duree_infraction' => $item['duree_infraction'],
                        'duree_initial' => $item['duree_initial'],
                        'date_debut' => $item['date_debut'],
                        'date_fin' => $item['date_fin'],
                        'heure_debut' => $item['heure_debut'],
                        'heure_fin' => $item['heure_fin'],
                        'gps_debut' => $item['gps_debut'],
                        'gps_fin' => $item['gps_fin'],
                        'point' => $item['point'],
                        'insuffisance' => $item['insuffisance']
                    ]);
                }
            }
        }
    }
}

if(!function_exists('checkTempsConduiteMaxJourTravail')){
    function checkTempsConduiteMaxJourTravail(){
        // Récupérer toutes les pénalités
        $infractions = Infraction::whereNotNull('calendar_id')
                                   ->where('event', '!=' , 'Temps de repos hebdomadaire')
                                   ->where('event', '!=' , 'Temps de conduite maximum dans une journée de travail')
                                   ->where('event', '!=' , 'Temps de repos minimum après une journée de travail')
                                   ->orderBy('date_debut')
                                   ->orderBy('heure_debut')
                                   ->get();
        $limite = 0;
        // Tableau pour stocker les heures de conduite pour chaque chauffeur
        $dataInfraction = [];

        // Parcourir chaque pénalité
        foreach ($infractions as $infraction) {
            $calendar_date_debut = Carbon::parse($infraction->related_calendar->date_debut);
            $calendar_date_fin = $infraction->related_calendar->date_fin ? Carbon::parse($infraction->related_calendar->date_fin) : null;
            $calendar_delais_route = $infraction->related_calendar->delais_route;


            $drive_duration_second = getDriveDurationCached($infraction->imei, $calendar_date_debut, $calendar_date_fin);

            if (is_null($calendar_date_fin)) {
                if ($calendar_delais_route <= 1) {
                    // Si la date de début est pendant la journée, ajouter le délai de route à 22h, sinon ajouter à 4h pour la nuit
                    $heureDebut = $calendar_date_debut->hour;
                    if ($heureDebut >= 4 && $heureDebut < 22) {
                        $calendar_date_fin = $calendar_date_debut->copy()->setHour(22)->startOfHour(); // Fin de la journée à 22h
                    } else {
                        $calendar_date_fin = $calendar_date_debut->copy()->addDay()->setHour(4)->startOfHour(); // Début de la journée suivante à 4h
                    }
                } else {
                    $calendar_date_fin = $calendar_date_debut->copy()->addHours($calendar_delais_route)->startOfHour(); // Ajouter le délai de route à la date de début
                }
            }

            $calendar_heure_debut = $calendar_date_debut->format('H:i:s');
            $calendar_heure_fin = $calendar_date_fin->format('H:i:s');
            
        
            if (($calendar_heure_debut >= '04:00:00' && $calendar_heure_fin <= '22:00:00')) {
                // Règle de jour
                $limite = 13 * 3600;
            } elseif ($calendar_heure_debut >= '22:00:00' || $calendar_heure_fin <= '04:00:00') {
                // Règle de nuit
                $limite = 12 * 3600;
            } elseif (($calendar_heure_debut < '04:00:00' && $calendar_heure_fin > '22:00:00') || ($calendar_heure_debut < '04:00:00' && $calendar_heure_fin < '22:00:00')) {
                // Le trajet chevauche la journée et la nuit
                $limite = 12 * 3600;
            } 

            if(intval($drive_duration_second) > $limite){
                $entryExists = false;
                foreach ($dataInfraction as $entry) {
                    if ($entry['calendar_id'] == $infraction->calendar_id &&
                        $entry['imei'] == $infraction->imei &&
                        $entry['rfid'] == $infraction->rfid &&
                        $entry['date_debut'] == $infraction->date_debut &&
                        $entry['date_fin'] == $infraction->date_fin) {
                        // Une entrée similaire existe déjà, marquez l'existence de l'entrée
                        $entryExists = true;
                        break;
                    }
                }
                if (!$entryExists) {
                    $dataInfraction[] = [
                        'calendar_id' => $infraction->calendar_id,
                        'imei' => $infraction->imei,
                        'rfid' => $infraction->rfid,
                        'vehicule' => $infraction->vehicule,
                        'event' => 'Temps de conduite maximum dans une journée de travail',
                        'distance' => $infraction->distance,
                        'distance_calendar' => $infraction->distance_calendar,
                        'odometer' => $infraction->odometer,
                        'duree_initial' => $limite,
                        'duree_infraction' => intval($drive_duration_second),
                        'date_debut' => $calendar_date_debut->toDateString(),
                        'date_fin' => $calendar_date_fin->toDateString(),
                        'heure_debut' => $calendar_date_debut->toTimeString(),
                        'heure_fin' => $calendar_date_fin->toTimeString(),
                        'gps_debut' => $infraction->gps_debut,
                        'gps_fin' => $infraction->gps_fin,
                        'point' => ($drive_duration_second -$limite) / 600,
                        'insuffisance' => ($drive_duration_second  - $limite) 
                    ];
                }
            }
        }
        return $dataInfraction;
    }
}

if(!function_exists('SaveTempsConduiteMaxJourTravail')){
    function SaveTempsConduiteMaxJourTravail(){
        $infractions = checkTempsConduiteMaxJourTravail();
        foreach($infractions as $item){
            $existingInfraction = Infraction::where('imei', $item['imei'])
                    ->where('rfid', $item['rfid'])
                    ->where('event', $item['event'])
                    ->where('date_debut', $item['date_debut'])
                    ->where('date_fin', $item['date_fin'])
                    ->where('heure_debut', $item['heure_debut'])
                    ->where('heure_fin', $item['heure_fin'])
                    ->first();
    
            if (!$existingInfraction) {
            
                if(isset($item['rfid']) && $item['rfid'] != "0000000000" && trim($item['rfid']) != trim("u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0	")){
                    Infraction::create([
                        'calendar_id' => $item['calendar_id'],
                        'imei' => $item['imei'],
                        'rfid' => $item['rfid'],
                        'vehicule' => $item['vehicule'],
                        'event' => trim($item['event']),
                        'distance' => $item['distance'],
                        'distance_calendar' => $item['distance_calendar'],
                        'odometer' => $item['odometer'],
                        'duree_infraction' => $item['duree_infraction'],
                        'duree_initial' => $item['duree_initial'],
                        'date_debut' => $item['date_debut'],
                        'date_fin' => $item['date_fin'],
                        'heure_debut' => $item['heure_debut'],
                        'heure_fin' => $item['heure_fin'],
                        'gps_debut' => $item['gps_debut'],
                        'gps_fin' => $item['gps_fin'],
                        'point' => $item['point'],
                        'insuffisance' => $item['insuffisance']
                    ]);
                }
            }
        }
    }
}

if(!function_exists('checkTempsConduiteContinue')){
    function checkTempsConduiteContinue(){
        // Récupérer toutes les pénalités
        $infractions = Infraction::whereNotNull('calendar_id')
                                    ->where(function ($query) {
                                        $query->where('event', 'TEMPS DE CONDUITE CONTINUE NUIT')
                                            ->orWhere('event', 'TEMPS DE CONDUITE CONTINUE JOUR');
                                    })      
                                   ->orderBy('date_debut')
                                   ->orderBy('heure_debut')
                                   ->get();

        $limite = 0;
        // Tableau pour stocker les heures de conduite pour chaque chauffeur
        $updates = [];

        // Parcourir chaque pénalité
        foreach ($infractions as $infraction) {
            $calendar_date_debut = Carbon::parse($infraction->related_calendar->date_debut);
            $calendar_date_fin = $infraction->related_calendar->date_fin ? Carbon::parse($infraction->related_calendar->date_fin) : null;
            $calendar_delais_route = $infraction->related_calendar->delais_route;


            $drive_duration_second = getDriveDurationCached($infraction->imei, $calendar_date_debut, $calendar_date_fin);

            if (is_null($calendar_date_fin)) {
                if ($calendar_delais_route <= 1) {
                    // Si la date de début est pendant la journée, ajouter le délai de route à 22h, sinon ajouter à 4h pour la nuit
                    $heureDebut = $calendar_date_debut->hour;
                    if ($heureDebut >= 4 && $heureDebut < 22) {
                        $calendar_date_fin = $calendar_date_debut->copy()->setHour(22)->startOfHour(); // Fin de la journée à 22h
                    } else {
                        $calendar_date_fin = $calendar_date_debut->copy()->addDay()->setHour(4)->startOfHour(); // Début de la journée suivante à 4h
                    }
                } else {
                    $calendar_date_fin = $calendar_date_debut->copy()->addHours($calendar_delais_route)->startOfHour(); // Ajouter le délai de route à la date de début
                }
            }

            $calendar_heure_debut = $calendar_date_debut->format('H:i:s');
            $calendar_heure_fin = $calendar_date_fin->format('H:i:s');
            
        
            if (($calendar_heure_debut >= '04:00:00' && $calendar_heure_fin <= '22:00:00')) {
                // Règle de jour
                $limite = 4 * 3600;
            } elseif ($calendar_heure_debut >= '22:00:00' || $calendar_heure_fin <= '04:00:00') {
                // Règle de nuit
                $limite = 2 * 3600;
            } elseif (($calendar_heure_debut < '04:00:00' && $calendar_heure_fin > '22:00:00') || ($calendar_heure_debut < '04:00:00' && $calendar_heure_fin < '22:00:00')) {
                // Le trajet chevauche la journée et la nuit
                $limite = 2 * 3600;
            } 
            $limite = $limite + 660;
            if(intval($drive_duration_second) > $limite){
                $updates[] = [
                    'id' => $infraction->id,
                    'duree_initial' => $limite,
                    'duree_infraction' => intval($drive_duration_second),
                    'point' => ($drive_duration_second - $limite) / 600,
                    'insuffisance' => ($drive_duration_second  - $limite) 
                ];
            }
        }
        
        foreach($updates as $update){
            Infraction::where('id', $update['id'])
            ->update([
                'duree_initial' => $update['duree_initial'],
                'duree_infraction' => $update['duree_infraction'],
                'point' => $update['point'],
                'insuffisance' => $update['insuffisance'],
            ]);
        }
    }
}

// if(!function_exists('v_infraction')){
//     function v_infraction($imei, $chauffeur, $type){
//         $results = DB::table('event')
//         ->select('imei', 'chauffeur', 'vehicule', 'type', 'odometer', 'latitude', 'longitude', DB::raw("LEFT(date,10) as simple_date"), DB::raw("RIGHT(date,8) as heure"), 'date as date_heure')
//         ->where('imei', '=', $imei)
//         ->where('chauffeur', '=', $chauffeur)
//         ->where('type', '=', $type)
//         ->orderBy('heure', 'ASC')
//         ->get();
        
//         return $results;
//     }
// }

// if(!function_exists('getPointPenaliteByEventType')){
//     function getPointPenaliteByEventType($event){
//         $eventType = trim($event);
//         $result = DB::table('penalite')
//         ->select('point_penalite')
//         ->where('event', '=', $eventType)
//         ->first();

//         return $result->point_penalite;
//     }
// }

// if (!function_exists('checkInfraction')) {
//     function checkInfraction()
//     {
//         $startDate = Carbon::now()->subMonths(2)->endOfMonth();
//         $endDate = Carbon::now()->startOfMonth();

//         $records = DB::table('event')
//         ->select('imei', 'chauffeur', 'vehicule', 'type', 'odometer','vitesse', 'latitude', 'longitude', DB::raw("LEFT(date,10) as simple_date"), DB::raw("RIGHT(date,8) as heure"), 'date as date_heure')
//         ->whereBetween('date', [$startDate, $endDate])
//         ->orderBy('simple_date', 'ASC')
//         ->orderBy('heure', 'ASC')->get();
        
//         $eventTypes = [
//             'Accélération brusque', 
//             'Freinage brusque', 
//             'Excès de vitesse en agglomération', 
//             'Excès de vitesse hors agglomération', 
//             'Survitesse excessive',
//             'Survitesse sur la piste de Tritriva',
//             'Survitesse sur la piste d\'Ibity',
//             // 'TEMPS DE CONDUITE CONTINUE JOUR',
//             // 'TEMPS DE CONDUITE CONTINUE NUIT',
//         ];
//         $results = [];
//         $prevRecord = null;
//         $firstValidRecord = null;
//         $lastValidRecord = null;
//         $maxSpeed = 0;

//         foreach ($records as $record) {
//             // if(trim($record->type) === "Accélération brusque" || trim($record->type) === "Freinage brusque"){
//             if(in_array(trim($record->type), $eventTypes)){
//                 $results[] = [
//                     'imei' => $record->imei,
//                     'chauffeur' => $record->chauffeur,
//                     'vehicule' => $record->vehicule,
//                     'type' => $record->type,
//                     'distance' => 0,
//                     'vitesse' => $record->vitesse,
//                     'odometer' => $record->odometer,
//                     'duree_infraction' => 1, 
//                     'duree_initial' => 1, 
//                     'date_debut' => $record->simple_date,
//                     'date_fin' => $record->simple_date,
//                     'heure_debut' => $record->heure,
//                     'heure_fin' => $record->heure,
//                     'date_heure_debut' => $record->date_heure,
//                     'date_heure_fin' => $record->date_heure,
//                     'gps_debut' => $record->latitude . ',' . $record->longitude,
//                     'gps_fin' => $record->latitude . ',' . $record->longitude,
//                     'point' => getPointPenaliteByEventType($record->type),
//                     'insuffisance' => 0
//                 ];
//             }
//             // else{
            
//             //     if ($firstValidRecord === null) {
//             //         $firstValidRecord = $record;
//             //         $maxSpeed = $record->vitesse;
//             //     }

//             //     // Vérifier s'il y a un enregistrement précédent
//             //     if ($prevRecord !== null) {
//             //         // Comparer les attributs chauffeur, véhicule et date sans tenir compte de l'heure
//             //         if ($record->chauffeur === $prevRecord->chauffeur &&
//             //             $record->vehicule === $prevRecord->vehicule &&
//             //             $record->simple_date === $prevRecord->simple_date && trim($record->type) === trim($prevRecord->type)) {
//             //             // Convertir les dates en objets DateTime pour faciliter la comparaison
//             //             $prevDate = new DateTime($prevRecord->date_heure);
//             //             $currentDate = new DateTime($record->date_heure);
//             //             $tolerence = Penalite::where('event','=', $record->type)->first();
//             //             // Calculer la différence en secondes
//             //             $differenceSeconds = $currentDate->getTimestamp() - $prevDate->getTimestamp();

//             //             if ($differenceSeconds === $tolerence->param) {
//             //                 // Si l'intervalle est de 60 secondes, continuer à traiter les enregistrements
//             //                 // Mettre à jour le dernier enregistrement valide
//             //                 if ($record->vitesse > $maxSpeed) {
//             //                     $maxSpeed = $record->vitesse; // Mettre à jour la vitesse maximale si la vitesse actuelle est plus grande
//             //                 }
//             //                 $lastValidRecord = $record;
//             //             } else {
//             //                 // Si l'intervalle n'est pas de 60 secondes, réinitialiser les enregistrements valides
//             //                 if ($firstValidRecord !== null && $lastValidRecord !== null) {
//             //                     $results[] = groupedInfraction($firstValidRecord, $prevRecord, $maxSpeed);
//             //                 }
//             //                 $firstValidRecord = $record;
//             //                 $lastValidRecord = null;
//             //                 $maxSpeed = $record->vitesse; 
//             //             }
//             //         } else {
//             //             // Si les attributs chauffeur, véhicule ou date sont différents, réinitialiser les enregistrements valides
//             //             if ($firstValidRecord !== null && $lastValidRecord !== null) {
//             //                 $results[] = groupedInfraction($firstValidRecord, $prevRecord, $maxSpeed);
//             //             }
//             //             $firstValidRecord = $record;
//             //             $lastValidRecord = null;
//             //             $maxSpeed = $record->vitesse;
//             //         }
//             //     }
//             //     // Mettre à jour l'enregistrement précédent
//             //     $prevRecord = $record;
//             // }
//         }
//         // Ajouter le dernier groupe d'infractions
//         // if ($firstValidRecord !== null && $lastValidRecord !== null) {
//         //     $results[] = groupedInfraction($firstValidRecord, $prevRecord, $maxSpeed);
//         // }

//         return $results;
//     }
// }


// if(!function_exists('groupedInfraction')){
//     function groupedInfraction($firstRecord, $lastRecord, $maxvitesse){
//         $firstDate = new DateTime($firstRecord->date_heure);
//         $lastDate = new DateTime($lastRecord->date_heure);
//         $differenceSeconds = $lastDate->getTimestamp() - $firstDate->getTimestamp();
//         $distance = $lastRecord->odometer - $firstRecord->odometer;
//         $tolerence = Penalite::where('event','=',$firstRecord->type)->first();
        

//         return [
//             'imei' => $firstRecord->imei,
//             'chauffeur' => $firstRecord->chauffeur,
//             'vehicule' => $firstRecord->vehicule,
//             'type' => $firstRecord->type,
//             'distance' => $distance,
//             'odometer' => $lastRecord->odometer,
//             'vitesse' => $maxvitesse,
//             'duree_infraction' => ($differenceSeconds + $tolerence->default_value), 
//             'duree_initial' => $tolerence->default_value, 
//             'date_debut' => $firstRecord->simple_date,
//             'date_fin' => $lastRecord->simple_date,
//             'heure_debut' => $firstRecord->heure,
//             'heure_fin' => $lastRecord->heure,
//             'date_heure_debut' => $firstRecord->date_heure,
//             'date_heure_fin' => $lastRecord->date_heure,
//             'gps_debut' => $firstRecord->latitude . ',' . $firstRecord->longitude,
//             'gps_fin' => $lastRecord->latitude . ',' . $lastRecord->longitude,
//             'point' => (($differenceSeconds + $tolerence->default_value) * $tolerence->point_penalite) / $tolerence->default_value,
//             'insuffisance' => 0
//         ];
//     }
// }

use Illuminate\Support\Str;
if (!function_exists('getPlateNumberByRfidAndTransporteur()')) {

    function getPlateNumberByRfidAndTransporteur($driverId, $transporteurId){
        set_time_limit(2000);
        $chauffeur = Chauffeur::where('id', $driverId)->first();
        $transporteur = Transporteur::where('id', $transporteurId)->first();
        // Formatage des dates au format YYYYMMDD
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=USER_GET_OBJECTS";
        
        // $response = Http::timeout(300)->get($url);
        $response = Http::timeout(5000)->retry(3, 1000)->get($url);
        $data = $response->json();
        $plate_number = "";
        // foreach($data as $item){
        //     if (isset($chauffeur->rfid) && isset($item['params']['rfid'])  && $item['params']['rfid'] === $chauffeur->rfid) {
        //         $plate_number = $item['plate_number'];
        //     }
        // }
        $chunks = array_chunk($data, 10);
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $item) {
                if (isset($chauffeur->rfid) && isset($item['params']['rfid']) && $item['params']['rfid'] === $chauffeur->rfid) {
                    $plate_number = $item['imei'];
                    break 2; // Quitter les deux boucles dès qu'une correspondance est trouvée
                }
            }
        }
        
        return $plate_number;
    }
}



if(!function_exists('checkTruckinCalendar')){
    function checkTruckinCalendar($id_planning, $camion){
        if (strpos($camion, ' - ') !== false) {
            // Extraire uniquement l'immatriculation
            $immatriculation = explode(' - ', $camion)[0];
        } else {
            // Prendre le camion tel quel (cas des immatriculations normales)
            $immatriculation = $camion;
        }
        $exists = ImportExcel::where('import_calendar_id', $id_planning)
                     ->where('camion', 'LIKE', '%'. $immatriculation .'%') // Recherche en début de chaîne
                     ->exists();


        return $exists ? true : false;
    }
}

if(!function_exists('checkBadgeinCalendar')){
    function checkBadgeinCalendar($id_planning, $badge){
        $badge = trim($badge);
        $exists = ImportExcel::where('import_calendar_id', $id_planning)
                     ->where('badge_chauffeur', $badge)
                     ->exists();


        return $exists ? true : false;
    }
}

if(!function_exists('getTruckByImei')){
    function getTruckByImei($imei){
        $truck = Vehicule::where('imei', $imei)
                     ->value('nom');

        return $truck;
    }
}

if(!function_exists('getBadgeCalendarByTruck')){
    function getBadgeCalendarByTruck($id_planning, $imei){
        $truck = Vehicule::where('imei', $imei)
                     ->value('nom');
        
        $numero_badge =  ImportExcel::where('import_calendar_id', $id_planning)
        ->where('camion', $truck)
        ->pluck('badge_chauffeur')
        ->first();

        return $numero_badge;
    }
}

if(!function_exists('getDriverInfractionWithmaximumPoint')){
    function getDriverInfractionWithmaximumPoint($id_driver, $imei, $id_planning){
        $mois = Importcalendar::where('id', $id_planning)
        ->select(DB::raw('MONTH(date_debut) as mois'))
        ->value('mois');

        $query1 = DB::table('infraction as i')
            ->leftJoin('import_excel as ie', function ($join) {
                $join->on('i.imei', '=', 'ie.imei')
                    ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= ie.date_debut")
                    ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= ie.date_fin");
            })
            ->join('chauffeur as ch', 'i.rfid', '=', 'ch.rfid')
            ->join('transporteur as t', 'ch.transporteur_id', '=', 't.id')
            ->select(
                'ch.nom as driver',
                'ch.id as driver_id',
                'ch.rfid as rfid',
                't.nom as transporteur_nom',
                'i.event as infraction',
                DB::raw('SUM(i.point) as total_point')
            )
            ->where('ch.id', $id_driver)
            ->where('ie.import_calendar_id', $id_planning)
            ->where('i.event', '!=', 'temps de repos hebdomadaire')
            ->groupBy('ch.id', 'ch.nom', 'ch.rfid', 't.nom', 'i.event');

        $query2 = DB::table('infraction as i')
            ->join('chauffeur as ch', 'i.rfid', '=', 'ch.rfid')
            ->join('transporteur as t', 'ch.transporteur_id', '=', 't.id')
            ->select(
                'ch.nom as driver',
                'ch.id as driver_id',
                'ch.rfid as rfid',
                't.nom as transporteur_nom',
                'i.event as infraction',
                DB::raw('SUM(i.point) as total_point')
            )
            ->where('ch.id', $id_driver)
            ->where('i.event', 'temps de repos hebdomadaire')
            ->whereRaw('MONTH(CONCAT(i.date_debut, " ", i.heure_debut)) = ?', $mois)
            ->groupBy('ch.id', 'ch.nom', 'ch.rfid', 't.nom', 'i.event');

        $subquery = $query1->union($query2);

        // Utiliser selectSub pour la requête principale
        $result = DB::table(DB::raw("({$subquery->toSql()}) as subquery"))
        ->mergeBindings($subquery) // Merge bindings from the subquery
        ->select('subquery.driver', 'subquery.rfid', 'subquery.transporteur_nom', 'subquery.infraction', 'subquery.total_point as point')
        ->orderBy('subquery.total_point', 'desc')
        ->limit(1)
        ->first();

        if ($result) {
            // Traiter les résultats obtenus
            // return $result->infraction . " avec un total de " . $result->point;
            return $result->infraction;
        } else {
            $results2 =  getTruckInfractionWithmaximumPoint($imei, $id_planning);

            if($result2){
                return $result2->infraction;
            }
        }
    }
}


if(!function_exists('getTruckInfractionWithmaximumPoint')){
    function getTruckInfractionWithmaximumPoint($imei, $id_planning){
        $mois = Importcalendar::where('id', $id_planning)
        ->select(DB::raw('MONTH(date_debut) as mois'))
        ->value('mois');
            
        $query1 = DB::table('infraction as i')
            ->leftJoin('import_excel as ie', function ($join) {
                $join->on('i.imei', '=', 'ie.imei')
                    ->whereRaw("CONCAT(i.date_debut, ' ', i.heure_debut) >= ie.date_debut")
                    ->whereRaw("CONCAT(i.date_fin, ' ', i.heure_fin) <= ie.date_fin");
            })
            ->join('vehicule as v', 'i.imei', '=', 'v.imei')
            ->join('transporteur as t', 'v.id_transporteur', '=', 't.id')
            ->select(
                'v.imei as imei',
                't.nom as transporteur_nom',
                'i.event as infraction',
                DB::raw('SUM(i.point) as total_point')
            )
            ->where('v.imei', $imei)
            ->where('ie.import_calendar_id', $id_planning)
            ->where('i.event', '!=', 'temps de repos hebdomadaire')
            ->groupBy('v.imei', 't.nom', 'i.event');
        
        $query2 = DB::table('infraction as i')
            ->join('vehicule as v', 'i.imei', '=', 'v.imei')
            ->join('transporteur as t', 'v.id_transporteur', '=', 't.id')
            ->select(
                'v.imei as imei',
                't.nom as transporteur_nom',
                'i.event as infraction',
                DB::raw('SUM(i.point) as total_point')
            )
            ->where('v.imei', $imei)
            ->where('i.event', 'temps de repos hebdomadaire')
            ->whereRaw('MONTH(CONCAT(i.date_debut, " ", i.heure_debut)) = ?', $mois)
            ->groupBy('v.imei', 't.nom', 'i.event');
        
        $subquery = $query1->union($query2);


        // Utiliser selectSub pour la requête principale
        $result = DB::table(DB::raw("({$subquery->toSql()}) as subquery"))
        ->mergeBindings($subquery) // Merge bindings from the subquery
        ->select('subquery.imei', 'subquery.transporteur_nom', 'subquery.infraction', 'subquery.total_point as point')
        ->orderBy('subquery.total_point', 'desc')
        ->limit(1)
        ->first();

        if ($result) {
            // Traiter les résultats obtenus
            return $result->infraction;
        } else {
            // Aucun résultat trouvé, gérer le cas où il n'y a pas de données
            return "";
        }
    }
}




if (!function_exists('NotificationValidation')) {

    function NotificationValidation($message,$modifier_id)
    {
        $admin = User::where('id',$modifier_id)->whereHas('roles', function ($query) {
            $query->where('name', 'operator');
        })->first();
        
        if($admin){
            Notification::send($admin, new ValidationChauffeurNotification($message));
        }
        
    }
}


if (!function_exists('getImeibyPlateNumber()')) {

    function getImeibyPlateNumber()
    {
        set_time_limit(2000);
    
        // Récupérer les données de ImportExcel
        $importExcelData = ImportExcel::all(); 
    
        // URL de l'API
        $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=USER_GET_OBJECTS";
        
        // Récupérer les données de l'API avec timeout et retry
        $response = Http::timeout(5000)->retry(3, 1000)->get($url);
        $apiData = $response->json();
        
        // Vérification si les données sont valides
        if (!$apiData || !is_array($apiData)) {
            return response()->json(['error' => 'Données API invalides'], 500);
        }
    
        // Création d'une collection pour faciliter la recherche
        // $apiCollection = collect($apiData)->keyBy('plate_number');
        $apiCollection = collect($apiData)
            ->map(function ($item) {
                $item['plate_number'] = trim($item['plate_number']);
                return $item;
            })
            ->keyBy('plate_number');
        
        $imei_platenumber = [];
        // Mettre à jour les données ImportExcel
        foreach ($importExcelData as $row) {
            $plateNumber = $row->camion; // Assure-toi que cette colonne correspond bien à l'immatriculation du camion
    
            // Vérifier si l'immatriculation existe dans les données de l'API
            if ($apiCollection->has($plateNumber)) {
                $imei = $apiCollection[$plateNumber]['imei'] ?? 'Immatricule inexistant';
                $rfid = $apiCollection[$plateNumber]['params']['rfid'] ?? 'RFID inexistant';
                
                //Mise à jour uniquement si un IMEI est trouvé
                if ($imei) {
                    // $imei_platenumber [] = [
                    //     'camion' => $plateNumber,
                    //     'imei' => $imei,
                    //     'rfid' => $rfid
                    // ];
                    $row->update(['imei' => $imei, 'rfid_chauffeur' => $rfid]);
                }
            }
        }


        return response()->json(['message' => 'Mise à jour des IMEI terminée']);
    }
}


if (!function_exists('updateDatebeginAndEndByImei()')) {

    /**
     * Fonction qui update le date debut et fin de chaque calendrier en récuperant la date debut lié à l'évenement
     * @param mixed $calendar
     * @return Illuminate\Http\JsonResponse|mixed
     */
    function updateDatebeginAndEndByImei($calendar) {

        try{
            $date_debut = $calendar->date_debut;
            $date_fin = $calendar->date_fin;
            $date_debut_parse = Carbon::parse($calendar->date_debut)->format('YmdHis');
            $date_fin_parse = Carbon::parse($calendar->date_fin)->format('YmdHis');
            $imei = $calendar->imei;
            $delais_route = $calendar->delais_route; // Délais en jours (peut être décimal)
            
            $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=OBJECT_GET_EVENTS,{$imei},{$date_debut_parse},{$date_fin_parse}";
            $response = Http::timeout(600)->get($url);
            $data = $response->json();
            
            // Liste des événements à comparer
            $evenements = ['Sortie Ibity (Usine , Ibity)', 'Sortie Port Tamatave (Port TAMATAVE)', 'Sortie usine Tanjombato (Usine , Tanjombato)'];
            // Initialisation de la nouvelle date de début
            $nouvelle_date_debut = $date_debut;
            
            if(!is_null($data)){
                foreach ($data as $data_all) {
                    $eventType = $data_all[1] ?? null; // Deuxième élément (événement)
                    $eventDate = $data_all[4] ?? null; // Cinquième élément (date)
            
                    if ($eventType && in_array($eventType, $evenements) && $eventDate) {
                        // Comparer avec la date actuelle
                        if ($eventDate != $date_debut) { 
                            $nouvelle_date_debut = $eventDate;
                            break; // On prend la première occurrence trouvée
                        }
                    }
                }
            
                // Si la date a changé, on met à jour
                if ($nouvelle_date_debut) {
                    // Calcul de la nouvelle date de fin
                    $nouvelle_date_debut_carbon = Carbon::parse($nouvelle_date_debut);

                    // Conversion du délai en heures et minutes
                    $heures_a_ajouter = floor($delais_route * 24); // Partie entière en heures
                    $minutes_a_ajouter = ($delais_route * 24 - $heures_a_ajouter) * 60; // Partie décimale en minutes

                    // Ajouter les heures et minutes
                    $nouvelle_date_fin = $nouvelle_date_debut_carbon->copy()
                        ->addHours($heures_a_ajouter)
                        ->addMinutes($minutes_a_ajouter);

                    // Mise à jour des valeurs dans $calendar
                    $calendar->date_debut = $nouvelle_date_debut;
                    $calendar->date_fin = $nouvelle_date_fin;

                    // Sauvegarde dans la base de données
                    $calendar->save();
                }
            }
        }catch(Exception $e){
            return response()->json(['message' => 'Erreur ' .   $e->getMessage()]);
        }
    }
}



if (!function_exists('SaveVehiculeFromCalendar()')) {

    /**
     * 
     * @param mixed $calendar
     * @return Illuminate\Http\JsonResponse|mixed
     */
    function SaveVehiculeFromCalendar($id_planning, $logger) {
        try{
            set_time_limit(2000);
        
            // Récupérer les données de ImportExcel
            $camions = ImportExcel::where('import_calendar_id', $id_planning)
            ->pluck('camion') 
            ->unique()       
            ->values();

            // URL de l'API
            $url = "www.m-tectracking.mg/api/api.php?api=user&ver=1.0&key=5AA542DBCE91297C4C3FB775895C7500&cmd=USER_GET_OBJECTS";
            
            // Récupérer les données de l'API avec timeout et retry
            $response = Http::timeout(5000)->retry(3, 1000)->get($url);
            $apiData = $response->json();
            
            // Vérification si les données sont valides
            if (!$apiData || !is_array($apiData)) {
                return response()->json(['error' => 'Données API invalides'], 500);
            }
        
            // Création d'une collection pour faciliter la recherche
            $apiCollection = collect($apiData)
            ->map(function ($item) {
                $item['plate_number'] = trim($item['plate_number']);
                return $item;
            })
            ->keyBy('plate_number');
                    
            $imei_platenumber = [];
            // Mettre à jour les données ImportExcel
            foreach ($camions as $row) {
                $plateNumber = $row; // Assure-toi que cette colonne correspond bien à l'immatriculation du camion
                // Vérifier si l'immatriculation existe dans les données de l'API
                if ($apiCollection->has($plateNumber)) {
                    $imei = (string) $apiCollection[$plateNumber]['imei'] ?? null;
                    $transporteur_name = $apiCollection[$plateNumber]['group_name'] ?? null;

                    //  Si le group_name est null, on tente de le déduire depuis le champ "name"
                    if (is_null($transporteur_name)) {
                        $name = $apiCollection[$plateNumber]['name'] ?? null;

                        if (!is_null($name) && str_contains($name, '-')) {
                            $parts = explode('-', $name);
                            $transporteur_name = trim(end($parts)); // "BIOTRANS"
                        }
                    }

                    //  Créer ou récupérer le transporteur s'il y a un nom trouvé
                    if (!empty($transporteur_name)) {
                        $transporteur = Transporteur::firstOrCreate(
                            ['nom' => $transporteur_name]
                        );
                    } else {
                        $transporteur = null; // Aucun transporteur identifié
                    }

                    $existingVehicule =  Vehicule::where('imei', $imei )->where('id_planning', $id_planning)->first() ;
                    //Mise à jour uniquement si un IMEI est trouvé
                    if (!$existingVehicule) {
                        $vehicule = Vehicule::create([
                            'imei' =>  $imei,
                            'nom' => $row,
                            'description' => null,
                            'id_transporteur' => $transporteur->id ?? NULL,
                            'id_planning' => $id_planning
                        ]);
                    }    
                } else {
                    $logger->warning('Camion non géolocalisé : ', [
                        'camion' => $row,
                        'raison' => 'Aucune correspondance trouvée dans l\'API' 
                    ]);
                }
            }

            return response()->json(['message' => 'Mise à jour des IMEI terminée']);
        }catch(Exception $e){
            \Log::error('Erreur dans SaveVehiculeFromCalendar', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

}