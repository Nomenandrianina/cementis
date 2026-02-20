<?php

namespace App\Console\Commands\Infraction;

use Illuminate\Console\Command;
use App\Services\InfractionService;
use App\Services\MovementService;
use App\Services\TruckService;
use Illuminate\Support\Facades\DB;

class CheckInfraction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:infraction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $lastmonth = DB::table('import_calendar')->latest('id')->first();

        $startDate = new \DateTime($lastmonth->date_debut);

 
        $endDate = new \DateTime($lastmonth->date_fin);

        // $infractionService = new InfractionService();
        // $infractionService->saveInfraction($this, $startDate, $endDate);
        $movementService = new MovementService();
        $rep = $movementService->getMaxStopInJourney("865135060353990", "2025-04-02 18:08:27", "2025-04-03 18:08:27");
        dd($rep);
        $this->info('Process completed!');
    }
}
