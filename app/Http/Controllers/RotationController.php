<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\Rotation;
use App\Models\RotationObjective;
use App\Models\Rvehicule;
use App\Services\GpsApiService;
use App\Services\ReportService;
use App\Services\RotationCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RotationController extends Controller
{
     public function __construct(
        private readonly RotationCalculatorService $calculator,
        private readonly GpsApiService $gpsApi
    ) {}
 
    public function index(Request $request)
    {
        $circuits = Circuit::where('active', true)->orderBy('name')->get();
        $vehicles = Rvehicule::orderBy('name')->get();
 
        $query = Rotation::with(['rvehicule', 'circuit'])->latest('started_at');
 
        if ($request->circuit_id) {
            $query->where('circuit_id', $request->circuit_id);
        }
        if ($request->vehicle_id) {
            $query->where('rvehicule_id', $request->vehicle_id);
        }
        if ($request->month) {
            $query->where('counted_month', $request->month);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
 
        $rotations = $query->paginate(25)->withQueryString();
 
        return view('rotations.index', compact('rotations', 'circuits', 'vehicles'));
    }
 
    public function show(Rotation $rotation)
    {
        $rotation->load(['rvehicule', 'circuit.legs', 'rotationLegs.circuitLeg']);
        $objective = $rotation->circuit->currentObjective();
        return view('rotations.show', compact('rotation', 'objective'));
    }
 
    /**
     * Lance le calcul des rotations pour un circuit/véhicule/mois donné.
     */
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'circuit_id' => 'required|exists:circuits,id',
            'vehicle_id' => 'nullable|exists:r_vehicules,id',
            'year'       => 'required|integer|min:2020|max:2099',
            'month'      => 'required|integer|min:1|max:12',
        ]);
 
        $circuit  = Circuit::findOrFail($data['circuit_id']);
        $vehicles = $data['vehicle_id']
            ? [Rvehicule::findOrFail($data['vehicle_id'])]
            : $circuit->vehicles->all();
 
        if (empty($vehicles)) {
            return back()->with('error', 'Aucun véhicule affecté à ce circuit.');
        }
 
        $totalCount = 0;
        $errors     = [];
 
        foreach ($vehicles as $vehicle) {
            $result      = $this->calculator->calculateForMonth($vehicle, $circuit, $data['year'], $data['month']);
            $totalCount += $result['count'];
            $errors      = array_merge($errors, $result['errors']);
        }
 
        $msg = "{$totalCount} rotation(s) calculée(s) pour " . count($vehicles) . " véhicule(s).";
        if (!empty($errors)) {
            $msg .= ' Avertissements : ' . implode(' | ', $errors);
        }
 
        return back()->with('success', $msg);
    }
 
    public function destroy(Rotation $rotation)
    {
        $rotation->rotationLegs()->delete();
        $rotation->delete();
        return back()->with('success', 'Rotation supprimée.');
    }
}
