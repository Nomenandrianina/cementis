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

    // public function exportCsv(Request $request)
    // {
    //     $data = $request->validate([
    //         'circuit_id' => 'required|exists:circuits,id',
    //         'year'       => 'required|integer',
    //         'month'      => 'required|integer',
    //     ]);

    //     $circuit = Circuit::with(['legs', 'vehicles'])->findOrFail($data['circuit_id']);
    //     $report  = $this->report->monthlyReport($circuit, $data['year'], $data['month']);

    //     $filename = "rotations_{$circuit->code}_{$data['year']}{$data['month']}.csv";

    //     $headers = [
    //         'Content-Type'        => 'text/csv; charset=UTF-8',
    //         'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    //     ];

    //     $callback = function () use ($report) {
    //         $fp = fopen('php://output', 'w');
    //         fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

    //         fputcsv($fp, ['Véhicule', 'Immatriculation', 'N° Rotation', 'Début', 'Fin', 'Durée (min)', 'Objectif (min)', 'Écart (min)', 'Statut']);

    //         foreach ($report['vehicle_reports'] as $vr) {
    //             foreach ($vr['rotations'] as $idx => $rot) {
    //                 $status = match (true) {
    //                     $rot['vs_target'] === null  => '—',
    //                     $rot['vs_target'] <= 0      => '✓ Dans objectif',
    //                     default                     => '✗ Dépassé',
    //                 };
    //                 fputcsv($fp, [
    //                     $vr['vehicle']->name,
    //                     $vr['vehicle']->plate_number ?? '—',
    //                     $idx + 1,
    //                     $rot['started_at'],
    //                     $rot['completed_at'],
    //                     $rot['duration_minutes'],
    //                     $rot['target_duration'] ?? '—',
    //                     $rot['vs_target'] ?? '—',
    //                     $status,
    //                 ]);
    //             }
    //         }
    //         fclose($fp);
    //     };

    //     return response()->stream($callback, 200, $headers);
    // }
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

        $callback = function () use ($report, $circuit) {
            $fp = fopen('php://output', 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // ── En-tête principale ───────────────────────────────────────────────
            fputcsv($fp, [
                'Véhicule', 'Immatriculation', 'N° Rotation',
                'Début (T1)', 'Fin (T5)', 'Durée (min)', 'Objectif (min)', 'Écart (min)', 'Statut',
            ]);

            foreach ($report['vehicle_reports'] as $vr) {
                foreach ($vr['rotations'] as $idx => $rot) {
                    $status = match (true) {
                        $rot['vs_target'] === null => '—',
                        $rot['vs_target'] <= 0     => '✓ Dans objectif',
                        default                    => '✗ Dépassé',
                    };

                    // Ligne rotation
                    fputcsv($fp, [
                        $vr['vehicle']->name,
                        $vr['vehicle']->plate_number ?? '—',
                        $idx + 1,
                        $rot['started_at'],
                        $rot['completed_at'],
                        $rot['duration_minutes'] ?? '—',
                        $rot['target_duration']  ?? '—',
                        $rot['vs_target']        ?? '—',
                        $status,
                    ]);

                    // ── Détail des blocs (zones + sous-zones + checkpoints) ──────
                    fputcsv($fp, [
                        '', '', '',
                        'Type', 'Étape', 'Entrée', 'Sortie', 'Durée (min)', 'Objectif (min)', 'Écart (min)',
                    ]);

                    foreach ($rot['blocks'] as $block) {
                        if ($block['type'] === 'checkpoint') {
                            fputcsv($fp, [
                                '', '', '',
                                'Checkpoint',
                                $block['label'],
                                $block['occurred_at'] ?? '—',
                                '—', '—', '—', '—',
                            ]);
                        } elseif ($block['type'] === 'zone') {
                            fputcsv($fp, [
                                '', '', '',
                                'Zone',
                                $block['label'],
                                $block['enter_at']   ?? '—',
                                $block['leave_at']   ?? '—',
                                $block['actual_min'] ?? '—',
                                $block['target_min'] ?? '—',
                                $block['ecart']      ?? '—',
                            ]);

                            // Sous-zones indentées
                            foreach ($block['children'] as $child) {
                                fputcsv($fp, [
                                    '', '', '',
                                    '  └ Sous-zone',
                                    '    ' . $child['label'],
                                    $child['enter_at']   ?? '—',
                                    $child['leave_at']   ?? '—',
                                    $child['actual_min'] ?? '—',
                                    $child['target_min'] ?? '—',
                                    $child['ecart']      ?? '—',
                                ]);
                            }
                        }
                    }

                    // Séparateur entre rotations
                    fputcsv($fp, []);
                }

                // Séparateur entre véhicules
                fputcsv($fp, []);
            }

            fclose($fp);
        };

        return response()->stream($callback, 200, $headers);
    }

    // public function exportExcel(Request $request)
    // {
    //     $data = $request->validate([
    //         'circuit_id' => 'required|exists:circuits,id',
    //         'year'       => 'required|integer',
    //         'month'      => 'required|integer',
    //     ]);

    //     $circuit = Circuit::with(['legs', 'vehicles'])->findOrFail($data['circuit_id']);
    //     $report  = $this->report->monthlyReport($circuit, $data['year'], $data['month']);

    //     $filename = "rotations_{$circuit->code}_{$data['year']}{$data['month']}.xlsx";

    //     // ── Spreadsheet ──────────────────────────────────────────────────────────
    //     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    //     // ── Feuille 1 : Résumé ───────────────────────────────────────────────────
    //     $summary = $spreadsheet->getActiveSheet();
    //     $summary->setTitle('Résumé');

    //     $this->buildSummarySheet($summary, $report, $circuit);

    //     // ── Feuille par véhicule ─────────────────────────────────────────────────
    //     foreach ($report['vehicle_reports'] as $vr) {
    //         $sheet = $spreadsheet->createSheet();
    //         $sheetName = substr(preg_replace('/[^A-Za-z0-9\-_]/', '_', $vr['vehicle']->name), 0, 31);
    //         $sheet->setTitle($sheetName);
    //         $this->buildVehicleSheet($sheet, $vr, $report);
    //     }

    //     // ── Export ───────────────────────────────────────────────────────────────
    //     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    //     $temp   = tempnam(sys_get_temp_dir(), 'rotation_');
    //     $writer->save($temp);

    //     return response()->download($temp, $filename, [
    //         'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    //     ])->deleteFileAfterSend(true);
    // }

    // ── Feuille résumé ────────────────────────────────────────────────────────────

    // private function buildSummarySheet(
    //     \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
    //     array $report,
    //     $circuit
    // ): void {
    //     $bold    = ['font' => ['bold' => true]];
    //     $white   = ['font' => ['color' => ['rgb' => 'FFFFFF']]];
    //     $center  = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

    //     // Titre
    //     $sheet->setCellValue('A1', "Rapport mensuel – {$circuit->name}");
    //     $sheet->setCellValue('A2', $report['month_label'] ?? '');
    //     $sheet->mergeCells('A1:I1');
    //     $sheet->mergeCells('A2:I2');
    //     $sheet->getStyle('A1')->applyFromArray(array_merge($bold, [
    //         'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
    //         'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '8B1A1A']],
    //     ]));
    //     $sheet->getStyle('A2')->applyFromArray([
    //         'font' => ['color' => ['rgb' => '666666'], 'italic' => true],
    //         'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F5F0E8']],
    //     ]);

    //     // Stats globales
    //     $sheet->setCellValue('A4', 'Total rotations validées');
    //     $sheet->setCellValue('B4', $report['total_rotations']);
    //     $sheet->setCellValue('A5', 'Objectif total');
    //     $sheet->setCellValue('B5', $report['target_rotations'] ?? '—');
    //     $sheet->setCellValue('A6', 'Taux de réalisation');
    //     $sheet->setCellValue('B6', ($report['achievement_rate'] ?? '—') . '%');
    //     $sheet->getStyle('A4:A6')->applyFromArray($bold);

    //     // En-têtes tableau
    //     $row = 8;
    //     $headers = ['Véhicule', 'Immatriculation', 'Rotations', 'Objectif', 'Durée moy.', 'Objectif durée', 'Écart moy.', 'Annulées'];
    //     foreach ($headers as $col => $h) {
    //         $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
    //         $sheet->setCellValue($cell, $h);
    //     }
    //     $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
    //         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    //         'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '8B1A1A']],
    //     ]);

    //     $row++;
    //     foreach ($report['vehicle_reports'] as $vr) {
    //         $avgDur    = $vr['avg_duration'];
    //         $targetDur = $vr['target_duration'];
    //         $ecartMoy  = ($avgDur && $targetDur) ? $avgDur - $targetDur : null;

    //         $sheet->setCellValue("A{$row}", $vr['vehicle']->name);
    //         $sheet->setCellValue("B{$row}", $vr['vehicle']->plate_number ?? '—');
    //         $sheet->setCellValue("C{$row}", $vr['rotation_count']);
    //         $sheet->setCellValue("D{$row}", $vr['target_rotations'] ?? '—');
    //         $sheet->setCellValue("E{$row}", $avgDur    ? intdiv($avgDur, 60)    . 'h' . str_pad($avgDur % 60, 2, '0', STR_PAD_LEFT)    . 'm' : '—');
    //         $sheet->setCellValue("F{$row}", $targetDur ? intdiv($targetDur, 60) . 'h' . str_pad($targetDur % 60, 2, '0', STR_PAD_LEFT) . 'm' : '—');
    //         $sheet->setCellValue("G{$row}", $ecartMoy  !== null ? ($ecartMoy > 0 ? '+' : '') . $ecartMoy . 'min' : '—');
    //         $sheet->setCellValue("H{$row}", $vr['cancelled_count'] ?? 0);

    //         // Couleur selon objectif rotations
    //         if ($vr['target_rotations'] && $vr['rotation_count'] >= $vr['target_rotations']) {
    //             $sheet->getStyle("C{$row}")->applyFromArray([
    //                 'font' => ['color' => ['rgb' => '2D7A4A'], 'bold' => true],
    //             ]);
    //         } elseif ($vr['target_rotations']) {
    //             $sheet->getStyle("C{$row}")->applyFromArray([
    //                 'font' => ['color' => ['rgb' => 'C0272D'], 'bold' => true],
    //             ]);
    //         }

    //         $row++;
    //     }

    //     // Largeurs
    //     foreach (['A' => 30, 'B' => 18, 'C' => 12, 'D' => 12, 'E' => 14, 'F' => 14, 'G' => 12, 'H' => 10] as $col => $w) {
    //         $sheet->getColumnDimension($col)->setWidth($w);
    //     }
    // }

    // ── Feuille détail par véhicule ───────────────────────────────────────────────

    // private function buildVehicleSheet(
    //     \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
    //     array $vr,
    //     array $report
    // ): void {
    //     $row = 1;

    //     // Titre véhicule
    //     $sheet->setCellValue("A{$row}", $vr['vehicle']->name . ' — ' . ($vr['vehicle']->plate_number ?? ''));
    //     $sheet->mergeCells("A{$row}:J{$row}");
    //     $sheet->getStyle("A{$row}")->applyFromArray([
    //         'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
    //         'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '8B1A1A']],
    //     ]);
    //     $row += 2;

    //     foreach ($vr['rotations'] as $idx => $rot) {
    //         // ── En-tête rotation ──────────────────────────────────────────────────
    //         $sheet->setCellValue("A{$row}", "Rotation #" . ($idx + 1));
    //         $sheet->setCellValue("C{$row}", 'Début');
    //         $sheet->setCellValue("D{$row}", $rot['started_at']);
    //         $sheet->setCellValue("E{$row}", 'Fin');
    //         $sheet->setCellValue("F{$row}", $rot['completed_at']);
    //         $sheet->setCellValue("G{$row}", 'Durée');
    //         $sheet->setCellValue("H{$row}", $rot['duration_label']);
    //         $sheet->setCellValue("I{$row}", 'Objectif');
    //         $sheet->setCellValue("J{$row}", $rot['target_label']);

    //         $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
    //             'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    //             'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'A52020']],
    //         ]);
    //         $row++;

    //         // ── En-têtes colonnes détail ──────────────────────────────────────────
    //         $detailHeaders = ['', 'Type', 'Étape', 'Entrée', 'Sortie', 'Durée (min)', 'Objectif (min)', 'Écart (min)', '', ''];
    //         foreach ($detailHeaders as $c => $h) {
    //             $col  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1);
    //             $sheet->setCellValue("{$col}{$row}", $h);
    //         }
    //         $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
    //             'font' => ['bold' => true, 'color' => ['rgb' => '5C2B2B']],
    //             'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F5F0E8']],
    //         ]);
    //         $row++;

    //         // ── Blocs hiérarchiques ───────────────────────────────────────────────
    //         foreach ($rot['blocks'] as $block) {
    //             if ($block['type'] === 'checkpoint') {
    //                 $sheet->setCellValue("B{$row}", 'Checkpoint');
    //                 $sheet->setCellValue("C{$row}", $block['label']);
    //                 $sheet->setCellValue("D{$row}", $block['occurred_at'] ?? '—');
    //                 $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
    //                     'font' => ['color' => ['rgb' => '6B7280'], 'italic' => true],
    //                 ]);
    //                 $row++;

    //             } elseif ($block['type'] === 'zone') {
    //                 // Zone principale
    //                 $ecart = $block['ecart'];
    //                 $isDone = $block['is_done'];

    //                 $zoneFill = !$isDone       ? 'FFF'
    //                     : ($ecart === null      ? 'FDF8F0'
    //                     : ($ecart > 0           ? 'FEF2F2'
    //                                             : 'F0FDF4'));
    //                 $zoneFont = !$isDone       ? '9CA3AF'
    //                     : ($ecart === null      ? '8B1A1A'
    //                     : ($ecart > 0           ? 'C0272D'
    //                                         : '2D7A4A'));

    //                 $sheet->setCellValue("B{$row}", 'Zone');
    //                 $sheet->setCellValue("C{$row}", $block['label']);
    //                 $sheet->setCellValue("D{$row}", $block['enter_at']   ?? '—');
    //                 $sheet->setCellValue("E{$row}", $block['leave_at']   ?? '—');
    //                 $sheet->setCellValue("F{$row}", $block['actual_min'] ?? '—');
    //                 $sheet->setCellValue("G{$row}", $block['target_min'] ?? '—');
    //                 $sheet->setCellValue("H{$row}", $ecart !== null
    //                     ? ($ecart > 0 ? '+' : '') . $ecart
    //                     : '—');

    //                 $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
    //                     'font' => ['bold' => true, 'color' => ['rgb' => $zoneFont]],
    //                     'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $zoneFill]],
    //                 ]);

    //                 // Bordure gauche colorée (indicateur visuel zone)
    //                 $sheet->getStyle("B{$row}")->applyFromArray([
    //                     'borders' => [
    //                         'left' => [
    //                             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
    //                             'color'       => ['rgb' => $zoneFont],
    //                         ],
    //                     ],
    //                 ]);
    //                 $row++;

    //                 // Sous-zones
    //                 foreach ($block['children'] as $child) {
    //                     $cEcart = $child['ecart'];
    //                     $cDone  = $child['is_done'];
    //                     $cFill  = !$cDone      ? 'FAFAFA'
    //                         : ($cEcart === null ? 'FDF8F0'
    //                         : ($cEcart > 0      ? 'FFF5F5'
    //                                         : 'F5FFF8'));
    //                     $cFont  = !$cDone      ? 'AAAAAA'
    //                         : ($cEcart === null ? '8B1A1A'
    //                         : ($cEcart > 0      ? 'C0272D'
    //                                         : '2D7A4A'));

    //                     $sheet->setCellValue("B{$row}", '  └ Sous-zone');
    //                     $sheet->setCellValue("C{$row}", '    ' . $child['label']);
    //                     $sheet->setCellValue("D{$row}", $child['enter_at']   ?? '—');
    //                     $sheet->setCellValue("E{$row}", $child['leave_at']   ?? '—');
    //                     $sheet->setCellValue("F{$row}", $child['actual_min'] ?? '—');
    //                     $sheet->setCellValue("G{$row}", $child['target_min'] ?? '—');
    //                     $sheet->setCellValue("H{$row}", $cEcart !== null
    //                         ? ($cEcart > 0 ? '+' : '') . $cEcart
    //                         : '—');

    //                     $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
    //                         'font' => ['color' => ['rgb' => $cFont], 'italic' => true],
    //                         'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $cFill]],
    //                     ]);
    //                     // Indentation via indent
    //                     $sheet->getStyle("C{$row}")->getAlignment()->setIndent(2);
    //                     $row++;
    //                 }
    //             }
    //         }

    //         // Écart rotation vs objectif
    //         if ($rot['vs_target'] !== null) {
    //             $vsColor = $rot['vs_target'] > 0 ? 'C0272D' : '2D7A4A';
    //             $sheet->setCellValue("G{$row}", 'Écart total :');
    //             $sheet->setCellValue("H{$row}", ($rot['vs_target'] > 0 ? '+' : '') . $rot['vs_target'] . ' min');
    //             $sheet->getStyle("G{$row}:H{$row}")->applyFromArray([
    //                 'font' => ['bold' => true, 'color' => ['rgb' => $vsColor]],
    //             ]);
    //             $row++;
    //         }

    //         $row += 2; // Espace entre rotations
    //     }

    //     // Largeurs colonnes
    //     foreach ([
    //         'A' => 4, 'B' => 14, 'C' => 30, 'D' => 16,
    //         'E' => 16, 'F' => 14, 'G' => 14, 'H' => 12, 'I' => 14, 'J' => 14,
    //     ] as $col => $w) {
    //         $sheet->getColumnDimension($col)->setWidth($w);
    //     }
    // }

    public function exportExcel(Request $request)
    {
        $data = $request->validate([
            'circuit_id' => 'required|exists:circuits,id',
            'year'       => 'required|integer',
            'month'      => 'required|integer',
        ]);

        $circuit = Circuit::with(['legs', 'vehicles'])->findOrFail($data['circuit_id']);
        $report  = $this->report->monthlyReport($circuit, $data['year'], $data['month']);
        $filename = "rotations_{$circuit->code}_{$data['year']}{$data['month']}.xlsx";

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rotations');

        $this->buildHorizontalSheet($sheet, $report, $circuit);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $temp   = tempnam(sys_get_temp_dir(), 'rotation_');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function buildHorizontalSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $report,
        $circuit
    ): void {
        $rgb = fn(string $hex) => ['rgb' => ltrim($hex, '#')];
        $fmt = fn(?int $m) => $m === null ? ''
            : intdiv($m, 60) . 'h' . str_pad($m % 60, 2, '0', STR_PAD_LEFT) . 'm';

        $BORDEAUX  = '8B1A1A';
        $BORDEAUX2 = 'A52020';
        $CREAM     = 'F5F0E8';
        $SUCCESS   = '2D7A4A';
        $DANGER    = 'C0272D';
        $MUTED     = '9CA3AF';
        $WHITE     = 'FFFFFF';

        // Colonnes fixes
        $NB_FIXED  = 4;

        // Définitions d'étapes (structure des colonnes par rotation)
        $stepDefs = $this->buildStepDefinitions($circuit, $report);
        $nbSteps  = count($stepDefs);

        // Max rotations
        $maxRot = 0;
        foreach ($report['vehicle_reports'] as $vr) {
            $maxRot = max($maxRot, count($vr['rotations']));
        }
        if ($maxRot === 0 || $nbSteps === 0) {
            $sheet->setCellValue('A1', 'Aucune donnée.');
            return;
        }

        $totalCols  = $NB_FIXED + $maxRot * $nbSteps;
        $lastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // ── Ligne 1 : Titre ──────────────────────────────────────────────────────
        $sheet->setCellValue('A1', 'Rapport de Rotations ' . $circuit->name);
        $sheet->mergeCells("A1:{$lastLetter}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => $rgb($WHITE)],
            'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($BORDEAUX)],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Ligne 2 : Infos globales ──────────────────────────────────────────────
        $sheet->setCellValue('A2', 'Flatte');
        $sheet->setCellValue('B2', $circuit->code ?? '');
        $sheet->setCellValue('C2', 'Génération');
        $sheet->setCellValue('D2', $report['month_label'] ?? '');

        $col5 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5);
        $col6 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6);
        $col7 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7);
        $col8 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(8);

        $sheet->setCellValue("{$col5}2", 'Totaux Ciblé');
        $sheet->setCellValue("{$col6}2", $report['target_rotations'] ?? '—');
        $sheet->setCellValue("{$col7}2", 'Objectif (min)');
        $sheet->setCellValue("{$col8}2",
            isset($report['objective']) ? ($report['objective']->target_duration_minutes ?? '—') : '—'
        );

        $sheet->getStyle("A2:{$lastLetter}2")->applyFromArray([
            'font' => ['bold' => true, 'color' => $rgb('5C2B2B')],
            'fill' => ['fillType' => 'solid', 'startColor' => $rgb($CREAM)],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ── Ligne 3 : En-têtes "Rotation N" ──────────────────────────────────────
        $rotColors = ['8B1A1A', 'A52020', 'B83030', 'C04040'];

        $sheet->setCellValue('A3', '');
        $sheet->setCellValue('B3', '');
        $sheet->setCellValue('C3', '');
        $sheet->setCellValue('D3', '');
        $sheet->getStyle("A3:D3")->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => $rgb($BORDEAUX)],
        ]);

        for ($r = 0; $r < $maxRot; $r++) {
            $startIdx = $NB_FIXED + $r * $nbSteps + 1;
            $endIdx   = $startIdx + $nbSteps - 1;
            $startL   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startIdx);
            $endL     = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endIdx);

            $sheet->setCellValue("{$startL}3", 'Rotation ' . ($r + 1));
            if ($startIdx < $endIdx) {
                $sheet->mergeCells("{$startL}3:{$endL}3");
            }
            $bg = $rotColors[$r % count($rotColors)];
            $sheet->getStyle("{$startL}3:{$endL}3")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => $rgb($WHITE)],
                'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($bg)],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => [
                    'left'  => ['borderStyle' => 'medium', 'color' => $rgb($WHITE)],
                    'right' => ['borderStyle' => 'medium', 'color' => $rgb($WHITE)],
                ],
            ]);
        }
        $sheet->getRowDimension(3)->setRowHeight(20);

        // ── Ligne 4 : Sous-en-têtes étapes ────────────────────────────────────────
        $sheet->setCellValue('A4', 'Immatriculation');
        $sheet->setCellValue('B4', 'Véhicule');
        $sheet->setCellValue('C4', 'Nb rot.');
        $sheet->setCellValue('D4', 'Durée tot.');
        $sheet->getStyle("A4:D4")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => $rgb($WHITE)],
            'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($BORDEAUX)],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('DDDDDD')]],
        ]);

        for ($r = 0; $r < $maxRot; $r++) {
            $bg = $rotColors[$r % count($rotColors)];
            foreach ($stepDefs as $sIdx => $step) {
                $colIdx = $NB_FIXED + $r * $nbSteps + $sIdx + 1;
                $colL   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue("{$colL}4", $step['short_label']);
                $sheet->getStyle("{$colL}4")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 8, 'color' => $rgb($WHITE)],
                    'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($bg)],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('DDDDDD')]],
                ]);
            }
        }
        $sheet->getRowDimension(4)->setRowHeight(36);

        // ── Lignes données ─────────────────────────────────────────────────────────
        $row = 5;
        foreach ($report['vehicle_reports'] as $vi => $vr) {
            $bg0 = $vi % 2 === 0 ? 'FFFFFF' : 'FBF7F3';

            // Colonnes fixes
            $sheet->setCellValue("A{$row}", $vr['vehicle']->plate_number ?? '—');
            $sheet->setCellValue("B{$row}", $vr['vehicle']->name);
            $sheet->setCellValue("C{$row}", $vr['rotation_count']);
            $sheet->setCellValue("D{$row}", $vr['total_duration'] ? round($vr['total_duration'] / 60, 1) : '—');

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($bg0)],
                'font'      => ['size' => 8],
                'alignment' => ['vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E0E0E0')]],
            ]);

            // Nb rotations coloré
            $cColor = $vr['target_rotations']
                ? ($vr['rotation_count'] >= $vr['target_rotations'] ? $SUCCESS : $DANGER)
                : '1A1208';
            $sheet->getStyle("C{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => $rgb($cColor)],
                'alignment' => ['horizontal' => 'center'],
            ]);

            // Colonnes dynamiques
            for ($r = 0; $r < $maxRot; $r++) {
                $rot = $vr['rotations'][$r] ?? null;
                $bg1 = $rot
                    ? ($r % 2 === 0 ? $bg0 : ($vi % 2 === 0 ? 'FDF5F5' : 'F9F0F0'))
                    : 'F0F0F0';

                foreach ($stepDefs as $sIdx => $step) {
                    $colIdx = $NB_FIXED + $r * $nbSteps + $sIdx + 1;
                    $colL   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);

                    if (!$rot) {
                        $sheet->setCellValue("{$colL}{$row}", '');
                        $sheet->getStyle("{$colL}{$row}")->applyFromArray([
                            'fill'    => ['fillType' => 'solid', 'startColor' => $rgb('F0F0F0')],
                            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E8E8E8')]],
                        ]);
                        continue;
                    }

                    [$val, $color, $bold] = $this->getCellValue($step, $rot, $fmt);

                    $sheet->setCellValue("{$colL}{$row}", $val);
                    $sheet->getStyle("{$colL}{$row}")->applyFromArray([
                        'font'      => [
                            'bold'  => $bold,
                            'size'  => 8,
                            'color' => $color ? $rgb($color) : $rgb('1A1208'),
                        ],
                        'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($bg1)],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E8E8E8')]],
                    ]);
                }

                // Séparateur droit entre groupes rotation
                $lastGroupL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                    $NB_FIXED + ($r + 1) * $nbSteps
                );
                $sheet->getStyle("{$lastGroupL}{$row}")->applyFromArray([
                    'borders' => ['right' => ['borderStyle' => 'medium', 'color' => $rgb('AAAAAA')]],
                ]);
            }

            $sheet->getRowDimension($row)->setRowHeight(14);
            $row++;
        }

        // ── Largeurs colonnes ─────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(10);

        for ($r = 0; $r < $maxRot; $r++) {
            foreach ($stepDefs as $sIdx => $step) {
                $colIdx = $NB_FIXED + $r * $nbSteps + $sIdx + 1;
                $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getColumnDimension($col)->setWidth($step['width'] ?? 14);
            }
        }

        // Figer colonnes fixes + lignes en-têtes
        $freezeCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($NB_FIXED + 1);
        $sheet->freezePane("{$freezeCol}5");
        $sheet->setAutoFilter("A4:{$lastLetter}4");
    }

    private function buildStepDefinitions($circuit, array $report): array
    {
        $allLegs = $circuit->legs()->orderBy('order')->get();

        $zonePairs      = [];
        $pairedEnterIds = [];
        $pairedExitIds  = [];

        foreach ($allLegs as $leg) {
            if ($leg->event_type !== 'enter_zone') continue;
            if (in_array($leg->id, $pairedEnterIds)) continue;
            $leave = $allLegs->first(fn($l) =>
                $l->event_type === 'leave_zone' &&
                $l->reference_id == $leg->reference_id &&
                $l->order > $leg->order &&
                !in_array($l->id, $pairedExitIds)
            );
            if ($leave) {
                $zonePairs[$leg->id] = $leave->id;
                $pairedEnterIds[]    = $leg->id;
                $pairedExitIds[]     = $leave->id;
            }
        }

        $steps   = [];
        $skipIds = [];

        foreach ($allLegs as $leg) {
            if (in_array($leg->id, $skipIds)) continue;

            if ($leg->event_type === 'pass_checkpoint') {
                $steps[]   = ['type' => 'cp',         'leg_id' => $leg->id, 'leave_id' => null, 'short_label' => $this->shortLabel($leg->label),               'width' => 13];
                $skipIds[] = $leg->id;
                continue;
            }

            if ($leg->event_type === 'leave_zone' && in_array($leg->id, $pairedExitIds)) {
                $skipIds[] = $leg->id;
                continue;
            }

            if ($leg->event_type === 'enter_zone') {
                $leaveId = $zonePairs[$leg->id] ?? null;
                $leaveLeg = $leaveId ? $allLegs->firstWhere('id', $leaveId) : null;

                // Timestamp arrivée dans la zone principale
                $steps[] = ['type' => 'zone_enter', 'leg_id' => $leg->id, 'leave_id' => $leaveId,
                            'short_label' => $this->shortLabel($leg->label) . ' - Arrivée', 'width' => 14];

                // Sous-zones entre enter et leave
                if ($leaveLeg) {
                    $inner = $allLegs->filter(fn($l) =>
                        $l->order > $leg->order &&
                        $l->order < $leaveLeg->order &&
                        $l->event_type === 'enter_zone' &&
                        !in_array($l->id, $skipIds)
                    );

                    foreach ($inner as $il) {
                        $iz = \App\Models\Zone::find($il->reference_id);
                        if (!$iz || $iz->parent_id === null) continue;

                        $ilLeaveId = $zonePairs[$il->id] ?? null;
                        $lbl       = $this->shortLabel($il->label);

                        $steps[] = ['type' => 'sub_enter',    'leg_id' => $il->id, 'leave_id' => $ilLeaveId,
                                    'short_label' => $lbl . ' - Arrivée', 'width' => 14];
                        $steps[] = ['type' => 'sub_duration', 'leg_id' => $il->id, 'leave_id' => $ilLeaveId,
                                    'short_label' => $lbl . ' - Durée', 'width' => 10];
                        if ($ilLeaveId) {
                            $steps[] = ['type' => 'sub_leave', 'leg_id' => $il->id, 'leave_id' => $ilLeaveId,
                                        'short_label' => $lbl . ' - Départ', 'width' => 14];
                        }

                        $skipIds[] = $il->id;
                        if ($ilLeaveId) $skipIds[] = $ilLeaveId;
                    }
                }

                // Durée zone principale
                if ($leaveId) {
                    $steps[] = ['type' => 'zone_duration', 'leg_id' => $leg->id, 'leave_id' => $leaveId,
                                'short_label' => $this->shortLabel($leg->label) . ' - Durée', 'width' => 10];
                }

                // Timestamp départ zone principale
                if ($leaveLeg) {
                    $steps[] = ['type' => 'zone_leave', 'leg_id' => $leg->id, 'leave_id' => $leaveId,
                                'short_label' => $this->shortLabel($leaveLeg->label) . ' - Départ', 'width' => 14];
                }

                $skipIds[] = $leg->id;
                if ($leaveId) $skipIds[] = $leaveId;
                continue;
            }

            if ($leg->event_type === 'leave_zone') {
                $steps[]   = ['type' => 'zone_leave_unpaired', 'leg_id' => $leg->id, 'leave_id' => null,
                              'short_label' => $this->shortLabel($leg->label) . ' - Départ', 'width' => 14];
                $skipIds[] = $leg->id;
            }
        }

        // Colonne écart rotation (RHT var)
        $steps[] = ['type' => 'rht_var', 'leg_id' => null, 'leave_id' => null,
                    'short_label' => 'RHT var Rotation', 'width' => 12];

        return $steps;
    }

    private function getCellValue(array $step, array $rot, callable $fmt): array
    {
        $SUCCESS = '2D7A4A';
        $DANGER  = 'C0272D';
        $MUTED   = '9CA3AF';

        if ($step['type'] === 'rht_var') {
            $v = $rot['vs_target'];
            return [
                $v !== null ? ($v > 0 ? '+' : '') . $fmt($v) : '—',
                $v !== null ? ($v > 0 ? $DANGER : $SUCCESS) : $MUTED,
                true,
            ];
        }

        // Chercher le bon bloc par leg_id
        $block = null;
        $child = null;

        foreach ($rot['blocks'] as $b) {
            if (in_array($step['type'], ['zone_enter','zone_leave','zone_duration','zone_leave_unpaired'])) {
                if ($b['type'] === 'zone' && ($b['enter_leg_id'] ?? null) === $step['leg_id']) {
                    $block = $b;
                    break;
                }
            }
            if (in_array($step['type'], ['sub_enter','sub_leave','sub_duration'])) {
                foreach ($b['children'] ?? [] as $c) {
                    if (($c['enter_leg_id'] ?? null) === $step['leg_id']) {
                        $child = $c;
                        break 2;
                    }
                }
            }
            if ($step['type'] === 'cp') {
                if ($b['type'] === 'checkpoint' && ($b['leg_id'] ?? null) === $step['leg_id']) {
                    $block = $b;
                    break;
                }
            }
        }

        return match($step['type']) {
            'cp'               => [$block['occurred_at'] ?? '—', $block && $block['is_done'] ? null : $MUTED, false],
            'zone_enter'       => [$block['enter_at']   ?? '—', $block && $block['is_done'] ? null : $MUTED, false],
            'zone_leave',
            'zone_leave_unpaired' => [$block['leave_at'] ?? '—', $block && $block['is_done'] ? null : $MUTED, false],
            'zone_duration'    => [
                $fmt($block['actual_min'] ?? null),
                $block && $block['ecart'] !== null ? ($block['ecart'] > 0 ? $DANGER : $SUCCESS) : null,
                $block && $block['actual_min'] !== null,
            ],
            'sub_enter'        => [$child['enter_at']   ?? '—', $child && $child['is_done'] ? null : $MUTED, false],
            'sub_leave'        => [$child['leave_at']   ?? '—', $child && $child['is_done'] ? null : $MUTED, false],
            'sub_duration'     => [
                $fmt($child['actual_min'] ?? null),
                $child && $child['ecart'] !== null ? ($child['ecart'] > 0 ? $DANGER : $SUCCESS) : null,
                false,
            ],
            default            => ['—', $MUTED, false],
        };
    }

    private function shortLabel(string $label): string
    {
        $label = preg_replace('/^(T\d+\s*[-–]\s*)/i', '', $label);
        $label = preg_replace('/^(CP\s*[-–]\s*)/i', '', $label);
        $label = preg_replace('/^(Entrée zone|Sortie zone|Passage Check point)\s*/i', '', $label);
        if (preg_match('/\(([^)]+)\)/', $label, $m)) return trim($m[1]);
        return trim($label);
    }
}
