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

    public function exportExcel(Request $request)
    {
        $data = $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer',
        ]);

        $circuits = Circuit::with(['legs', 'vehicles'])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // supprimer la feuille vide par défaut

        foreach ($circuits as $index => $circuit) {
            $report = $this->report->monthlyReport($circuit, $data['year'], $data['month']);

            $sheet = $spreadsheet->createSheet($index);
            $sheet->setTitle(substr($circuit->code ?? $circuit->name, 0, 31)); // Excel limite à 31 chars

            $this->buildHorizontalSheet($sheet, $report, $circuit);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = "rotations_{$data['year']}{$data['month']}.xlsx";
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $temp     = tempnam(sys_get_temp_dir(), 'rotation_');
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
        $fmt = fn(?int $s) => $s === null
        ? '—'
        : str_pad(intdiv($s, 3600), 2, '0', STR_PAD_LEFT) . ':'
            . str_pad(intdiv($s % 3600, 60), 2, '0', STR_PAD_LEFT) . ':'
            . str_pad($s % 60, 2, '0', STR_PAD_LEFT);
        

        $BORDEAUX  = '8B1A1A';
        $BORDEAUX2 = 'A52020';
        $CREAM     = 'F5F0E8';
        $SUCCESS   = '2D7A4A';
        $DANGER    = 'C0272D';
        $MUTED     = '9CA3AF';
        $WHITE     = 'FFFFFF';

        // Colonnes fixes avant les étapes
        // A = Immat | B = Véhicule | C = Nb rotations (total véhicule) | D = Durée rotation
        $NB_FIXED = 4;

        // Définitions d'étapes : 1 colonne par étape (fixe, basé sur le circuit)
        $stepDefs = $this->buildStepDefinitions($circuit, $report);
        $nbSteps  = count($stepDefs);
        $totalCols  = $NB_FIXED + $nbSteps;
        $lastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
        if ($nbSteps === 0) {
            $sheet->setCellValue('A1', 'Aucune donnée.');
            return;
        }

        // ── Ligne 1 : Titre ──────────────────────────────────────────────────────
        $titre = "Rapport de Rotations – {$circuit->name} ({$report['month_label']})";
        $sheet->setCellValue('A1', $titre);
        $longueurTexte = strlen($titre);
        $caracteresParColonne = 12; 
        $nombreDeColonnes = ceil($longueurTexte / $caracteresParColonne);
        $nombreDeColonnes = max(1, $nombreDeColonnes);
        $derniereColonne = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($nombreDeColonnes);
        $sheet->mergeCells("A1:{$derniereColonne}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => $rgb($WHITE)],
            'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($BORDEAUX)],
            'alignment' => [
                'horizontal' => 'left', 
                'vertical' => 'center'
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Ligne 2 : Infos globales ──────────────────────────────────────────────
        $infoData = [
            'A' => $circuit->code ?? '',
            'B' => '',
            'C' => 'Objectif de la rotation',
            'D' => '',
            'E' => 'Détail des étapes',
            'F' => '',
            'G' => '',
            'H' => '',
        ];
        foreach ($infoData as $col => $val) {
            $sheet->setCellValue("{$col}2", $val);
        }
        $sheet->mergeCells("A2:B2");
        $sheet->mergeCells("C2:D2");
        $sheet->mergeCells("E2:H2");
        $sheet->getStyle("A2:{$lastLetter}2")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => $rgb('5C2B2B')],
            'fill' => ['fillType' => 'solid', 'startColor' => $rgb($CREAM)],
            'alignment' => [
                'horizontal' => 'center', 
                'vertical' => 'center'
            ],
        ]);
        $sheet->getStyle("C2:D2")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => $rgb('5C2B2B')],
        ]);
        foreach (['C', 'D'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ── Ligne 3 : Valeurs cibles (Totaux ciblés + objectifs durée par étape) ─
        // Optionnel : afficher les objectifs de durée dans la ligne 3
        $sheet->setCellValue('A3', 'Valeurs Cibles');
        $sheet->mergeCells("A3:C3");
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => $rgb($WHITE)],
            'alignment' => [
                'horizontal' => 'center', 
                'vertical' => 'center'
            ],
        ]);
        $sheet->setCellValue('D3', isset($report['objective'])
            ? $fmt($report['objective']->target_duration_seconds ?? null) : '—'
        );
        $sheet->getStyle('D3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => $rgb($WHITE)],
            'alignment' => [
                'horizontal' => 'center', 
                'vertical' => 'center'
            ],
        ]);

        // Objectifs par étape dans les colonnes correspondantes
        if (isset($report['objective'])) {
            $legObjectives = $report['objective']->leg_objectives ?? [];
            foreach ($stepDefs as $sIdx => $step) {
                if (!in_array($step['type'], ['zone_duration', 'sub_duration'])) continue;
                $obj = $legObjectives[$step['leg_id']] ?? $legObjectives[(string)$step['leg_id']] ?? null;
                if ($obj) {
                    $colL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($NB_FIXED + $sIdx + 1);
                    $sheet->setCellValue("{$colL}3", $fmt((int)$obj));
                    $sheet->getStyle("{$colL}3")->applyFromArray([
                        'font' => ['bold' => true, 'color' => $rgb('5C2B2B')],
                        'alignment' => [
                            'horizontal' => 'center', 
                            'vertical' => 'center'
                        ],
                    ]);
                }
            }
        }
        $sheet->getStyle("A3:{$lastLetter}3")->applyFromArray([
            'font' => ['bold' => true, 'color' => $rgb('5C2B2B')],
            'fill' => ['fillType' => 'solid', 'startColor' => $rgb('EDD5D5')],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // ── Ligne 4 : En-têtes colonnes ───────────────────────────────────────────
        $fixedHeaders = ['Immatriculation', 'Véhicule', 'Nb rot. total', 'Durée rotation'];
        foreach ($fixedHeaders as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}4", $h);
        }

        foreach ($stepDefs as $sIdx => $step) {
            $colL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($NB_FIXED + $sIdx + 1);
            $sheet->setCellValue("{$colL}4", $step['short_label']);
        }

        $sheet->getStyle("A4:{$lastLetter}4")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => $rgb($WHITE)],
            'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($BORDEAUX)],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('CCCCCC')]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(36);

        $allRotations = $report['vehicle_reports']
        ->flatMap(fn($vr) => collect($vr['rotations'])->map(fn($rot) => [
            'vehicle' => $vr['vehicle'],
            'rotation_count' => $vr['rotation_count'],
            'rot' => $rot,
        ]))
        ->sortByDesc(fn($item) => $item['rot']['duration_seconds'] ?? 0)
        ->values();

        // ── Lignes données : 1 ligne par rotation ─────────────────────────────────
        $row       = 5;
        $prevImmat = null; // Pour fusionner les cellules immat/vehicule/nb quand même véhicule

        // foreach ($report['vehicle_reports'] as $vi => $vr) {
        //     $rotCount    = count($vr['rotations']);
        //     $vehicleStart = $row; // ligne de départ pour ce véhicule (fusion possible)

        //     // Couleur alternée par véhicule
        //     $bg0 = $vi % 2 === 0 ? 'FFFFFF' : 'FBF7F3';
        //     $bg1 = $vi % 2 === 0 ? 'FDF5F5' : 'FAF0EF'; // légèrement teinté pour 2e rotation+

        //     foreach ($vr['rotations'] as $ri => $rot) {
        //         $isFirstRot = $ri === 0;
        //         $rowBg      = $isFirstRot ? $bg0 : $bg1;

        //         // ── Colonnes fixes ───────────────────────────────────────────────

        //         // Immatriculation — seulement sur la 1ère ligne du véhicule
        //         $sheet->setCellValue("A{$row}", $vr['vehicle']->plate_number ?? '—');
        //         // Nom véhicule — seulement sur la 1ère ligne
        //         $sheet->setCellValue("B{$row}", $vr['vehicle']->name ?? '—');
        //         // Nb rotations total — seulement sur la 1ère ligne
        //         $sheet->setCellValue("C{$row}", $rot['status'] === 'completed' ? 1 : 0);
        //         // Durée de CETTE rotation
        //         $sheet->setCellValue("D{$row}", $rot['duration_label'] ?? '—');

        //         // Style colonnes fixes
        //         $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
        //             'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($rowBg)],
        //             'font'      => ['size' => 8],
        //             'alignment' => ['vertical' => 'center'],
        //             'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E0E0E0')]],
        //         ]);
        //         $sheet->getStyle("D{$row}")->applyFromArray([
        //             'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($rowBg)],
        //             'font' => ['bold' => true, 'size' => 8],
        //             'alignment' => ['vertical' => 'center'],
        //             'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E0E0E0')]],
        //         ]);
        //         $sheet->getStyle("A{$row}:C{$row}")->getAlignment()
        //         ->setHorizontal('center')
        //         ->setVertical('center');
        //         $sheet->getStyle("D{$row}")->getAlignment()
        //         ->setHorizontal('center')
        //         ->setVertical('center');

        //         // Colonne C : colorée selon objectif (seulement 1ère ligne)
        //         if ($isFirstRot && $vr['target_rotations']) {
        //         }

        //         // Bordure gauche marquant le début d'un véhicule (1ère rotation seulement)
        //         if ($isFirstRot) {
        //             $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
        //                 'borders' => [
        //                     'top' => ['borderStyle' => 'medium', 'color' => $rgb($BORDEAUX2)],
        //                 ],
        //             ]);
        //         }

        //         // ── Colonnes étapes ──────────────────────────────────────────────
        //         foreach ($stepDefs as $sIdx => $step) {
        //             $colL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($NB_FIXED + $sIdx + 1);

        //             [$val, $color, $bold] = $this->getCellValue($step, $rot, $fmt);

        //             $sheet->setCellValue("{$colL}{$row}", $val);
        //             $sheet->getStyle("{$colL}{$row}")->applyFromArray([
        //                 'font'      => [
        //                     'bold'  => $bold,
        //                     'size'  => 8,
        //                     'color' => $color ? $rgb($color) : $rgb('1A1208'),
        //                 ],
        //                 'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($rowBg)],
        //                 'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        //                 'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E8E8E8')]],
        //             ]);

        //             // Bordure top sur 1ère rotation du véhicule
        //             if ($isFirstRot) {
        //                 $sheet->getStyle("{$colL}{$row}")->applyFromArray([
        //                     'borders' => ['top' => ['borderStyle' => 'medium', 'color' => $rgb($BORDEAUX2)]],
        //                 ]);
        //             }
        //         }

        //         $sheet->getRowDimension($row)->setRowHeight(14);
        //         $row++;
        //     }

        // }

        foreach ($allRotations as $ri => $item) {
            $rot     = $item['rot'];
            $vehicle = $item['vehicle'];
            $rowBg   = $ri % 2 === 0 ? 'FFFFFF' : 'FBF7F3';

            $sheet->setCellValue("A{$row}", $vehicle->plate_number ?? '—');
            $sheet->setCellValue("B{$row}", $vehicle->name ?? '—');
            $sheet->setCellValue("C{$row}", $rot['status'] === 'completed' ? 1 : 0);
            $sheet->setCellValue("D{$row}", $rot['duration_label'] ?? '—');

            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($rowBg)],
                'font'      => ['size' => 8],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E0E0E0')]],
            ]);
            $sheet->getStyle("D{$row}")->applyFromArray([
                'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($rowBg)],
                'font'      => ['bold' => true, 'size' => 8],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E0E0E0')]],
            ]);

            // ── Colonnes étapes ──────────────────────────────────────────────
            foreach ($stepDefs as $sIdx => $step) {
                $colL = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($NB_FIXED + $sIdx + 1);

                [$val, $color, $bold] = $this->getCellValue($step, $rot, $fmt);

                $sheet->setCellValue("{$colL}{$row}", $val);
                $sheet->getStyle("{$colL}{$row}")->applyFromArray([
                    'font'      => [
                        'bold'  => $bold,
                        'size'  => 8,
                        'color' => $color ? $rgb($color) : $rgb('1A1208'),
                    ],
                    'fill'      => ['fillType' => 'solid', 'startColor' => $rgb($rowBg)],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => $rgb('E8E8E8')]],
                ]);
            }

            $sheet->getRowDimension($row)->setRowHeight(14);
            $row++;
        }

        // ── Largeurs colonnes ─────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(11);

        foreach ($stepDefs as $sIdx => $step) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($NB_FIXED + $sIdx + 1);
            $sheet->getColumnDimension($col)->setWidth($step['width'] ?? 14);
        }

        // Figer les 4 premières colonnes + les 4 lignes d'en-tête
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
                            'short_label' => $this->shortLabel($leg->label) . ' - Date d\'arrivée', 'width' => 14];

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
                                    'short_label' => $lbl . ' - Date d\'arrivée', 'width' => 14];
                        $steps[] = ['type' => 'sub_duration', 'leg_id' => $il->id, 'leave_id' => $ilLeaveId,
                                    'short_label' => $lbl . ' - Durée', 'width' => 10];
                        if ($ilLeaveId) {
                            $steps[] = ['type' => 'sub_leave', 'leg_id' => $il->id, 'leave_id' => $ilLeaveId,
                                        'short_label' => $lbl . ' - Date de départ', 'width' => 14];
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
                                'short_label' => $this->shortLabel($leaveLeg->label) . ' - Date de départ', 'width' => 14];
                }

                if ($leaveLeg) {
                    $nextEnter = $allLegs->first(fn($l) =>
                        $l->order > $leaveLeg->order &&
                        $l->event_type === 'enter_zone' &&
                        !in_array($l->id, $skipIds) &&
                        $l->id !== $leg->id
                    );
                    if ($nextEnter) {
                        $steps[] = [
                            'type'         => 'inter_duration',
                            'leg_id'       => $leaveId,        // leave_leg_id (départ)
                            'enter_leg_id' => $nextEnter->id,  // enter_leg_id (arrivée)
                            'leave_id'     => null,
                            'short_label'  => $this->shortLabel($leaveLeg->label) . ' → ' . $this->shortLabel($nextEnter->label),
                            'width'        => 12,
                        ];
                    }
                }

                $skipIds[] = $leg->id;
                if ($leaveId) $skipIds[] = $leaveId;
                continue;
            }

            if ($leg->event_type === 'leave_zone') {
                $steps[]   = ['type' => 'zone_leave_unpaired', 'leg_id' => $leg->id, 'leave_id' => null,
                              'short_label' => $this->shortLabel($leg->label) . ' - Date de départ', 'width' => 14];
                $skipIds[] = $leg->id;

                $nextEnter = $allLegs->first(fn($l) =>
                    $l->order > $leg->order &&
                    $l->event_type === 'enter_zone' &&
                    !in_array($l->id, $skipIds)
                );
                if ($nextEnter) {
                    $steps[] = [
                        'type'        => 'inter_duration',
                        'leg_id'      => $leg->id,        
                        'enter_leg_id'=> $nextEnter->id,  
                        'leave_id'    => null,
                        'short_label' => $this->shortLabel($leg->label) . ' → ' . $this->shortLabel($nextEnter->label),
                        'width'       => 12,
                    ];
                }

            }
        }

        // Colonne écart rotation (RHT var)
        $steps[] = ['type' => 'ecart', 'leg_id' => null, 'leave_id' => null,
                    'short_label' => 'Ecart', 'width' => 12];

        return $steps;
    }

    private function getCellValue(array $step, array $rot, callable $fmt): array
    {
        $SUCCESS = '2D7A4A';
        $DANGER  = 'C0272D';
        $MUTED   = '9CA3AF';

        if ($step['type'] === 'ecart') {
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
            // ,'zone_leave_unpaired'
            if (in_array($step['type'], ['zone_enter','zone_leave','zone_duration'])) {
                if ($b['type'] === 'zone' && ($b['enter_leg_id'] ?? null) === $step['leg_id']) {
                    $block = $b;
                    break;
                }
            }

            if ($step['type'] === 'zone_leave_unpaired') {
                if ($b['type'] === 'zone' && ($b['leave_leg_id'] ?? null) === $step['leg_id'] && $b['leave_at'] !== null) {
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

            if ($step['type'] === 'inter_duration') {
                break;
            }
        }
        
        $convertToTz = function($dateString) {
            if (!$dateString || $dateString === '—') return '—';
            
            return \Illuminate\Support\Carbon::createFromFormat('d/m H:i:s', $dateString) // On ajoute manuellement les 3h
                ->format('d/m H:i:s');
        };

        return match($step['type']) {
            'cp'               => [$convertToTz($block['occurred_at'] ?? '—'), $block && $block['is_done'] ? null : $MUTED, false],
            'zone_enter_virtual'  => ['—', $MUTED, false], 
            'zone_enter'       => [$convertToTz($block['enter_at']   ?? '—'), $block && $block['is_done'] ? null : $MUTED, false],
            'zone_leave',
            'zone_leave_unpaired' => [$convertToTz($block['leave_at'] ?? '—'), $block && $block['is_done'] ? null : $MUTED, false],
            'zone_duration'    => [
                $fmt($block['actual_sec'] ?? null),
                $block && $block['ecart'] !== null ? ($block['ecart'] > 0 ? $DANGER : $SUCCESS) : null,
                $block && $block['actual_sec'] !== null,
            ],
            'inter_duration' => (function() use ($step, $rot, $fmt, $MUTED) {  // ← AJOUTE ICI
                $leaveBlock = null;
                $enterBlock = null;

                foreach ($rot['blocks'] as $b) {
                    if ($b['type'] !== 'zone') continue;
                    if (($b['leave_leg_id'] ?? null) === $step['leg_id'] && $b['leave_at'] !== null) {
                        $leaveBlock = $b;
                    }
                    if (($b['enter_leg_id'] ?? null) === $step['enter_leg_id'] && $b['enter_at'] !== null) {
                        $enterBlock = $b;
                    }
                }

                if (!$leaveBlock || !$enterBlock) {
                    return ['—', $MUTED, false];
                }

                try {
                    $leave = \Carbon\Carbon::createFromFormat('d/m H:i:s', $leaveBlock['leave_at'])
                                ->setYear(now()->year);
                    $enter = \Carbon\Carbon::createFromFormat('d/m H:i:s', $enterBlock['enter_at'])
                                ->setYear(now()->year);

                    if ($enter->lt($leave)) {
                        $enter->addDay();
                    }

                    $diffSec = (int) $leave->diffInSeconds($enter);
                    return [$fmt($diffSec), null, true];
                } catch (\Exception $e) {
                    return ['—', $MUTED, false];
                }
            })(),
            'sub_enter'        => [$convertToTz($child['enter_at']   ?? '—'), $child && $child['is_done'] ? null : $MUTED, false],
            'sub_leave'        => [$convertToTz($child['leave_at']   ?? '—'), $child && $child['is_done'] ? null : $MUTED, false],
            'sub_duration'     => [
                $fmt($child['actual_sec'] ?? null),
                $child && $child['ecart'] !== null ? ($child['ecart'] > 0 ? $DANGER : $SUCCESS) : null,
                true,
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
