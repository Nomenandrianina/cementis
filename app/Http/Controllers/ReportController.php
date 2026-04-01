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
// ReportController
// ============================================================
class ReportController extends Controller
{
    public function __construct(private readonly ReportService $report) {}
 
    public function index()
    {
        $circuits = Circuit::where('active', true)->orderBy('name')->get();
        return view('reports.index', compact('circuits'));
    }
 
    public function monthly(Request $request)
    {
        $data = $request->validate([
            'circuit_id' => 'required|exists:circuits,id',
            'year'       => 'required|integer|min:2020|max:2099',
            'month'      => 'required|integer|min:1|max:12',
        ]);
 
        $circuit = Circuit::with(['legs', 'vehicles'])->findOrFail($data['circuit_id']);
        $report  = $this->report->monthlyReport($circuit, $data['year'], $data['month']);
 
        return view('reports.monthly', compact('report', 'circuit'));
    }
 
    public function exportCsv(Request $request)
    {
        $data = $request->validate([
            'circuit_id' => 'required|exists:circuits,id',
            'year'       => 'required|integer',
            'month'      => 'required|integer',
        ]);
 
        $circuit = Circuit::with(['legs', 'vehicles'])->findOrFail($data['circuit_id']);
        $report  = $this->report->monthlyReport($circuit, $data['year'], $data['month']);
 
        $filename = "rotations_{$circuit->code}_{$data['year']}{$data['month']}.csv";
 
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
 
        $callback = function () use ($report) {
            $fp = fopen('php://output', 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
 
            fputcsv($fp, ['Véhicule', 'Immatriculation', 'N° Rotation', 'Début', 'Fin', 'Durée (min)', 'Objectif (min)', 'Écart (min)', 'Statut']);
 
            foreach ($report['vehicle_reports'] as $vr) {
                foreach ($vr['rotations'] as $idx => $rot) {
                    $status = match (true) {
                        $rot['vs_target'] === null  => '—',
                        $rot['vs_target'] <= 0      => '✓ Dans objectif',
                        default                     => '✗ Dépassé',
                    };
                    fputcsv($fp, [
                        $vr['vehicle']->name,
                        $vr['vehicle']->plate_number ?? '—',
                        $idx + 1,
                        $rot['started_at'],
                        $rot['completed_at'],
                        $rot['duration_minutes'],
                        $rot['target_duration'] ?? '—',
                        $rot['vs_target'] ?? '—',
                        $status,
                    ]);
                }
            }
            fclose($fp);
        };
 
        return response()->stream($callback, 200, $headers);
    }
}