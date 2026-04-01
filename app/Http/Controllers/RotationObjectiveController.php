<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\Rotation;
use App\Models\RotationObjective;
use App\Services\GpsApiService;
use App\Services\ReportService;
use App\Services\RotationCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;

// ============================================================
// RotationObjectiveController
// ============================================================
class RotationObjectiveController extends Controller
{
    public function index(Circuit $circuit)
    {
        $objectives = $circuit->objectives()->orderByDesc('effective_from')->get();
        return view('rotations.objectives', compact('circuit', 'objectives'));
    }
 
    public function store(Request $request, Circuit $circuit)
    {
        $data = $request->validate([
            'target_rotations_per_month' => 'nullable|integer|min:1',
            'target_duration_minutes'    => 'nullable|integer|min:1',
            'effective_from'             => 'required|date',
            'effective_until'            => 'nullable|date|after:effective_from',
            'notes'                      => 'nullable|string',
            'leg_objectives'             => 'nullable|array',
            'leg_objectives.*'           => 'nullable|integer|min:1',
        ]);
 
        $circuit->objectives()->create($data);
        return back()->with('success', 'Objectif créé.');
    }
 
    public function update(Request $request, Circuit $circuit, RotationObjective $objective)
    {
        $data = $request->validate([
            'target_rotations_per_month' => 'nullable|integer|min:1',
            'target_duration_minutes'    => 'nullable|integer|min:1',
            'effective_from'             => 'required|date',
            'effective_until'            => 'nullable|date|after:effective_from',
            'notes'                      => 'nullable|string',
            'leg_objectives'             => 'nullable|array',
            'leg_objectives.*'           => 'nullable|integer|min:1',
        ]);
 
        $objective->update($data);
        return back()->with('success', 'Objectif mis à jour.');
    }
 
    public function destroy(Circuit $circuit, RotationObjective $objective)
    {
        $objective->delete();
        return back()->with('success', 'Objectif supprimé.');
    }
}
