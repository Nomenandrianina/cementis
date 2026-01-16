<?php

namespace App\Console\Commands\Scoring;

use Illuminate\Console\Command;
use App\Services\CalendarService;
use App\Services\ScoreDriverService;
use App\Models\Importcalendar;
use App\Models\ScoreDriver;
use Illuminate\Support\Facades\DB;

class GenerateScoreDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:score-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les données de scoring pour le planning sélectionné';

    protected $scoreDriverService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ScoreDriverService $scoreDriverService)
    {
        parent::__construct();
        $this->scoreDriverService = $scoreDriverService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting the process generate score driver...');
        
        $selectedPlanning = DB::table('import_calendar')->latest('id')->value('id');
        $existingScoring = ScoreDriver::where('id_planning', $selectedPlanning)->exists();
        $import_calendar = Importcalendar::all();

        if ($existingScoring) {
            $scoring = ScoreDriver::where('id_planning', $selectedPlanning)->orderBy('score', 'desc')->get();
        } else {
            $data = [];
            $createScoring = [];
            $results = $this->scoreDriverService->generate_score_driver($selectedPlanning);
            
            if ($results) {
                foreach ($results as $result) {
                    $badge = $result->badge;
                    $transporteur = $this->scoreDriverService->get_vehicule_transporteur($selectedPlanning, $result->badge);
                    
                    $score = $result->score;

                        $createScoring[] = [
                            'badge' => $badge,
                            'transporteur' => $transporteur,
                            'id_planning' => $selectedPlanning,
                            'observation' => "",
                            'score' => ($score !== null) ? $score : 0
                        ];
                }
            }
            // Sauvegarder le scoring
            $this->save_score_driver($createScoring);
        }

        $this->info('Process generate score driver completed!');
    }

    public function save_score_driver($data){
        foreach($data as $item){
            ScoreDriver::create([
                'badge' => $item['badge'],
                'transporteur' => $item['transporteur'],
                'id_planning' => $item['id_planning'],
                'observation' => $item['observation'],
                'score' => $item['score'],
            ]);
        }
    }
}
