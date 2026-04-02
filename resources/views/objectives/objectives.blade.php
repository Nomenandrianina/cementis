@extends('layouts.app')

@section('title', 'Objectifs – ' . $circuit->name)
@section('page-title', 'Objectifs : ' . $circuit->name)

@section('topbar-actions')
    <a href="{{ route('circuits.edit', $circuit) }}" class="btn btn-ghost btn-sm">← Circuit</a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="grid-2" style="gap:24px;">

    {{-- Créer un objectif --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Nouvel objectif</span>
        </div>
        <div class="card-body">
            <form action="{{ route('circuits.objectives.store', $circuit) }}" method="POST">
                @csrf

                {{-- Rotations cibles --}}
                <div class="form-group">
                    <label>Rotations cibles / mois</label>
                    <input type="number" name="target_rotations_per_month" min="1" placeholder="Ex: 8">
                </div>

                {{-- Durée totale --}}
                <div class="form-group">
                    <label>Durée totale cible (minutes)</label>
                    <input type="number" name="target_duration_minutes" min="1" placeholder="Ex: 2880 (= 48h)">
                </div>

                
                @php
                    $legs = $circuit->legs()->orderBy('order')->get();

                    $durationInputs = [];
                    $processedLegs  = [];

                    $staticZoneLabels = ['garage', 'parking', 'depot', 'atelier', 'client'];

                    foreach ($legs as $idx => $leg) {
                        if (isset($processedLegs[$leg->id])) continue;

                        if ($leg->event_type === 'pass_checkpoint') {
                            // Pas de durée pour un checkpoint
                            $processedLegs[$leg->id] = true;
                            continue;
                        }

                        $lowerLabel = strtolower($leg->label);
                        $isStaticZone = false;
                        
                        // Vérifier si le label correspond à une zone de type garage/parking
                        foreach ($staticZoneLabels as $staticLabel) {
                            if (str_contains($lowerLabel, $staticLabel)) {
                                $isStaticZone = true;
                                break;
                            }
                        }

                        
                        if ($isStaticZone) {
                            $durationInputs[] = [
                                'label'    => "Temps d'arrêt : {$leg->label}",
                                'sublabel' => "{$leg->label}",
                                'leg_ids'  => [$leg->id],
                                'type'     => 'static_zone',
                                'key'      => "static_{$leg->id}",
                            ];
                            $processedLegs[$leg->id] = true;
                            continue;
                        }

                        if ($leg->event_type === 'enter_zone') {
                            $matchingLeave = $legs->first(fn($l) =>
                                $l->event_type === 'leave_zone' &&
                                $l->reference_id == $leg->reference_id &&
                                $l->order > $leg->order &&
                                !isset($processedLegs[$l->id])
                            );

                            $zoneName = \App\Models\Zone::find($leg->reference_id)?->name ?? $leg->label;

                            if ($matchingLeave) {
                                // Paire détectée → une seule saisie "Durée dans [zone]"
                                $durationInputs[] = [
                                    'label'    => "Durée dans la zone : {$zoneName}",
                                    'sublabel' => "{$leg->label}  →  {$matchingLeave->label}",
                                    'leg_ids'  => [$leg->id, $matchingLeave->id],
                                    'type'     => 'zone_pair',
                                    'key'      => "zone_pair_{$leg->id}_{$matchingLeave->id}",
                                ];
                                $processedLegs[$leg->id]          = true;
                                $processedLegs[$matchingLeave->id] = true;
                            }
                            // } else {
                            //     // Entrée sans sortie correspondante → durée individuelle
                            //     $durationInputs[] = [
                            //         'label'    => $leg->label,
                            //         'sublabel' => null,
                            //         'leg_ids'  => [$leg->id],
                            //         'type'     => 'zone_single',
                            //         'key'      => "leg_{$leg->id}",
                            //     ];
                            //     $processedLegs[$leg->id] = true;
                            // }
                        }

                        if ($leg->event_type === 'leave_zone' && !isset($processedLegs[$leg->id])) {
                            // Sortie sans entrée correspondante (ex: sortie initiale)
                            $durationInputs[] = [
                                'label'    => $leg->label,
                                'sublabel' => null,
                                'leg_ids'  => [$leg->id],
                                'type'     => 'zone_single',
                                'key'      => "leg_{$leg->id}",
                            ];
                            $processedLegs[$leg->id] = true;
                        }
                    }
                @endphp

                @if(!empty($durationInputs))
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);margin-bottom:14px;">
                            Durée cible par zone (minutes)
                        </div>

                        {{-- Indication checkpoints ignorés --}}
                        @if($legs->where('event_type','pass_checkpoint')->count() > 0)
                        <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(139,26,26,0.04);border:1px solid rgba(139,26,26,0.12);border-radius:7px;margin-bottom:14px;">
                            <svg width="14" height="14" fill="none" stroke="var(--bordeaux)" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span style="font-size:12px;color:var(--bordeaux);">
                                Les checkpoints (passages) n'ont pas de durée cible — ils sont simplement validés au passage.
                            </span>
                        </div>
                        @endif

                        {{-- Saisies de durée pour les zones --}}
                        @foreach($durationInputs as $input)
                            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--cream-d);">
                                <div style="flex:1;">
                                    <div style="font-size:13px;font-weight:600;color:var(--ink);">
                                        {{ $input['label'] }}
                                    </div>
                                    @if($input['sublabel'])
                                        <div style="font-size:11px;color:var(--muted);margin-top:2px;font-family:var(--mono);">
                                            {{ $input['sublabel'] }}
                                        </div>
                                    @endif
                                </div>

                                <div style="display:flex;align-items:center;gap:6px;">
                                    {{-- Pour une paire, on stocke dans les deux leg_ids --}}
                                    @foreach($input['leg_ids'] as $legId)
                                        <input type="hidden" name="_zone_input_keys[]" value="{{ $input['key'] }}">
                                    @endforeach

                                    <input
                                        type="number"
                                        name="leg_objectives[{{ $input['leg_ids'][0] }}]"
                                        {{-- Si paire, on copie la valeur dans le deuxième leg côté JS --}}
                                        @if(count($input['leg_ids']) > 1)
                                            data-mirror-to="leg_objectives[{{ $input['leg_ids'][1] }}]"
                                        @endif
                                        style="width:100px;text-align:center;"
                                        min="1"
                                        placeholder="min"
                                    >

                                    {{-- Champ miroir caché pour le leg de sortie de la paire --}}
                                    @if(count($input['leg_ids']) > 1)
                                        <input type="number" name="leg_objectives[{{ $input['leg_ids'][1] }}]"
                                            id="mirror_{{ $input['leg_ids'][1] }}" style="display:none;" min="1">
                                    @endif

                                    <span style="font-size:11px;color:var(--muted);">min</span>
                                </div>
                            </div>
                        @endforeach

                        @if(empty($durationInputs))
                            <p style="color:var(--muted);font-size:12px;">Aucune zone détectée dans ce circuit.</p>
                        @endif
                    </div>
                @endif

                @if($circuit->legs->isEmpty())
                    <div style="padding:16px;background:var(--cream);border-radius:7px;text-align:center;color:var(--muted);font-size:12px;margin-bottom:16px;">
                        Ajoutez d'abord des étapes au circuit avant de définir des objectifs.
                    </div>
                @endif

                <div class="grid-2" style="gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label>Date d'effet</label>
                        <input type="date" name="effective_from" value="{{ date('Y-m-01') }}" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Date de fin (optionnel)</label>
                        <input type="date" name="effective_until">
                    </div>
                </div>

                <div class="form-group mt-16">
                    <label>Notes</label>
                    <textarea name="notes" rows="2" placeholder="Observations, contexte…"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:4px;">Créer l'objectif</button>
            </form>
        </div>
    </div>

    {{-- Historique des objectifs --}}
    {{-- <div class="card">
        <div class="card-header"><span class="card-title">Historique</span></div>
        <div class="card-body">
            @forelse($objectives as $obj)
                <div style="background:var(--panel);border:1px solid var(--border);border-radius:4px;padding:14px;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <span class="mono" style="font-size:11px;color:var(--accent);">
                            {{ $obj->effective_from->format('d/m/Y') }}
                            @if($obj->effective_until) → {{ $obj->effective_until->format('d/m/Y') }} @else → ∞ @endif
                        </span>
                        @php $isActive = $obj->effective_from <= now() && (!$obj->effective_until || $obj->effective_until >= now()); @endphp
                        @if($isActive)
                            <span class="badge badge-success">En vigueur</span>
                        @endif
                        <div style="flex:1;"></div>
                        <form action="{{ route('circuits.objectives.destroy', [$circuit, $obj]) }}" method="POST"
                              onsubmit="return confirm('Supprimer cet objectif ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
                        <div>
                            <div style="color:var(--muted);">Rotations/mois</div>
                            <div style="font-weight:600;">{{ $obj->target_rotations_per_month ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);">Durée totale</div>
                            <div style="font-weight:600;">
                                @if($obj->target_duration_minutes)
                                    {{ intdiv($obj->target_duration_minutes, 60) }}h{{ $obj->target_duration_minutes % 60 }}m
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!empty($obj->leg_objectives))
                        <div style="margin-top:10px;border-top:1px solid var(--border);padding-top:10px;">
                            <div style="color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;">Par étape</div>
                            @foreach($circuit->legs as $leg)
                                @if(isset($obj->leg_objectives[$leg->id]))
                                    <div style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0;">
                                        <span style="color:var(--text-dim);">{{ $leg->label }}</span>
                                        <span class="mono">
                                            {{ intdiv($obj->leg_objectives[$leg->id], 60) }}h{{ $obj->leg_objectives[$leg->id] % 60 }}m
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($obj->notes)
                        <div style="margin-top:8px;font-size:11px;color:var(--muted);font-style:italic;">{{ $obj->notes }}</div>
                    @endif
                </div>
            @empty
                <p style="color:var(--muted);font-size:12px;">Aucun objectif défini.</p>
            @endforelse
        </div>
    </div> --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Historique</span></div>
        <div class="card-body">
            @forelse($objectives as $obj)
                <div style="background:var(--panel);border:1px solid var(--border);border-radius:4px;padding:14px;margin-bottom:12px;">
                    {{-- Header (Date + Badge + Delete) --}}
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <span class="mono" style="font-size:11px;color:var(--accent);">
                            {{ $obj->effective_from->format('d/m/Y') }}
                            @if($obj->effective_until) → {{ $obj->effective_until->format('d/m/Y') }} @else → ∞ @endif
                        </span>
                        @php $isActive = $obj->effective_from <= now() && (!$obj->effective_until || $obj->effective_until >= now()); @endphp
                        @if($isActive) <span class="badge badge-success">En vigueur</span> @endif
                        <div style="flex:1;"></div>
                        <form action="{{ route('circuits.objectives.destroy', [$circuit, $obj]) }}" method="POST" onsubmit="return confirm('Supprimer cet objectif ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </div>

                    {{-- Statistiques Globales --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;margin-bottom:12px;">
                        <div>
                            <div style="color:var(--muted);">Rotations/mois</div>
                            <div style="font-weight:600;">{{ $obj->target_rotations_per_month ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);">Durée totale</div>
                            <div style="font-weight:600;">
                                @if($obj->target_duration_minutes)
                                    {{ intdiv($obj->target_duration_minutes, 60) }}h{{ str_pad($obj->target_duration_minutes % 60, 2, '0', STR_PAD_LEFT) }}m
                                @else — @endif
                            </div>
                        </div>
                    </div>

                    {{-- Détails par Zone (Logique de regroupement) --}}
                    @if(!empty($obj->leg_objectives))
                        <div style="margin-top:10px;border-top:1px solid var(--border);padding-top:10px;">
                            <div style="color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;">Objectifs par zone</div>
                            
                            @php
                                $processedLegs = [];
                                $legs = $circuit->legs()->orderBy('order')->get();
                            @endphp

                            @foreach($legs as $leg)
                                @if(isset($processedLegs[$leg->id])) @continue @endif

                                @php
                                    $val = $obj->leg_objectives[$leg->id] ?? null;
                                @endphp

                                {{-- CAS 1 : Paire Entrée/Sortie --}}
                                @if($leg->event_type === 'enter_zone')
                                    @php
                                        $matchingLeave = $legs->first(fn($l) => 
                                            $l->event_type === 'leave_zone' && 
                                            $l->reference_id == $leg->reference_id && 
                                            $l->order > $leg->order &&
                                            !isset($processedLegs[$l->id])
                                        );
                                        $zoneName = \App\Models\Zone::find($leg->reference_id)?->name ?? $leg->label;
                                    @endphp

                                    @if($matchingLeave && $val)
                                        <div style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0;">
                                            <span style="color:var(--text-dim);">Durée : <strong>{{ $zoneName }}</strong></span>
                                            <span class="mono">{{ intdiv($val, 60) }}h{{ str_pad($val % 60, 2, '0', STR_PAD_LEFT) }}m</span>
                                        </div>
                                        @php $processedLegs[$matchingLeave->id] = true; @endphp
                                    @endif

                                {{-- CAS 2 : Zone statique (Garage, Parking, Dépôt) --}}
                                @elseif(Str::contains(strtolower($leg->event_type), ['garage', 'parking', 'depot']) && $val)
                                    <div style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0;">
                                        <span style="color:var(--text-dim);">Arrêt : <strong>{{ $leg->label }}</strong></span>
                                        <span class="mono">{{ intdiv($val, 60) }}h{{ str_pad($val % 60, 2, '0', STR_PAD_LEFT) }}m</span>
                                    </div>
                                @endif

                                @php $processedLegs[$leg->id] = true; @endphp
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p style="color:var(--muted);font-size:12px;">Aucun objectif défini.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection