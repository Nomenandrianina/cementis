@extends('layouts.app')

@section('title', 'Rotation #' . $rotation->id)
@section('page-title', 'Rotation')

@section('topbar-actions')
    <a href="{{ route('rotations.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
@endsection
    <script>
    let currentView = 'list'; // 'horizontal' ou 'list'

    function toggleView() {
        const h      = document.getElementById('view-horizontal');
        const l      = document.getElementById('view-list');
        const btn    = document.getElementById('view-toggle');

        if (currentView === 'list') {
            h.style.display   = 'block';
            l.style.display   = 'none';
            btn.textContent   = '⟷ Timeline';
            currentView       = 'horizontal';
            
        } else {
            h.style.display   = 'none';
            l.style.display   = 'block';
            btn.textContent   = '☰ Liste';
            currentView       = 'list';
        }
    }
    </script>
    {{-- @endpush --}}
@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="grid-2" style="gap:24px;">

    {{-- Informations générales --}}
    <div>
        <div class="card mb-16">
            <div class="card-header">
                <span class="card-title">Informations</span>
                @if($rotation->is_valid)
                    <span class="badge badge-success">✓ Valide</span>
                @else
                    <span class="badge badge-danger">✗ Invalide</span>
                @endif
            </div>
            <div class="card-body">
                <table style="width:100%;font-size:13px;">
                    <tbody>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;width:40%;">Camion</td>
                            <td style="font-weight:600;">{{ $rotation->rvehicule->name }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Immatriculation</td>
                            <td class="mono">{{ $rotation->rvehicule->plate_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Circuit</td>
                            <td>{{ $rotation->circuit->name }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Mois compté</td>
                            <td class="mono">{{ $rotation->counted_month ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Statut</td>
                            <td>
                                @switch($rotation->status)
                                    @case('completed') <span class="badge badge-success">Complète</span> @break
                                    @case('acceptable') <span class="badge badge-info">Acceptable</span> @break
                                    @case('in_progress') <span class="badge badge-blue">En cours</span> @break
                                    @case('cancelled') <span class="badge badge-danger">Annulée</span> @break
                                @endswitch
                            </td>
                        </tr>
                        @if($rotation->invalidation_reason)
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Raison</td>
                            <td style="color:var(--danger);font-size:12px;">{{ $rotation->invalidation_reason }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Durées vs objectifs --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Durée vs Objectif</span>
            </div>
            <div class="card-body">
                @php
                    $targetDur = $objective?->target_duration_seconds;
                    $actualDur = $rotation->duration_seconds;
                @endphp

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;margin-bottom:16px;">
                    <div>
                        <div class="stat-label">Début de la rotation</div>
                        <div class="mono" style="font-size:14px;color:var(--text);">
                            {{ $rotation->started_at?->format('d/m/Y') }}<br>
                            <span style="color:var(--accent);font-size:18px;font-family:var(--head);font-weight:700;">
                                {{-- {{ $rotation->started_at?->format('H:i') }} --}}
                                {{ $rotation->started_at_local?->format('H:i:s') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">Fin de la rotation</div>
                        <div class="mono" style="font-size:14px;color:var(--text);">
                            {{ $rotation->completed_at?->format('d/m/Y') ?? '—' }}<br>
                            <span style="color:var(--accent);font-size:18px;font-family:var(--head);font-weight:700;">
                                {{ $rotation->completed_at?->format('H:i:s') ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">Durée totale</div>
                        <span style="font-family:var(--head);font-size:24px;font-weight:900;color:
                            @if($actualDur && $targetDur)
                                {{ $actualDur <= $targetDur ? 'var(--success)' : 'var(--danger)' }}
                            @else
                                var(--accent)
                            @endif
                        ">
                            @if($actualDur)
                                @durSec($actualDur)
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </div>

                @if($targetDur && $actualDur)
                    @php $pct = min(round($actualDur / $targetDur * 100), 150); @endphp
                    <div style="margin-top:8px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:4px;">
                            <span>Objectif : @durSec($targetDur)</span>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $pct > 100 ? 'over' : 'good' }}" style="width:{{ min($pct, 100) }}%;"></div>
                        </div>
                        @php $ecart = $actualDur - $targetDur; @endphp
                        <div style="text-align:right;font-size:12px;margin-top:6px;" class="{{ $ecart > 0 ? 'text-danger' : 'text-success' }}">
                            @durSec($ecart) vs objectif
                        </div>
                    </div>
                @elseif(!$targetDur)
                    <p style="font-size:12px;color:var(--muted);">Aucun objectif défini pour ce circuit.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline des étapes --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Étapes de la rotation</span>
            <div style="display:flex;align-items:center;gap:10px;margin-left:auto;">
                <span class="badge badge-muted">{{ $rotation->rotationLegs->count() }} / {{ $rotation->circuit->legs->count() }} étapes</span>
                {{-- Toggle vue --}}
                <button onclick="toggleView()" id="view-toggle"
                        style="background:var(--cream);border:1px solid var(--cream-dd);border-radius:5px;
                            padding:4px 10px;font-size:10px;font-weight:600;letter-spacing:0.08em;
                            text-transform:uppercase;cursor:pointer;color:var(--muted);font-family:var(--sans);">
                    ☰ Liste
                </button>
            </div>
        </div>

        @php
            $legObjectives = $objective?->leg_objectives ?? [];
            $allLegs       = $rotation->circuit->legs;
            $completedLegs = $rotation->rotationLegs->keyBy('circuit_leg_id');

            // Stats globales
            $doneCount      = $rotation->rotationLegs->count();
            $totalCount     = $allLegs->count();
            $progressPct    = $totalCount > 0 ? round($doneCount / $totalCount * 100) : 0;
        @endphp

        {{-- Barre de progression globale --}}
        <div style="padding:0 20px;border-bottom:1px solid var(--cream-d);">
            <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--muted);margin-bottom:4px;padding-top:10px;">
                <span>Progression</span>
                <span class="mono">{{ $progressPct }}%</span>
            </div>
            <div style="height:4px;background:var(--cream-d);border-radius:2px;margin-bottom:10px;">
                <div style="height:100%;width:{{ $progressPct }}%;
                            background:{{ $progressPct === 100 ? 'var(--success)' : 'var(--bordeaux)' }};
                            border-radius:2px;transition:width 0.4s ease;"></div>
            </div>
        </div>

        {{-- ── VUE HORIZONTALE (défaut) ──────────────────────────────────────── --}}
        @php
            $currentParentId = null;
            // Pré-calculer les paires entrée→sortie et les durées
            $zonePairs    = []; // enter_leg_id => exit_leg_id
            $zoneActualSec = []; // enter_leg_id => minutes réels
            $pendingEntries = []; // reference_id => ['leg_id', 'occurred_at']

            foreach ($allLegs as $leg) {
                $rl = $completedLegs->get($leg->id);
                if ($leg->event_type === 'enter_zone') {
                    $pendingEntries[$leg->reference_id] = [
                        'leg_id'      => $leg->id,
                        'occurred_at' => $rl?->occurred_at,
                    ];
                } elseif (in_array($leg->event_type, ['exit_zone', 'leave_zone'])) {
                    if (isset($pendingEntries[$leg->reference_id])) {
                        $entry = $pendingEntries[$leg->reference_id];
                        $zonePairs[$entry['leg_id']] = $leg->id;
                        if ($entry['occurred_at'] && $rl) {
                            $zoneActualSec[$entry['leg_id']] = (int) $rl->occurred_at->diffInSeconds($entry['occurred_at']);
                        }
                        unset($pendingEntries[$leg->reference_id]);
                    }
                }
            }

            // IDs des legs sortie qui ont une entrée pairée (pour skip le rowspan sur ces lignes)
            $pairedExitIds  = array_values($zonePairs);  // exit_leg_ids qui ont déjà leur rowspan
            $pairedEnterIds = array_keys($zonePairs);     // enter_leg_ids qui ont une sortie

            $fmt = function($min) {
                if ($min === null) return null;
                $abs = abs($min);
                $h = intdiv($abs, 60);
                $m = $abs % 60;
                return ($h > 0 ? $h.'h' : '').str_pad($m, 2, '0', STR_PAD_LEFT).'m';
            };
        @endphp
        <div id="view-horizontal" style="display:none">
            {{-- Scroll horizontal avec les étapes --}}
            <div style="overflow-x:auto; padding:40px 20px 20px; scrollbar-width:thin; scrollbar-color:var(--cream-dd) transparent;">
                <div style="display:flex; align-items:flex-start; gap:0; min-width:max-content; position:relative;">

                    @foreach($allLegs as $idx => $leg)
                        @php
                            // 1. Initialisation des variables de boucle
                            $isLast = $loop->last; 
                            $isFirst = $loop->first;
                            $rl = $completedLegs->get($leg->id);
                            $isDone = $rl !== null;
                            
                            // 2. Logique de Zone
                            $isEnter = $leg->event_type === 'enter_zone';
                            $isExit = in_array($leg->event_type, ['exit_zone', 'leave_zone', 'exit']);
                            $hasPair = isset($zonePairs[$leg->id]);
                            
                            // 3. Détection Sous-Zone (Ex: Garage, Parking)
                            $isSubZone = (str_contains(strtolower($leg->label), 'garage') || str_contains(strtolower($leg->label), 'parking'));
                            
                            // 4. Récupération des données de performance
                            $actualSecZone = $zoneActualSec[$leg->id] ?? null;
                            $targetSecZone = $legObjectives[$leg->id] ?? null;
                            // dd($zoneActualSec, $actualSecZone, $targetSecZone);
                            
                            // 5. Calcul des couleurs
                            $ecartZone = ($actualSecZone !== null && $targetSecZone !== null) ? $actualSecZone - $targetSecZone : null;

                            $dotColor = match(true) {
                                !$isDone => 'var(--cream-dd)',
                                $ecartZone > 0 => 'var(--danger)',
                                $isDone && $actualSecZone !== null => 'var(--success)',
                                $isDone => 'var(--bordeaux)',
                                default => 'var(--muted)',
                            };
                        @endphp

                        <div style="display:flex; align-items:flex-start; flex-shrink:0; position:relative;">
                            
                            {{-- Barre de zone au-dessus (Uniquement Zone Mère) --}}
                            @if($isEnter && $hasPair && !$isSubZone)
                                <div style="position:absolute; top:-30px; left:20px; right:-20px; height:4px; 
                                            background:{{ $dotColor }}; opacity:0.2; border-radius:10px; z-index:0;">
                                    <span style="position:absolute; top:-18px; left:0; font-size:9px; font-weight:bold; color:{{ $dotColor }}; text-transform:uppercase; white-space:nowrap;">
                                        SECTEUR : {{ $leg->label }}
                                    </span>
                                </div>
                            @endif

                            <div style="width:{{ $isSubZone ? '135px' : '165px' }}; position:relative; z-index:1;">

                                {{-- Connecteur ligne + dot --}}
                                <div style="display:flex; align-items:center; margin-bottom:8px;">
                                    {{-- Ligne Gauche --}}
                                    <div style="flex:1; height:2px; background:{{ $isFirst ? 'transparent' : 'var(--cream-dd)' }};"></div>
                                    
                                    {{-- Dot --}}
                                    <div style="width:{{ $isSubZone ? '10px' : '14px' }}; height:{{ $isSubZone ? '10px' : '14px' }}; 
                                                border-radius:50%; background:{{ $isDone ? $dotColor : '#fff' }};
                                                border:2px solid {{ $isDone ? $dotColor : 'var(--cream-dd)' }}; flex-shrink:0;
                                                box-shadow: {{ $isDone ? '0 0 0 3px rgba(0,0,0,0.03)' : 'none' }};"></div>
                                    
                                    {{-- Ligne Droite --}}
                                    <div style="flex:1; height:2px; background:{{ $isLast ? 'transparent' : 'var(--cream-dd)' }};"></div>
                                </div>

                                {{-- Carte --}}
                                <div style="background:#fff; border:1px solid {{ $isSubZone ? 'var(--cream-d)' : 'var(--cream-dd)' }};
                                            border-radius:7px; padding:10px; margin:0 5px;
                                            {{ $isSubZone ? 'transform:scale(0.95); background:#fcfcfc;' : 'box-shadow:0 2px 5px rgba(0,0,0,0.04);' }}
                                            border-top:3px solid {{ $dotColor }}; transition: all 0.2s;">
                                    
                                    {{-- Header Carte (ID + Type) --}}
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                        <span style="font-family:var(--mono); font-size:9px; color:var(--muted); font-weight:bold;">T{{ $leg->order }}</span>
                                        <span style="font-size:8px; font-weight:bold; background:{{ $isSubZone ? 'var(--cream)' : 'var(--cream-d)' }}; padding:1px 5px; border-radius:4px; color:var(--ink-light);">
                                            {{ $isEnter ? 'IN' : ($isExit ? 'OUT' : 'CP') }}
                                        </span>
                                    </div>

                                    {{-- Label Étape --}}
                                    <div style="font-size:11px; font-weight:700; line-height:1.2; height:26px; overflow:hidden; color:{{ $isSubZone ? '#666' : 'var(--ink)' }}; margin-bottom:6px;" title="{{ $leg->label }}">
                                        {{ $leg->label }}
                                    </div>

                                    {{-- Temps de passage --}}
                                    @if($isDone)
                                        <div style="font-family:var(--mono); font-size:10px; color:var(--ink-light); margin-bottom:4px;">
                                            {{ $rl->occurred_at->format('H:i') }}
                                            <span style="font-size:9px; opacity:0.6;">({{ $rl->occurred_at->format('d/m') }})</span>
                                        </div>
                                    @else
                                        <div style="font-size:9px; color:var(--muted); font-style:italic;">En attente...</div>
                                    @endif

                                    {{-- SECTION PERFORMANCE (Objectif vs Réel) --}}
                                    @if($isEnter && ($targetSecZone !== null || $actualSecZone !== null))
                                        <div style="margin-top:8px; padding-top:8px; border-top:1px dashed var(--cream-dd);">
                                            
                                            {{-- Ligne Objectif (Toujours visible si défini) --}}
                                            @if($targetSecZone !== null)
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:2px;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase; font-weight:600;">Objectif:</span>
                                                    <span style="font-size: 9px; font-weight: 700; color: var(--ink);">
                                                        {{ is_numeric($targetSecZone) ? $fmt($targetSecZone) : $targetSecZone }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Ligne Réel --}}
                                            @if($actualSecZone !== null)
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:2px;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase; font-weight:600;">Réalisé:</span>
                                                    <span style="font-size: 10px; font-weight: 800; color: {{ $dotColor }};">
                                                        @durSec($actualSecZone)
                                                    </span>
                                                </div>

                                                {{-- Ligne Écart --}}
                                                @if($targetSecZone !== null && is_numeric($targetSecZone))
                                                    @php $ecart = $actualSecZone - $targetSecZone; @endphp
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; padding-top: 4px; border-top: 1px solid #f0f0f0;">
                                                        <span style="font-size: 7px; font-weight:bold; color: {{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }}; text-transform: uppercase;">
                                                            {{ $ecart > 0 ? 'Retard' : 'Avance' }}:
                                                        </span>
                                                        <span style="font-size: 9px; font-weight: 800; color: {{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                            {{ $ecart > 0 ? '+' : '' }}@durSec($ecart)
                                                        </span>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Légende simplifiée --}}
            <div style="display:flex; gap:20px; padding:12px 20px; border-top:1px solid var(--cream-d); background:rgba(0,0,0,0.01);">
                <div style="display:flex; align-items:center; gap:6px;">
                    <div style="width:8px; height:8px; border-radius:50%; background:var(--success);"></div>
                    <span style="font-size:10px; color:var(--muted); font-weight:600;">OK / Avance</span>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <div style="width:8px; height:8px; border-radius:50%; background:var(--danger);"></div>
                    <span style="font-size:10px; color:var(--muted); font-weight:600;">Hors Objectif</span>
                </div>
                <div style="margin-left:auto; font-size:10px; color:var(--muted); font-style:italic;">
                    <i class="fas fa-arrows-alt-h"></i> Défilement horizontal
                </div>
            </div>
        </div>


        {{-- ── VUE LISTE (compacte, pour beaucoup d'étapes) ──────────────────── --}}
        <div id="view-list" style="max-height:520px;overflow-y:auto;padding:16px;
            scrollbar-width:thin;scrollbar-color:var(--cream-dd) transparent;">

            @php
                $fmt = fn(?int $min) => $min === null ? '—'
                    : intdiv($min, 60) . 'h' . str_pad($min % 60, 2, '0', STR_PAD_LEFT) . 'm';
            @endphp

            @foreach($displayBlocks as $block)

                @if($block['type'] === 'checkpoint')
                    {{-- ── Checkpoint ────────────────────────────────────────────── --}}
                    <div style="display:flex;align-items:center;gap:10px;
                                padding:7px 10px;margin:3px 0;border-radius:5px;">
                        <div style="width:7px;height:7px;border-radius:50%;flex-shrink:0;
                                    background:{{ $block['rl'] ? 'var(--success)' : 'var(--cream-dd)' }};"></div>
                        <span style="font-size:12px;font-weight:600;color:{{ $block['rl'] ? 'var(--ink)' : 'var(--muted)' }};">
                            {{ $block['leg']->label }}
                        </span>
                        <span style="margin-left:auto;font-family:var(--mono);font-size:11px;color:var(--muted);">
                            {{ $block['rl']?->occurred_at?->timezone('Africa/Nairobi')->format('H:i:s') ?? '—' }}
                        </span>
                    </div>

                @elseif($block['type'] === 'zone_block')
                    {{-- ── Bloc zone (avec sous-zones éventuelles) ───────────────── --}}
                    @php
                        $isDone  = $block['enter_rl'] !== null;
                        $ecart   = $block['ecart'];
                        $hasObj  = $block['target_sec'] !== null;

                        $borderColor = !$isDone         ? 'var(--cream-dd)'
                            : ($ecart === null          ? 'var(--bordeaux)'
                            : ($ecart > 0               ? 'var(--danger)'
                                                        : 'var(--success)'));

                        $bgColor = !$isDone             ? 'var(--cream)'
                            : ($ecart === null          ? 'rgba(139,26,26,0.03)'
                            : ($ecart > 0               ? 'rgba(192,39,45,0.04)'
                                                        : 'rgba(45,122,74,0.04)'));
                    @endphp

                    <div style="border:1.5px solid {{ $borderColor }};border-radius:9px;
                                background:{{ $bgColor }};margin:10px 0;overflow:hidden;">

                        {{-- En-tête du bloc zone --}}
                        <div style="display:flex;align-items:center;gap:10px;padding:11px 14px;">

                            {{-- Dot statut --}}
                            <div style="width:10px;height:10px;border-radius:50%;flex-shrink:0;
                                        background:{{ $borderColor }};
                                        {{ $isDone ? 'box-shadow:0 0 0 3px ' . ($ecart > 0 ? 'rgba(192,39,45,0.15)' : ($ecart < 0 ? 'rgba(45,122,74,0.15)' : 'rgba(139,26,26,0.15)')) . ';' : '' }}">
                            </div>

                            {{-- Label + horaires --}}
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:700;color:var(--ink);">
                                    {{ $block['enter_leg']->label }}
                                </div>
                                <div style="display:flex;gap:10px;margin-top:3px;flex-wrap:wrap;">
                                    @if($block['enter_rl'])
                                        <span style="font-family:var(--mono);font-size:10px;
                                                    background:#fff;padding:1px 6px;border-radius:4px;
                                                    border:1px solid var(--cream-dd);color:var(--ink-light);">
                                            Entrée : {{ $block['enter_rl']->occurred_at->timezone('Africa/Nairobi')->format('H:i:s') }}
                                        </span>
                                    @endif
                                    @if($block['leave_rl'])
                                        <span style="font-family:var(--mono);font-size:10px;
                                                    background:#fff;padding:1px 6px;border-radius:4px;
                                                    border:1px solid var(--cream-dd);color:var(--ink-light);">
                                            Sortie : {{ $block['leave_rl']->occurred_at->timezone('Africa/Nairobi')->format('H:i:s') }}
                                        </span>
                                    @endif
                                    @if(!$isDone)
                                        <span style="font-size:10px;color:var(--muted);font-style:italic;">Non atteint</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Durée + objectif --}}
                            <div style="text-align:right;flex-shrink:0;min-width:80px;">
                                <div style="font-family:var(--mono);font-size:16px;font-weight:800;
                                            color:{{ $borderColor }};line-height:1;">
                                    Effectif : @durSec($block['actual_sec'])
                                </div>
                                @if($hasObj)
                                    <div style="font-size:10px;font-weight:600;margin-top:2px;
                                                color:{{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                        {{ $ecart > 0 ? '+' : '' }}@durSec($ecart)
                                    </div>
                                    <div style="font-size:16px;color:var(--muted);font-weight:800">Objectif : @durSec($block['target_sec'])</div>
                                @endif
                            </div>
                        </div>

                        {{-- ── Sous-zones imbriquées ──────────────────────────── --}}
                        @if(!empty($block['children']))
                            <div style="border-top:1px solid {{ $borderColor }}33;
                                        padding:8px 14px 10px;
                                        background:rgba(255,255,255,0.5);">

                                <div style="font-size:9px;font-weight:700;letter-spacing:0.12em;
                                            text-transform:uppercase;color:var(--muted);margin-bottom:6px;">
                                    Sous-zones
                                </div>

                                @foreach($block['children'] as $child)
                                    @php
                                        $skipped    = $child['was_skipped'] ?? false;
                                        $isDone     = !$skipped && $child['enter_rl'] !== null; 
                                        $childDone   = $child['enter_rl'] !== null;
                                        $childEcart  = $child['ecart'];
                                        $childHasObj = $child['target_sec'] !== null;

                                        $childBorder = !$childDone      ? 'var(--cream-dd)'
                                            : ($childEcart === null     ? 'var(--bordeaux)'
                                            : ($childEcart > 0          ? 'var(--danger)'
                                                                        : 'var(--success)'));
                                        $childBg = !$childDone          ? '#fff'
                                            : ($childEcart === null     ? 'rgba(139,26,26,0.02)'
                                            : ($childEcart > 0          ? 'rgba(192,39,45,0.05)'
                                                                        : 'rgba(45,122,74,0.05)'));
                                    @endphp

                                    <div style="display:flex;align-items:center;gap:10px;
                                                background:{{ $childBg }};
                                                border:1px solid {{ $childBorder }};
                                                border-radius:7px;padding:9px 12px;
                                                margin-bottom:5px;">

                                        {{-- Connecteur visuel --}}
                                        <div style="display:flex;flex-direction:column;align-items:center;
                                                    gap:1px;flex-shrink:0;">
                                            <div style="width:1px;height:6px;background:var(--cream-dd);"></div>
                                            <div style="width:7px;height:7px;border-radius:50%;
                                                        background:{{ $childBorder }};"></div>
                                            <div style="width:1px;height:6px;background:var(--cream-dd);"></div>
                                        </div>

                                        {{-- Label + horaires --}}
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:11px;font-weight:700;
                                                        color:{{ $childDone ? 'var(--ink)' : 'var(--muted)' }};">
                                                {{ $child['enter_leg']->label }}
                                            </div>
                                            <div style="display:flex;gap:8px;margin-top:2px;flex-wrap:wrap;">
                                                @if($child['enter_rl'])
                                                    <span style="font-family:var(--mono);font-size:10px;color:var(--muted);background:#fff;padding:1px 6px;border-radius:4px;
                                                    border:1px solid var(--cream-dd);color:var(--ink-light);">
                                                        Entrée {{ $child['enter_rl']->occurred_at->timezone('Africa/Nairobi')->format('H:i:s') }}
                                                    </span>
                                                @endif
                                                @if($child['leave_rl'])
                                                    <span style="font-family:var(--mono);font-size:10px;color:var(--muted);background:#fff;padding:1px 6px;border-radius:4px;
                                                    border:1px solid var(--cream-dd);color:var(--ink-light);">
                                                        Sortie {{ $child['leave_rl']->occurred_at->timezone('Africa/Nairobi')->format('H:i:s') }}
                                                    </span>
                                                @endif
                                                @if(!$childDone)
                                                    <span style="font-size:10px;color:var(--muted);font-style:italic;">Non atteint</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Durée sous-zone --}}
                                        <div style="text-align:right;flex-shrink:0;min-width:70px;">
                                            <div style="font-family:var(--mono);font-size:14px;font-weight:700;
                                                        color:{{ $childBorder }};line-height:1;">
                                                Effectif : @durSec($child['actual_sec'])
                                            </div>
                                            @if($childHasObj)
                                                <div style="font-size:10px;font-weight:600;margin-top:1px;
                                                            color:{{ $childEcart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                    {{ $childEcart > 0 ? '+' : '' }}@durSec($childEcart)
                                                </div>
                                                <div style="font-size:14px;color:var(--muted);font-weight:700">Objectif : @durSec($child['target_sec'])</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endif

            @endforeach
        </div>
    </div>

    

</div>
@endsection