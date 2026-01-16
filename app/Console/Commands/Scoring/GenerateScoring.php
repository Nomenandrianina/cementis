<?php

namespace App\Console\Commands\Scoring;

use Illuminate\Console\Command;
use App\Services\CalendarService;
use App\Models\Importcalendar;
use App\Models\Scoring;
use Illuminate\Support\Facades\DB;

class GenerateScoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scoring:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les données de scoring pour le planning sélectionné';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting the process...');
        
        $selectedPlanning = DB::table('import_calendar')->latest('id')->value('id');
        $existingScoring = Scoring::where('id_planning', $selectedPlanning)->exists();
        $import_calendar = Importcalendar::all();

        if ($existingScoring) {
            $scoring = Scoring::where('id_planning', $selectedPlanning)->orderBy('point', 'desc')->get();
        } else {
            $data = [];
            $createScoring = [];
            $results = scoring($selectedPlanning); // Appel de la fonction scoring
            
            if ($results) {
                $comment = '';
                foreach ($results as $result) {
                    $badge_calendar = $result->badge_calendar;
                    $badge_rfid = get_driver_by_rfid($result->rfid_conducteur);
                    $rfid_infraction = $result->rfid_conducteur;
                    $rfid_calendar = $result->rfid_calendar;
                    $camion = $result->camion;
                    $imei = $result->imei;
                    $transporteur_id = get_transporteur_by_imei($selectedPlanning, $result->imei, $result->camion);
                    $total_point = $result->total_point;

                        $createScoring[] = [
                            'id_planning' => $selectedPlanning,
                            'transporteur_id' => $transporteur_id,
                            'badge_rfid' => $badge_rfid,
                            'badge_calendar' => $badge_calendar,
                            'rfid_chauffeur' => $rfid_calendar,
                            'rfid_infraction' => $rfid_infraction,
                            'imei' => $imei,
                            'camion' => $camion,
                            'comment' => $comment,
                            'distance' => 0,
                            'point' => ($total_point !== null) ? $total_point : 0
                        ];
                        $comment = '';
                }
            }
            // Sauvegarder le scoring
            $this->saveScoring($createScoring);
        }

        $this->info('Process completed!');
    }

    public function saveScoring($data){
        foreach($data as $item){
            Scoring::create([
                'id_planning' => $item['id_planning'],
                // 'driver_id' => $item['driver_id'],
                'transporteur_id' => $item['transporteur_id'],
                'camion' => $item['camion'],
                'badge_rfid' => $item['badge_rfid'],
                'badge_calendar' => $item['badge_calendar'],
                'rfid_chauffeur' => $item['rfid_chauffeur'],
                'rfid_infraction' => $item['rfid_infraction'],
                'imei' => $item['imei'],
                'comment' => $item['comment'],
                'distance' => $item['distance'],
                'point' => $item['point'],
            ]);
        }
    }
}
