<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Zone;
use App\Services\GpsApiService;
use Illuminate\Http\Request;

// ============================================================
// ZoneController
// ============================================================
class ZoneController extends Controller
{
    public function __construct(private readonly GpsApiService $gpsApi) {}

    public function index()
    {
        $zones = Zone::withTrashed()->orderBy('name')->get();
        return view('zones.index', compact('zones'));
    }

    public function sync()
    {
        $apiZones = $this->gpsApi->getZones();
        $synced   = 0;

        foreach ($apiZones as $zoneId => $data) {
            Zone::updateOrCreate(
                ['gps_zone_id' => $zoneId],
                [
                    'name'     => $data['name'] ?? "Zone {$zoneId}",
                    'color'    => $data['color'] ?? null,
                    'vertices' => $data['vertices'] ?? null,
                    'active'   => true,
                ]
            );
            $synced++;
        }

        return back()->with('success', "{$synced} zones synchronisées.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'in:zone,origin,destination',
            'role'        => 'nullable|in:start,end,waypoint',
            'color'       => 'nullable|string',
            'gps_zone_id' => 'nullable|string',
        ]);

        Zone::create($data);
        return back()->with('success', 'Zone créée.');
    }

    public function update(Request $request, Zone $zone)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'in:zone,origin,destination',
            'role'   => 'nullable|in:start,end,waypoint',
            'color'  => 'nullable|string',
            'active' => 'boolean',
        ]);

        $zone->update($data);
        return back()->with('success', 'Zone mise à jour.');
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return back()->with('success', 'Zone supprimée.');
    }
}