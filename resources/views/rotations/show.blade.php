@extends('layouts.app')

@section('title', 'Rotation #' . $rotation->id)
@section('page-title', 'Rotation #' . $rotation->id)

@section('topbar-actions')
    <a href="{{ route('rotations.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
@endsection
{{-- @push('scripts') --}}
    <script>
    let currentView = 'horizontal';

    function toggleView() {
        const h      = document.getElementById('view-horizontal');
        const l      = document.getElementById('view-list');
        const btn    = document.getElementById('view-toggle');

        if (currentView === 'horizontal') {
            h.style.display   = 'none';
            l.style.display   = 'block';
            btn.textContent   = '⟷ Timeline';
            currentView       = 'list';
        } else {
            h.style.display   = 'block';
            l.style.display   = 'none';
            btn.textContent   = '☰ Liste';
            currentView       = 'horizontal';
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
                    $targetDur = $objective?->target_duration_minutes;
                    $actualDur = $rotation->duration_minutes;
                @endphp

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;margin-bottom:16px;">
                    <div>
                        <div class="stat-label">Début (T1)</div>
                        <div class="mono" style="font-size:14px;color:var(--text);">
                            {{ $rotation->started_at?->format('d/m/Y') }}<br>
                            <span style="color:var(--accent);font-size:18px;font-family:var(--head);font-weight:700;">
                                {{ $rotation->started_at?->format('H:i') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">Fin (T5)</div>
                        <div class="mono" style="font-size:14px;color:var(--text);">
                            {{ $rotation->completed_at?->format('d/m/Y') ?? '—' }}<br>
                            <span style="color:var(--accent);font-size:18px;font-family:var(--head);font-weight:700;">
                                {{ $rotation->completed_at?->format('H:i') ?? '—' }}
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
                                {{ intdiv($actualDur, 60) }}h{{ $actualDur % 60 }}m
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
                            <span>Objectif : {{ intdiv($targetDur, 60) }}h{{ $targetDur % 60 }}m</span>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $pct > 100 ? 'over' : 'good' }}" style="width:{{ min($pct, 100) }}%;"></div>
                        </div>
                        @php $ecart = $actualDur - $targetDur; @endphp
                        <div style="text-align:right;font-size:12px;margin-top:6px;" class="{{ $ecart > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $ecart > 0 ? '+' : '' }}{{ intdiv($ecart, 60) }}h{{ abs($ecart % 60) }}m vs objectif
                        </div>
                    </div>
                @elseif(!$targetDur)
                    <p style="font-size:12px;color:var(--muted);">Aucun objectif défini pour ce circuit.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline des étapes --}}
    {{-- <div class="card">
        <div class="card-header">
            <span class="card-title">Étapes de la rotation</span>
            <span class="badge badge-muted">{{ $rotation->rotationLegs->count() }} / {{ $rotation->circuit->legs->count() }}</span>
        </div>
        <div class="card-body">
            @php
                $legObjectives = $objective?->leg_objectives ?? [];
                $allLegs = $rotation->circuit->legs;
                $completedLegs = $rotation->rotationLegs->keyBy('circuit_leg_id');
            @endphp

            <div class="timeline">
                @foreach($allLegs as $leg)
                    @php
                        $rl = $completedLegs->get($leg->id);
                        $isDone = $rl !== null;
                        $targetMin = $legObjectives[$leg->id] ?? null;
                        $actualMin = $rl?->duration_since_previous_minutes;
                    @endphp
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $isDone ? 'done' : '' }}"></div>
                        <div class="timeline-label">{{ $leg->label }}</div>
                        @if($isDone)
                            <div class="timeline-time">{{ $rl->occurred_at->format('d/m/Y H:i:s') }}</div>
                            @if($actualMin !== null)
                                <div class="timeline-duration">
                                    +{{ intdiv($actualMin, 60) }}h{{ $actualMin % 60 }}m depuis étape précédente
                                    @if($targetMin)
                                        @php $e = $actualMin - $targetMin; @endphp
                                        <span class="{{ $e > 0 ? 'text-danger' : 'text-success' }}">
                                            (objectif : {{ intdiv($targetMin,60) }}h{{ $targetMin%60 }}m,
                                            {{ $e > 0 ? '+' : '' }}{{ intdiv($e,60) }}h{{ abs($e%60) }}m)
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="timeline-duration text-muted">Non atteint</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div> --}}
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
        {{-- <div id="view-horizontal">
            <div style="overflow-x:auto;padding:24px 20px 20px;
                        scrollbar-width:thin;scrollbar-color:var(--cream-dd) transparent;">
                <div style="display:flex;align-items:flex-start;gap:0;min-width:max-content;position:relative;">

                    @foreach($allLegs as $idx => $leg)
                        @php
                            $rl        = $completedLegs->get($leg->id);
                            $isDone    = $rl !== null;
                            $targetMin = $legObjectives[$leg->id] ?? null;
                            $actualMin = $rl?->duration_since_previous_minutes;
                            $isLast    = $loop->last;

                            // Couleur selon état et dépassement
                            $dotColor = match(true) {
                                !$isDone             => 'var(--cream-dd)',
                                $targetMin && $actualMin && ($actualMin > $targetMin) => 'var(--danger)',
                                $isDone              => 'var(--success)',
                                default              => 'var(--bordeaux)',
                            };
                            $cardBg = match(true) {
                                !$isDone             => 'var(--cream)',
                                $targetMin && $actualMin && ($actualMin > $targetMin) => 'rgba(192,39,45,0.04)',
                                $isDone              => 'rgba(45,122,74,0.04)',
                                default              => '#fff',
                            };
                        @endphp

                        <div style="display:flex;align-items:flex-start;flex-shrink:0;">

                            
                            <div style="width:160px;position:relative;">

                                
                                <div style="display:flex;align-items:center;margin-bottom:8px;">
                                   
                                    @if(!$loop->first)
                                        <div style="flex:1;height:2px;
                                                    background:{{ $isDone ? ($dotColor) : 'var(--cream-dd)' }};
                                                    margin-right:-1px;"></div>
                                    @else
                                        <div style="width:20px;"></div>
                                    @endif

                                    
                                    <div style="width:14px;height:14px;border-radius:50%;flex-shrink:0;
                                                background:{{ $isDone ? $dotColor : '#fff' }};
                                                border:2px solid {{ $isDone ? $dotColor : 'var(--cream-dd)' }};
                                                box-shadow:{{ $isDone ? '0 0 0 3px rgba(45,122,74,0.12)' : 'none' }};
                                                z-index:1;position:relative;">
                                    </div>

                                    
                                    @if(!$loop->last)
                                        <div style="flex:1;height:2px;
                                                    background:var(--cream-dd);
                                                    margin-left:-1px;"></div>
                                    @else
                                        <div style="width:20px;"></div>
                                    @endif
                                </div>

                                
                                <div style="background:{{ $cardBg }};border:1px solid var(--cream-dd);
                                            border-radius:7px;padding:10px;margin:0 4px;
                                            border-top:2px solid {{ $isDone ? $dotColor : 'var(--cream-dd)' }};">

                                    
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                                        <span style="font-family:var(--mono);font-size:9px;font-weight:700;
                                                    color:{{ $isDone ? $dotColor : 'var(--muted)' }};">
                                            T{{ $leg->order }}
                                        </span>
                                        <span style="font-size:8px;font-weight:700;text-transform:uppercase;
                                                    letter-spacing:0.06em;color:var(--muted);
                                                    background:var(--cream-d);padding:1px 5px;border-radius:10px;">
                                            @if(str_contains($leg->event_type,'pass'))
                                                CP
                                            @elseif($leg->event_type === 'enter_zone')
                                                IN
                                            @else
                                                OUT
                                            @endif
                                        </span>
                                    </div>

                                    
                                    <div style="font-size:11px;font-weight:600;color:var(--ink);
                                                line-height:1.3;margin-bottom:5px;
                                                display:-webkit-box;-webkit-line-clamp:2;
                                                -webkit-box-orient:vertical;overflow:hidden;"
                                        title="{{ $leg->label }}">
                                        {{ $leg->label }}
                                    </div>

                                    @if($isDone)
                                        
                                        <div style="font-family:var(--mono);font-size:10px;
                                                    color:var(--ink-light);margin-bottom:3px;">
                                            {{ $rl->occurred_at->format('H:i') }}
                                            <span style="color:var(--muted);font-size:9px;">
                                                {{ $rl->occurred_at->format('d/m') }}
                                            </span>
                                        </div>

                                        @if($actualMin !== null)
                                            
                                            <div style="font-size:10px;color:var(--muted);">
                                                +{{ intdiv($actualMin,60) }}h{{ str_pad($actualMin%60,2,'0',STR_PAD_LEFT) }}m
                                            </div>

                                            @if($targetMin)
                                                @php $ecart = $actualMin - $targetMin; @endphp
                                                <div style="font-size:10px;font-weight:600;margin-top:2px;
                                                            color:{{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                    {{ $ecart > 0 ? '+' : '' }}{{ intdiv($ecart,60) }}h{{ str_pad(abs($ecart%60),2,'0',STR_PAD_LEFT) }}m
                                                    vs obj.
                                                </div>
                                            @endif
                                        @endif
                                    @else
                                        <div style="font-size:10px;color:var(--muted);font-style:italic;">Non atteint</div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach

                </div>
            </div>

            
            <div style="display:flex;gap:16px;padding:8px 20px 14px;border-top:1px solid var(--cream-d);">
                @foreach([
                    ['var(--success)', 'Dans les temps'],
                    ['var(--danger)',  'Dépassement'],
                    ['var(--cream-dd)','Non atteint'],
                ] as [$color, $label])
                    <div style="display:flex;align-items:center;gap:5px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></div>
                        <span style="font-size:10px;color:var(--muted);">{{ $label }}</span>
                    </div>
                @endforeach
                <div style="margin-left:auto;font-size:10px;color:var(--muted);">← Faites défiler →</div>
            </div>
        </div> --}}
        {{-- <div id="view-horizontal">
            <div style="overflow-x:auto; padding:40px 20px 20px; scrollbar-width:thin; scrollbar-color:var(--cream-dd) transparent;">
                <div style="display:flex; align-items:flex-start; gap:0; min-width:max-content; position:relative;">

                    @foreach($allLegs as $idx => $leg)
                        @php
                            $isLast = $loop->last; 
                            $isFirst = $loop->first;
                            $rl = $completedLegs->get($leg->id);
                            $isDone = $rl !== null;
                            
                            // Détection Zone Mère vs Sous-Zone (Logique basée sur votre structure)
                            $isEnter = $leg->event_type === 'enter_zone';
                            $isExit = in_array($leg->event_type, ['exit_zone', 'leave_zone']);
                            $hasPair = $isEnter && isset($zonePairs[$leg->id]);
                            
                            // Critère sous-zone (à adapter selon vos labels)
                            $isSubZone = (str_contains(strtolower($leg->label), 'garage') || str_contains(strtolower($leg->label), 'parking'));
                            
                            // Calcul de l'écart pour la couleur
                            $actualMinZone = $isEnter ? ($zoneActualMin[$leg->id] ?? null) : null;
                            $targetMinZone = $isEnter ? ($legObjectives[$leg->id] ?? null) : null;
                            $ecartZone = ($actualMinZone !== null && $targetMinZone !== null) ? $actualMinZone - $targetMinZone : null;

                            $dotColor = match(true) {
                                !$isDone => 'var(--cream-dd)',
                                $ecartZone > 0 => 'var(--danger)',
                                $isDone => 'var(--success)',
                                default => 'var(--muted)',
                            };
                        @endphp

                        <div style="display:flex; align-items:flex-start; flex-shrink:0; position:relative;">
                            
                            
                            @if($isEnter && $hasPair && !$isSubZone)
                                <div style="position:absolute; top:-25px; left:20px; right:-20px; height:4px; 
                                            background:{{ $dotColor }}; opacity:0.3; border-radius:10px; z-index:0;">
                                    <span style="position:absolute; top:-15px; left:0; font-size:9px; font-weight:bold; color:{{ $dotColor }}; text-transform:uppercase;">
                                        Zone: {{ $leg->label }}
                                    </span>
                                </div>
                            @endif

                            <div style="width:{{ $isSubZone ? '130px' : '160px' }}; position:relative; z-index:1;">

                                
                                <div style="display:flex; align-items:center; margin-bottom:8px;">
                                    <div style="flex:1; height:2px; background:{{ $idx == 0 ? 'transparent' : 'var(--cream-dd)' }};"></div>
                                    
                                    <div style="width:{{ $isSubZone ? '10px' : '14px' }}; height:{{ $isSubZone ? '10px' : '14px' }}; 
                                                border-radius:50%; background:{{ $isDone ? $dotColor : '#fff' }};
                                                border:2px solid {{ $isDone ? $dotColor : 'var(--cream-dd)' }}; flex-shrink:0;"></div>
                                    
                                    <div style="flex:1; height:2px; background:{{ $isLast ? 'transparent' : 'var(--cream-dd)' }};"></div>
                                </div>

                                
                                <div style="background:#fff; border:1px solid {{ $isSubZone ? 'var(--cream-d)' : 'var(--cream-dd)' }};
                                            border-radius:7px; padding:8px; margin:0 4px;
                                            {{ $isSubZone ? 'transform:scale(0.9);' : 'box-shadow:0 2px 4px rgba(0,0,0,0.02);' }}
                                            border-top:3px solid {{ $dotColor }};">
                                    
                                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                        <span style="font-size:8px; color:var(--muted); font-weight:bold;">T{{ $leg->order }}</span>
                                        <span style="font-size:7px; background:{{ $isSubZone ? 'var(--cream)' : 'var(--cream-d)' }}; padding:1px 4px; border-radius:4px;">
                                            {{ $isEnter ? 'IN' : ($isExit ? 'OUT' : 'CP') }}
                                        </span>
                                    </div>

                                    <div style="font-size:10px; font-weight:700; line-height:1.2; height:24px; overflow:hidden; color:{{ $isSubZone ? '#666' : 'var(--ink)' }};">
                                        {{ $leg->label }}
                                    </div>

                                    @if($isDone)
                                        <div style="font-size:9px; color:var(--ink-light); margin-top:4px;">
                                            {{ $rl->occurred_at->format('H:i') }}
                                        </div>
                                        
                                        
                                        @if($hasPair && $actualMinZone !== null)
                                            <div style="margin-top:5px; padding-top:5px; border-top:1px dashed #eee;">
                                               
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase;">DUREE:</span>
                                                    <span style="font-size: 10px; font-weight: 800; color: {{ $dotColor }};">
                                                        {{ $fmt($actualMinZone) }}
                                                    </span>
                                                </div>

                                                
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1px;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase;">OBJ:</span>
                                                    <span style="font-size: 9px; font-weight: 600; color: #64748b;">
                                                        {{ $fmt($targetMinZone) }}
                                                    </span>
                                                </div>

                                                
                                                @php $ecart = $actualMinZone - $targetMinZone; @endphp
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1px; border-top: 1px solid #f8f9fa; padding-top: 1px;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase;">{{ $ecart > 0 ? 'RETARD' : 'AVANCE' }}:</span>
                                                    <span style="font-size: 9px; font-weight: 700; color: {{ $dotColor }};">
                                                        {{ $ecart > 0 ? '+' : '' }}{{ $fmt($ecart) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div> --}}
        @php
            $currentParentId = null;
            // Pré-calculer les paires entrée→sortie et les durées
            $zonePairs    = []; // enter_leg_id => exit_leg_id
            $zoneActualMin = []; // enter_leg_id => minutes réels
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
                            $zoneActualMin[$entry['leg_id']] = (int) $rl->occurred_at->diffInMinutes($entry['occurred_at']);
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
        <div id="view-horizontal">
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
                            $actualMinZone = $zoneActualMin[$leg->id] ?? null;
                            $targetMinZone = $legObjectives[$leg->id] ?? null;
                            // dd($zoneActualMin, $actualMinZone, $targetMinZone);
                            
                            // 5. Calcul des couleurs
                            $ecartZone = ($actualMinZone !== null && $targetMinZone !== null) ? $actualMinZone - $targetMinZone : null;

                            $dotColor = match(true) {
                                !$isDone => 'var(--cream-dd)',
                                $ecartZone > 0 => 'var(--danger)',
                                $isDone && $actualMinZone !== null => 'var(--success)',
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
                                    @if($isEnter && ($targetMinZone !== null || $actualMinZone !== null))
                                        <div style="margin-top:8px; padding-top:8px; border-top:1px dashed var(--cream-dd);">
                                            
                                            {{-- Ligne Objectif (Toujours visible si défini) --}}
                                            @if($targetMinZone !== null)
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:2px;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase; font-weight:600;">Objectif:</span>
                                                    <span style="font-size: 9px; font-weight: 700; color: var(--ink);">
                                                        {{ is_numeric($targetMinZone) ? $fmt($targetMinZone) : $targetMinZone }}
                                                    </span>
                                                </div>
                                            @endif

                                            {{-- Ligne Réel --}}
                                            @if($actualMinZone !== null)
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:2px;">
                                                    <span style="font-size: 7px; color: var(--muted); text-transform: uppercase; font-weight:600;">Réalisé:</span>
                                                    <span style="font-size: 10px; font-weight: 800; color: {{ $dotColor }};">
                                                        {{ $fmt($actualMinZone) }}
                                                    </span>
                                                </div>

                                                {{-- Ligne Écart --}}
                                                @if($targetMinZone !== null && is_numeric($targetMinZone))
                                                    @php $ecart = $actualMinZone - $targetMinZone; @endphp
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px; padding-top: 4px; border-top: 1px solid #f0f0f0;">
                                                        <span style="font-size: 7px; font-weight:bold; color: {{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }}; text-transform: uppercase;">
                                                            {{ $ecart > 0 ? 'Retard' : 'Avance' }}:
                                                        </span>
                                                        <span style="font-size: 9px; font-weight: 800; color: {{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                            {{ $ecart > 0 ? '+' : '' }}{{ $fmt($ecart) }}
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
        {{-- <div id="view-list" style="display:none;">
            <div style="max-height:420px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--cream-dd) transparent;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead style="position:sticky;top:0;z-index:10;">
                        <tr style="background:var(--cream);">
                            <th style="padding:8px 16px;text-align:left;font-size:9px;font-weight:700;
                                    letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);
                                    border-bottom:1px solid var(--cream-dd);"></th>
                            <th style="padding:8px 16px;text-align:left;font-size:9px;font-weight:700;
                                    letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);
                                    border-bottom:1px solid var(--cream-dd);">Étape</th>
                            <th style="padding:8px 16px;text-align:left;font-size:9px;font-weight:700;
                                    letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);
                                    border-bottom:1px solid var(--cream-dd);">Heure</th>
                            <th style="padding:8px 16px;text-align:right;font-size:9px;font-weight:700;
                                    letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);
                                    border-bottom:1px solid var(--cream-dd);">Durée</th>
                            <th style="padding:8px 16px;text-align:right;font-size:9px;font-weight:700;
                                    letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);
                                    border-bottom:1px solid var(--cream-dd);">Objectif</th>
                            <th style="padding:8px 16px;text-align:right;font-size:9px;font-weight:700;
                                    letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);
                                    border-bottom:1px solid var(--cream-dd);">Écart</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allLegs as $leg)
                            @php
                                $rl        = $completedLegs->get($leg->id);
                                $isDone    = $rl !== null;
                                $targetMin = $legObjectives[$leg->id] ?? null;
                                $actualMin = $rl?->duration_since_previous_minutes;
                                $ecart     = ($targetMin && $actualMin !== null) ? $actualMin - $targetMin : null;
                            @endphp
                            <tr style="border-bottom:1px solid var(--cream-d);
                                    background:{{ !$isDone ? 'transparent' : ($ecart > 0 ? 'rgba(192,39,45,0.02)' : 'rgba(45,122,74,0.02)') }};">
                                <td style="padding:9px 16px;">
                                    <div style="display:flex;align-items:center;gap:7px;">
                                        <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;
                                                    background:{{ $isDone ? ($ecart > 0 ? 'var(--danger)' : 'var(--success)') : 'var(--cream-dd)' }};"></div>
                                        <span style="font-family:var(--mono);font-size:10px;font-weight:600;
                                                    color:{{ $isDone ? 'var(--bordeaux)' : 'var(--muted)' }};">
                                        </span>
                                    </div>
                                </td>
                                <td style="padding:9px 16px;">
                                    <div style="font-weight:600;color:{{ $isDone ? 'var(--ink)' : 'var(--muted)' }};">
                                        {{ $leg->label }}
                                    </div>
                                    <div style="font-size:10px;color:var(--muted);margin-top:1px;">
                                        @if(str_contains($leg->event_type,'pass')) checkpoint
                                        @elseif($leg->event_type==='enter_zone') entrée zone
                                        @else sortie zone @endif
                                    </div>
                                </td>
                                <td style="padding:9px 16px;font-family:var(--mono);font-size:11px;color:var(--ink-light);">
                                    @if($isDone)
                                        {{ $rl->occurred_at->format('H:i:s') }}<br>
                                        <span style="color:var(--muted);font-size:10px;">{{ $rl->occurred_at->format('d/m/Y') }}</span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding:9px 16px;text-align:right;font-family:var(--mono);font-size:11px;">
                                    @if($actualMin !== null)
                                        +{{ intdiv($actualMin,60) }}h{{ str_pad($actualMin%60,2,'0',STR_PAD_LEFT) }}m
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding:9px 16px;text-align:right;font-family:var(--mono);
                                        font-size:11px;color:var(--muted);">
                                    @if($targetMin)
                                        {{ intdiv($targetMin,60) }}h{{ str_pad($targetMin%60,2,'0',STR_PAD_LEFT) }}m
                                    @else —
                                    @endif
                                </td>
                                <td style="padding:9px 16px;text-align:right;font-family:var(--mono);font-size:11px;">
                                    @if($ecart !== null)
                                        <span style="font-weight:700;color:{{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                            {{ $ecart > 0 ? '+' : '' }}{{ intdiv($ecart,60) }}h{{ str_pad(abs($ecart%60),2,'0',STR_PAD_LEFT) }}m
                                        </span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> --}}
        

        

        {{--<div id="view-list" style="display:none;">
            <div style="max-height:520px;overflow-y:auto;scrollbar-width:thin;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead style="position:sticky;top:0;z-index:10;">
                        <tr style="background:var(--cream);">
                            <th style="padding:8px 12px;width:24px;border-bottom:1px solid var(--cream-dd);"></th>
                            <th style="padding:8px 12px;text-align:left;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--cream-dd);">Étape</th>
                            <th style="padding:8px 12px;text-align:left;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--cream-dd);">Heure</th>
                            <th style="padding:8px 12px;text-align:right;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--cream-dd);">Durée effectuée</th>
                            <th style="padding:8px 12px;text-align:right;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--cream-dd);">Objectif</th>
                            <th style="padding:8px 12px;text-align:right;font-size:9px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--cream-dd);">Écart</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allLegs as $leg)
                            @php
                                $rl      = $completedLegs->get($leg->id);
                                $isDone  = $rl !== null;
                                $isEnter = $leg->event_type === 'enter_zone';
                                $isExit  = in_array($leg->event_type, ['exit_zone', 'leave_zone']);
                                $isPass  = str_contains($leg->event_type, 'pass');

                               
                                $isPairedExit = in_array($leg->id, $pairedExitIds);

                               
                                $hasPair   = $isEnter && isset($zonePairs[$leg->id]);
                                $isFinalEntry = $isEnter && !in_array($leg->id, $pairedEnterIds);

                                $actualMin = $isEnter ? ($zoneActualMin[$leg->id] ?? null) : null;
                                $raw       = $isEnter ? ($legObjectives[$leg->id] ?? $legObjectives[(string)$leg->id] ?? null) : null;
                                $targetMin = ($raw !== null && $raw !== 'null') ? (int)$raw : null;
                                $ecart     = ($hasPair && $actualMin !== null && $targetMin !== null)
                                                ? $actualMin - $targetMin
                                                : null;

                               
                                $rowBg = ($isEnter || ($isExit && $isPairedExit))
                                    ? ($ecart === null ? 'transparent'
                                        : ($ecart > 0 ? 'rgba(192,39,45,0.025)' : 'rgba(45,122,74,0.025)'))
                                    : 'transparent';

                                // Dot color
                                $dotColor = !$isDone ? 'var(--cream-dd)'
                                    : ($ecart === null ? 'var(--muted)'
                                        : ($ecart > 0 ? 'var(--danger)' : 'var(--success)'));

                                
                                $tdPadding = $hasPair
                                    ? '9px 12px 3px'
                                    : ($isPairedExit ? '3px 12px 9px' : '7px 12px');

                                
                                $borderBottom = $hasPair
                                    ? 'none'
                                    : '1px solid var(--cream-d)';
                            @endphp

                            @if($isPairedExit)
                                
                                <tr style="background:{{ $rowBg }};border-bottom:1px solid var(--cream-d);">
                                    <td style="padding:{{ $tdPadding }};">
                                        <div style="width:7px;height:7px;border-radius:50%;background:var(--muted);"></div>
                                    </td>
                                    <td style="padding:{{ $tdPadding }};">
                                        <div style="font-weight:600;color:{{ $isDone ? 'var(--ink)' : 'var(--muted)' }};">{{ $leg->label }}</div>
                                        <div style="font-size:10px;color:var(--muted);margin-top:1px;">sortie zone</div>
                                    </td>
                                    <td style="padding:{{ $tdPadding }};font-family:var(--mono);font-size:11px;color:var(--ink-light);">
                                        @if($isDone)
                                            {{ $rl->occurred_at->format('H:i:s') }}<br>
                                            <span style="color:var(--muted);font-size:10px;">{{ $rl->occurred_at->format('d/m/Y') }}</span>
                                        @else
                                            <span style="color:var(--muted);">—</span>
                                        @endif
                                    </td>
                                
                                </tr>

                            @else
                                
                                <tr style="background:{{ $rowBg }};border-bottom:{{ $borderBottom }};">
                                    <td style="padding:{{ $tdPadding }};">
                                        <div style="width:7px;height:7px;border-radius:50%;background:{{ $dotColor }};"></div>
                                    </td>
                                    <td style="padding:{{ $tdPadding }};">
                                        <div style="font-weight:600;color:{{ $isDone ? 'var(--ink)' : 'var(--muted)' }};">{{ $leg->label }}</div>
                                        <div style="font-size:10px;color:var(--muted);margin-top:1px;">
                                            @if($isEnter) entrée zone
                                            @elseif($isExit) sortie zone
                                            @else checkpoint @endif
                                        </div>
                                    </td>
                                    <td style="padding:{{ $tdPadding }};font-family:var(--mono);font-size:11px;color:var(--ink-light);">
                                        @if($isDone)
                                            {{ $rl->occurred_at->format('H:i:s') }}<br>
                                            <span style="color:var(--muted);font-size:10px;">{{ $rl->occurred_at->format('d/m/Y') }}</span>
                                        @else
                                            <span style="color:var(--muted);">—</span>
                                        @endif
                                    </td>

                                    @if($hasPair)
                                    
                                        <td rowspan="2" style="padding:7px 12px;text-align:right;font-family:var(--mono);font-size:11px;border-left:2px solid var(--cream-dd);vertical-align:middle;">
                                            @if($actualMin !== null)
                                                <span style="font-weight:700;color:{{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                    {{ $fmt($actualMin) }}
                                                </span>
                                            @else
                                                <span style="color:var(--muted);">—</span>
                                            @endif
                                        </td>
                                        <td rowspan="2" style="padding:7px 12px;text-align:right;font-family:var(--mono);font-size:11px;color:var(--muted);vertical-align:middle;">
                                            {{ $targetMin !== null ? $fmt($targetMin) : '—' }}
                                        </td>
                                        <td rowspan="2" style="padding:7px 12px;text-align:right;font-family:var(--mono);font-size:11px;vertical-align:middle;">
                                            @if($ecart !== null)
                                                <span style="font-weight:700;color:{{ $ecart > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                    {{ $ecart > 0 ? '+' : '-' }}{{ $fmt($ecart) }}
                                                </span>
                                            @else
                                                <span style="color:var(--muted);">—</span>
                                            @endif
                                        </td>

                                    @else
                                        
                                        <td style="padding:{{ $tdPadding }};text-align:right;font-family:var(--mono);font-size:11px;">
                                            @if($isFinalEntry && $isDone)
                                                <span style="color:var(--muted);font-size:10px;font-style:italic;">en cours…</span>
                                            @else
                                                <span style="color:var(--muted);">—</span>
                                            @endif
                                        </td>
                                        <td style="padding:{{ $tdPadding }};text-align:right;font-family:var(--mono);font-size:11px;color:var(--muted);">
                                            {{ ($isFinalEntry && $targetMin !== null) ? $fmt($targetMin) : '—' }}
                                        </td>
                                        <td style="padding:{{ $tdPadding }};text-align:right;font-family:var(--mono);font-size:11px;">
                                            <span style="color:var(--muted);">—</span>
                                        </td>
                                    @endif
                                </tr>
                            @endif

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> --}}

        <div id="view-list" style="display:none;max-height:520px; overflow-y:auto; padding: 10px; font-family: sans-serif;">
            <div style="position: relative; border-left: 2px solid #e2e8f0; margin-left: 20px; padding-left: 20px;">
                
                @foreach($allLegs as $leg)
                    @php
                        $isSubZone = false;
                        $rl      = $completedLegs->get($leg->id);
                        $isDone  = $rl !== null;
                        $isEnter = $leg->event_type === 'enter_zone';
                        $isExit  = in_array($leg->event_type, ['exit_zone', 'leave_zone']);
                        $isPass  = str_contains($leg->event_type, 'pass');

                        
                        $isPairedExit = in_array($leg->id, $pairedExitIds);

                        
                        $hasPair   = $isEnter && isset($zonePairs[$leg->id]);
                        $isFinalEntry = $isEnter && !in_array($leg->id, $pairedEnterIds);

                        $actualMin = $isEnter ? ($zoneActualMin[$leg->id] ?? null) : null;
                        $raw       = $isEnter ? ($legObjectives[$leg->id] ?? $legObjectives[(string)$leg->id] ?? null) : null;
                        $targetMin = ($raw !== null && $raw !== 'null') ? (int)$raw : null;
                        $ecart     = ($hasPair && $actualMin !== null && $targetMin !== null)
                                        ? $actualMin - $targetMin
                                        : null;

                        
                        $rowBg = ($isEnter || ($isExit && $isPairedExit))
                            ? ($ecart === null ? 'transparent'
                                : ($ecart > 0 ? 'rgba(192,39,45,0.025)' : 'rgba(45,122,74,0.025)'))
                            : 'transparent';

                        // Dot color
                        $dotColor = !$isDone ? 'var(--cream-dd)'
                            : ($ecart === null ? 'var(--muted)'
                                : ($ecart > 0 ? 'var(--danger)' : 'var(--success)'));

                        
                        $tdPadding = $hasPair
                            ? '9px 12px 3px'
                            : ($isPairedExit ? '3px 12px 9px' : '7px 12px');

                        
                        $borderBottom = $hasPair
                            ? 'none'
                            : '1px solid var(--cream-d)';
                        
                        $color = $ecart > 0 ? '#ef4444' : '#22c55e'; // Rouge ou Vert
                        $bgColor = $ecart > 0 ? '#fef2f2' : '#f0fdf4';
                        if ($isEnter && $hasPair) {
                            // Si le label contient "Garage" ou "Parking" (ou si vous avez un critère spécifique)
                            if (str_contains(strtolower($leg->label), 'garage') || str_contains(strtolower($leg->label), 'parking')) {
                                $isSubZone = true;
                            }
                        }
                    @endphp

                    @if($isEnter && $hasPair)
                        <div style="
                            background: {{ $bgColor }}; 
                            border: 1px solid {{ $color }}44; 
                            border-radius: 8px; 
                            padding: 12px; 
                            /* On réduit la marge et la largeur pour les sous-zones */
                            margin: {{ $isSubZone ? '5px 0 5px 30px' : '15px 0' }}; 
                            width: {{ $isSubZone ? 'calc(100% - 40px)' : 'auto' }};
                            position: relative;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            box-shadow: {{ $isSubZone ? 'none' : '0 2px 4px rgba(0,0,0,0.02)' }};
                        ">
                            <div style="flex: 1;">
                                <div style="font-size: 11px; font-weight: 700; color: #334155;">
                                    {{ $leg->label }}
                                </div>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    <span style="background: #ffffffaa; padding: 1px 4px; border-radius: 3px;">
                                        In: {{ $rl?->occurred_at->format('H:i:s') }}
                                    </span>
                                    @if($isDone && isset($zonePairs[$leg->id]))
                                        @php 
                                            $exitLeg = $allLegs->firstWhere('id', $zonePairs[$leg->id]);
                                            $exitRl = $completedLegs->get($exitLeg->id);
                                        @endphp
                                        <span style="margin-left: 5px; background: #ffffffaa; padding: 1px 4px; border-radius: 3px;">
                                            Out: {{ $exitRl?->occurred_at->format('H:i:s') ?? '--:--' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div style="min-width: 80px; text-align: right; border-left: 1px solid {{ $color }}33; padding-left: 12px;">
                                <div style="font-size: 14px; font-weight: 800; color: {{ $color }}; line-height: 1;">
                                    {{ $fmt($actualMin) }}
                                </div>
                                <div style="font-size: 10px; font-weight: 600; color: {{ $color }}; margin: 2px 0;">
                                    {{ $ecart > 0 ? '+' : '' }}{{ $fmt($ecart) }}
                                </div>
                                <div style="font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Obj: {{ $fmt($targetMin) }}
                                </div>
                            </div>
                        </div>

                    @elseif(!$isEnter && !$isPairedExit)
                        {{-- CHECKPOINTS (CP) hors zone --}}
                        <div style="padding: 8px 0 8px 10px; font-size: 11px; color: #64748b; display: flex; align-items: center;">
                            <div style="width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; margin-right: 10px;"></div>
                            <strong>{{ $leg->label }}</strong>
                            <span style="margin-left: auto; font-family: monospace;">{{ $rl?->occurred_at?->format('H:i:s') ?? '--:--' }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    

</div>
@endsection