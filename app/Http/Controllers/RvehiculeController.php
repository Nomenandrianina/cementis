<?php

namespace App\Http\Controllers;

use App\Models\Rvehicule;
use App\Services\GpsApiService;
use Illuminate\Http\Request;

class RvehiculeController extends Controller
{
    public function __construct(private readonly GpsApiService $gpsApi) {}

    public function index()
    {
        $vehicles = Rvehicule::withCount('rotations')->orderBy('name')->get();
        return view('r_vehicules.index', compact('vehicles'));
    }

    public function sync()
    {
        $objects = $this->gpsApi->getObjects();
        $synced  = 0;

        foreach ($objects as $imei => $data) {
            Rvehicule::updateOrCreate(
                ['imei' => $imei],
                [
                    'name'           => $data['name'] ?? $imei,
                    'plate_number'   => $data['plate_number'] ?? null,
                    'model'          => $data['model'] ?? null,
                ]
            );
            $synced++;
        }

        return back()->with('success', "{$synced} véhicules synchronisés depuis l'API GPS.");
    }

    public function toggle(Rvehicule $vehicle)
    {
        $vehicle->update(['active' => !$vehicle->active]);
        return back()->with('success', "Véhicule {$vehicle->name} " . ($vehicle->active ? 'activé' : 'désactivé') . '.');
    }
}