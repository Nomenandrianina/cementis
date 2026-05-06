<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Zone;
use App\Services\GpsApiService;
use Illuminate\Http\Request;

class CheckpointController extends Controller
{
    public function __construct(private readonly GpsApiService $gpsApi) {}

    public function index(Request $request)
    {
        $query = Checkpoint::orderBy('name', 'asc');

        // Recherche simple intégrée
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $checkpoints = $query->paginate(25)->withQueryString();

        return view('checkpoints.index', compact('checkpoints'));
    }

    public function sync()
    {
        set_time_limit(120);

        $markers = $this->gpsApi->getMarkers();
        $synced  = 0;

        foreach ($markers as $markerId => $data) {
            Checkpoint::updateOrCreate(
                ['gps_marker_id' => $markerId],
                [
                    'name'        => $data['name'] ?? "Checkpoint {$markerId}",
                    'description' => $data['desc'] ?? null,
                    'lat'         => $data['lat'],
                    'lng'         => $data['lng'],
                    'radius'      => $data['radius'] ?? 0.1,
                    'icon'        => $data['icon'] ?? null,
                    'active'      => true,
                ]
            );
            $synced++;
        }

        return back()->with('success', "{$synced} checkpoints synchronisés.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:obligatoire,optionnel',
            'description' => 'nullable|string',
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
            'radius'      => 'required|numeric|min:0.01',
        ]);

        Checkpoint::create($data);
        return back()->with('success', 'Checkpoint créé.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:obligatoire,optionnel',
            'description' => 'nullable|string',
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
            'radius'      => 'required|numeric|min:0.01',
            'active'      => 'boolean',
        ]);
        
        $checkpoint = Checkpoint::findOrFail($id);
        $checkpoint->update($data);
        return back()->with('success', 'Checkpoint mis à jour.');
    }

    public function destroy(Checkpoint $checkpoint)
    {
        $checkpoint->delete();
        return back()->with('success', 'Checkpoint supprimé.');
    }
}


