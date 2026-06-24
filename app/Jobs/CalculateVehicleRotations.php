<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\RotationCalculatorService;
use App\Models\Rvehicule;
use App\Models\Circuit;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// app/Jobs/CalculateVehicleRotations.php
class CalculateVehicleRotations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $vehicleId,
        public int $circuitId,
        public int $year,
        public int $month,
    ) {}

    public function handle(RotationCalculatorService $calculator): void
    {
        $vehicle = Rvehicule::findOrFail($this->vehicleId);
        $circuit = Circuit::findOrFail($this->circuitId);
        $calculator->calculateForMonth($vehicle, $circuit, $this->year, $this->month);
    }
}