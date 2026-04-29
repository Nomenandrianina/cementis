<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\CircuitLeg;
use App\Models\Checkpoint;
use App\Models\Zone;
use App\Models\Rvehicule;
use Illuminate\Http\Request;

class CircuitController extends Controller
{
    public function index()
    {
        $circuits = Circuit::withCount('legs')->with('vehicles')->orderBy('name')->get();
        return view('circuits.index', compact('circuits'));
    }

    public function create()
    {
        $zones       = Zone::where('active', true)->orderBy('name')->get();
        $checkpoints = Checkpoint::where('active', true)->orderBy('name')->get();
        $vehicles    = Rvehicule::orderBy('name')->get();
        return view('circuits.create', compact('zones', 'checkpoints', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:circuits',
            'description' => 'nullable|string',
        ]);

        $circuit = Circuit::create($data);
        return redirect()->route('circuits.edit', $circuit)->with('success', 'Circuit créé avec succès.');
    }

    public function edit(Circuit $circuit)
    {
        $circuit->load(['legs.circuit', 'vehicles', 'objectives']);
        $zones       = Zone::where('active', true)->orderBy('name')->get();
        $checkpoints = Checkpoint::where('active', true)->orderBy('name')->get();
        $vehicles    = Rvehicule::orderBy('name')->get();
        return view('circuits.edit', compact('circuit', 'zones', 'checkpoints', 'vehicles'));
    }

    public function update(Request $request, Circuit $circuit)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:circuits,code,' . $circuit->id,
            'description' => 'nullable|string',
            'active'      => 'boolean',
        ]);

        $circuit->update($data);
        return back()->with('success', 'Circuit mis à jour.');
    }

    public function destroy(Circuit $circuit)
    {
        $circuit->delete();
        return redirect()->route('circuits.index')->with('success', 'Circuit supprimé.');
    }

    // ── Étapes (legs) ──────────────────────────────────────────────────────────

    public function storeLeg(Request $request, Circuit $circuit)
    {
        $data = $request->validate([
            'label'          => 'required|string|max:255',
            'event_type'     => 'required|in:enter_zone,leave_zone,pass_checkpoint',
            'reference_type' => 'required|in:zone,checkpoint',
            'reference_id'   => 'required|integer',
            'optional'       => 'boolean',
            'direction'      => 'in:inbound,outbound,any',
        ]);

        $maxOrder = $circuit->legs()->max('order') ?? 0;
        $data['order'] = $maxOrder + 1;

        $circuit->legs()->create($data);
        return back()->with('success', 'Étape ajoutée.');
    }

    public function updateLeg(Request $request, Circuit $circuit, CircuitLeg $leg)
    {
        $data = $request->validate([
            'label'      => 'required|string|max:255',
            'event_type' => 'required|in:enter_zone,leave_zone,pass_checkpoint',
            'reference_type' => 'required|in:zone,checkpoint',
            'reference_id'   => 'required|integer',
            'optional'       => 'boolean',
            'direction'      => 'in:inbound,outbound,any',
        ]);

        $leg->update($data);
        return back()->with('success', 'Étape mise à jour.');
    }

    public function destroyLeg(Circuit $circuit, CircuitLeg $leg)
    {
        $leg->delete();
        // Réordonner
        $circuit->legs()->orderBy('order')->each(function ($l, $idx) {
            $l->update(['order' => $idx + 1]);
        });
        return back()->with('success', 'Étape supprimée.');
    }

    public function reorderLegs(Request $request, Circuit $circuit)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:circuit_legs,id',
        ]);

        $legs = [];
        foreach ($request->order as $position => $legId) {
            CircuitLeg::where('id', $legId)
                    ->where('circuit_id', $circuit->id)
                    ->update(['order' => $position + 1]);

            $legs[] = ['id' => (int) $legId, 'order' => $position + 1];
        }

        return response()->json([
            'success' => true,
            'legs'    => $legs,   // retourné pour mise à jour des badges côté JS
        ]);
    }

    // ── Véhicules affectés ─────────────────────────────────────────────────────

    public function assignVehicle(Request $request, Circuit $circuit)
    {
        $data = $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'assigned_from'  => 'required|date',
            'assigned_until' => 'nullable|date|after:assigned_from',
        ]);

        $circuit->vehicles()->syncWithoutDetaching([
            $data['vehicle_id'] => [
                'assigned_from'  => $data['assigned_from'],
                'assigned_until' => $data['assigned_until'] ?? null,
            ]
        ]);

        return back()->with('success', 'Véhicule affecté au circuit.');
    }

    public function removeVehicle(Circuit $circuit, Rvehicule $vehicle)
    {
        $circuit->vehicles()->detach($vehicle->id);
        return back()->with('success', 'Véhicule retiré du circuit.');
    }
}