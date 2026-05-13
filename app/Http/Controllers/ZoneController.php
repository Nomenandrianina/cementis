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

    // public function index()
    // {
    //     $zones = Zone::withTrashed()->orderBy('name')->get();
    //     return view('zones.index', compact('zones'));
    // }
    // public function index()
    // {
    //     // Zones racines avec leurs enfants directs
    //     $rootZones   = Zone::withTrashed()
    //                        ->with(['children' => fn($q) => $q->withTrashed()->orderBy('name')])
    //                        ->whereNull('parent_id')
    //                        ->orderBy('name')
    //                        ->get();

    //     // Pour le sélecteur "zone parente" du formulaire
    //     $parentZones = Zone::where('active', true)
    //                        ->orderBy('name')
    //                        ->get(['id', 'name', 'parent_id']);
        
    //     $totalCount = Zone::count();

    //     return view('zones.index', compact('rootZones', 'parentZones', 'totalCount'));
    // }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $filter = $request->input('filter');

        $query = Zone::whereNull('parent_id')
            ->with('children');

        // Filtre par type
        if ($filter === 'roots') {
            // déjà filtré par whereNull
        } elseif (in_array($filter, ['obligatoire', 'optionnel'])) {
            $query->where('option', $filter);
        }

        // Recherche : zones racines dont le nom correspond,
        // OU racines qui ont un enfant dont le nom correspond
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('children', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $rootZones  = $query->orderBy('name')->paginate(25)->withQueryString();
        $totalCount = Zone::count();
        $parentZones = Zone::orderBy('name')->get();

        return view('zones.index', compact('rootZones', 'totalCount', 'parentZones', 'search'));
    }

    public function children(Zone $zone)
    {
        $children = $zone->children()->with('children')->get();
    
        return response()->json($children);
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
            'option'      => 'in:obligatoire,optionnel',
            'parent_id'   => 'nullable|exists:zones,id',
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
            'option' => 'in:obligatoire,optionnel',
            'parent_id' => [
                'nullable',
                'exists:zones,id',
                function ($attr, $value, $fail) use ($zone) {
                    if ((int) $value === $zone->id) {
                        $fail('Une zone ne peut pas être son propre parent.');
                    }
                    // Empêcher les cycles : le parent ne doit pas être un enfant
                    if ($value && Zone::where('id', $value)
                                      ->where('parent_id', $zone->id)
                                      ->exists()) {
                        $fail('Impossible : cette zone est déjà parente de la zone sélectionnée.');
                    }
                },
            ],
            'role'   => 'nullable|in:start,end,waypoint',
            'color'  => 'nullable|string',
            'active' => 'boolean',
        ]);

        $zone->update($data);
        return back()->with('success', 'Zone mise à jour.');
    }

    public function destroy(Zone $zone)
    {
        $zone->children()->update(['parent_id' => null]);
        $zone->delete();
        return back()->with('success', 'Zone supprimée.');
    }

    public function apiLocalZones()
    {
        $zones = Zone::where('active', true)
                     ->with('parent:id,name')
                     ->orderBy('name')
                     ->get(['id', 'name', 'parent_id'])
                     ->map(fn($z) => [
                         'id'          => $z->id,
                         'name'        => $z->name,
                         'parent_id'   => $z->parent_id,
                         'parent_name' => $z->parent?->name,
                         'full_path'   => $z->getFullPath(),
                     ]);

        return response()->json(['data' => $zones]);
    }
}