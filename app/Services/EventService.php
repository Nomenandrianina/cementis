<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Infraction;
use App\Models\Penalite;
use App\Models\Vehicule;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventService
{
    const ALLOWED_EVENT = [
        // 'Accélération brusque', 
        // 'Freinage brusque', 
        'Excès de vitesse en agglomération', 
        'Excès de vitesse hors agglomération', 
        'Survitesse excessive',
        'Survitesse sur la piste de Tritriva',
        'Survitesse sur la piste d\'Ibity',
    ];

    /**
     * Antonio
     * get Event last 7 jours et filtrer par les evenements existant puis enregistrer dans la base.
     *
     */
    public function saveEventLastSevenDays(){
        try{
            $apiService = new GeolocalisationService();
            $apiData= $apiService->getEventApi();
            
            $penalitesAllowed = Penalite::all()->toArray();
            $allowedTypes = array_column($penalitesAllowed, 'duree','event');
            $filteredData = [];

            // Parcourir les données de l'API
            foreach ($apiData as $event) {
                if (in_array($event[1], array_keys($allowedTypes)) && isset($event[10]['rfid'])) {
                    $filteredData[] = $event;
                }
            }

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
        }catch (Exception $e) {
            // Logger l'erreur pour la traçabilité
            Log::error('Erreur lors de l\'insertion des évenements : ' . $e->getMessage());
            
            return null;
        }
        
    }

    /**
     * Antonio
     * Enregister les events pour un imei et entre deux période.
     *
     * @param string imei
     * @param datetime start_date
     * @param datetime end_date
     * @return json|null
     */
    // public function saveEventForPeriode($imei, $start_date, $end_date){
    //     try{
    //         $apiService = new GeolocalisationService();
    //         $apiData= $apiService->getEventForPeriodeApi($imei, $start_date, $end_date);
            
    //         $filteredData = [];
    //         // Parcourir les données de l'API
    //         if(!empty($apiData)){
    //             foreach ($apiData as $event) {
    //                 if (in_array($event[1], self::ALLOWED_EVENT) && isset($event[10]['rfid'])) {
    //                     $filteredData[] = $event;
    //                 }
    //             }
    //         }
            

    //         if (!empty($filteredData)) {
    //             foreach ($filteredData as $item) {
    //                 // Vérifiez si une entrée identique existe déjà dans la table Event
    //                 $existingEvent = Event::where('imei', trim($item[2]))
    //                 ->where('date', $item['4'])
    //                 ->where('type', trim($item['1']))
    //                 ->first();

    //                 // Si aucune entrée identique n'existe, insérez les données dans la table Event
    //                 if (!$existingEvent) {
                    
    //                     if(isset($item[10]['rfid']) && 
    //                        $item[10]['rfid'] != "0000000000" && 
    //                        trim($item[10]['rfid']) != trim("u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0")){
    //                         Event::create([
    //                             'imei' => $item[2],
    //                             'chauffeur' => $item[10]['rfid'],
    //                             'vehicule' => $item[3],
    //                             'type' => trim($item[1]),
    //                             'date' => $item[4],
    //                             'odometer' => $item[10]['odo'] ?? 0,
    //                             'vitesse' => $item[9] ?? 0,
    //                             'latitude' => $item[5] ?? 0,
    //                             'longitude' => $item[6] ?? 0,
    //                             'duree' => 1,
    //                             'description' => trim($item[1]) ?? 0,
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }
    //     }catch (Exception $e) {
    //         // Logger l'erreur pour la traçabilité
    //         Log::error('Erreur lors de l\'insertion des évenements : ' . $e->getMessage());
            
    //         return null;
    //     }
    // }
    public function saveEventForPeriode($imei, $start_date, $end_date)
    {
        try {
            $apiService = new GeolocalisationService();
            $apiData = $apiService->getEventForPeriodeApi($imei, $start_date, $end_date);

            $filteredData = [];
            // Parcourir les données de l'API
            if (!empty($apiData)) {
                foreach ($apiData as $event) {
                    if (in_array(trim($event[1]), self::ALLOWED_EVENT) && isset($event[10]['rfid'])) {
                        // Filtre supplémentaire pour s'assurer que les données sont valides
                        // if ($event[10]['rfid'] != "0000000000" && trim($event[10]['rfid']) != trim("u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0u00f0")) {
                            // Ajouter les données dans le tableau `$filteredData` sans les insérer
                            $date = (new \DateTime($event[4]))->modify('+3 hours');
                            $filteredData[] = [
                                'imei' => trim($event[2]),
                                'chauffeur' => $event[10]['rfid'],
                                'vehicule' => $event[3],
                                'type' => trim($event[1]),
                                'date' => $date,
                                'odometer' => $event[10]['odo'] ?? 0,
                                'vitesse' => $event[9] ?? 0,
                                'latitude' => $event[5] ?? 0,
                                'longitude' => $event[6] ?? 0,
                                'duree' => 1,
                                'description' => trim($event[1]) ?? '',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        // }
                    }
                }
            }
            return $filteredData; // Retourne le tableau d'événements au lieu de les enregistrer
        } catch (Exception $e) {
            // Logger l'erreur pour la traçabilité
            Log::error('Erreur lors du traitement des événements : ' . $e->getMessage());
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }


    /**
     * Antonio
     * Enregistrer les évenements de dernier mois.
     *
     */
    // public function proccessEventForPeriod(){
    //     $trucks = Vehicule::get();
        
    //     $start_date = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
    //     $end_date = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
        
    //     foreach($trucks as $truck){
    //         self::saveEventForPeriode($truck->imei, $start_date, $end_date);
    //     }
    // }
    // public function proccessEventForPeriod($console, $start_date, $end_date)
    // {
    //     // Récupération des camions
    //     $trucks = Vehicule::get();
        
    //     // Définition des dates de la période
    //     // $start_date = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
    //     // $end_date = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
        
    //     // Affichage de la barre de progression
    //     $console->withProgressBar($trucks, function($truck) use ($start_date, $end_date) {
    //         // Traitement de chaque camion
    //         self::saveEventForPeriode($truck->imei, $start_date, $end_date);
    //     });

    //     // Message final lorsque le traitement est terminé
    //     $console->info('Tous les événements pour la période ont été traités.');
    // }

    public function proccessEventForPeriod($console, $start_date, $end_date, $id_planning)
    {
        // Récupération des camions
        $trucks = Vehicule::where('id_planning', $id_planning)->get();

        // Tableau pour stocker tous les événements
        $allEvents = [];
        
        // Affichage de la barre de progression
        $console->withProgressBar($trucks, function($truck) use ($start_date, $end_date, &$allEvents) {
            // Traitement de chaque camion
            $events = self::saveEventForPeriode($truck->imei, $start_date, $end_date);
            
            // Ajouter les événements du camion actuel au tableau global
            $allEvents = array_merge($allEvents, $events);
        });
        // dd($allEvents);
        // Si des événements ont été collectés, les insérer en batch
        if (!empty($allEvents)) {
            // Event::insert($allEvents);
            $chunkSize = 100;

            foreach (array_chunk($allEvents, $chunkSize) as $chunk) {
                $eventsToInsert = []; // Tableau pour stocker les événements à insérer

                foreach ($chunk as $event) {
                    // Vérifiez si l'événement existe déjà
                    $exists = DB::table('event')
                        ->where('imei', $event['imei'])
                        ->where('chauffeur', $event['chauffeur'])
                        ->where('vehicule', $event['vehicule']) 
                        ->where('type', trim($event['type']))  
                        ->where('vitesse', $event['vitesse']) 
                        ->where('latitude', number_format((float)$event['latitude'], 2, '.', ''))
                        ->where('longitude', number_format((float)$event['longitude'], 2, '.', ''))
                        ->where('odometer', number_format((float)$event['odometer'], 2, '.', '')) 
                        ->where('description', trim($event['description']))     
                        ->where('duree', $event['duree'])     
                        ->where('date', $event['date'])     
                        ->exists();
                    // Si l'événement n'existe pas, ajoutez-le au tableau des insertions
                    if (!$exists) {
                        $eventsToInsert[] = $event;
                    }
                }

                // Insérez uniquement les événements qui n'existent pas déjà
                if (!empty($eventsToInsert)) {
                    Event::insert($eventsToInsert);
                }
            }
            $console->info('Tous les événements pour la période ont été insérés.');
        } else {
            $console->info('Aucun événement à insérer.');
        }

        // Message final lorsque le traitement est terminé
        $console->info('Tous les événements pour la période ont été traités.');
    }


}