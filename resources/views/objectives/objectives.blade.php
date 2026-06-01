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
                    <label>Durée totale cible</label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                            <span style="font-size:10px;color:var(--muted);">H</span>
                            <input type="number" id="total_dur_h" value="0" class="total-dur-part"
                                style="width:70px;text-align:center;" min="0" placeholder="0">
                        </div>
                        <span style="color:var(--muted);margin-top:16px;">:</span>
                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                            <span style="font-size:10px;color:var(--muted);">min</span>
                            <input type="number" id="total_dur_m" value="0" class="total-dur-part"
                                style="width:70px;text-align:center;" min="0" max="59" placeholder="0">
                        </div>
                        <span style="color:var(--muted);margin-top:16px;">:</span>
                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                            <span style="font-size:10px;color:var(--muted);">sec</span>
                            <input type="number" id="total_dur_s" value="0" class="total-dur-part"
                                style="width:70px;text-align:center;" min="0" max="59" placeholder="0">
                        </div>
                    </div>
                    <input type="hidden" name="target_duration_seconds" id="target_duration_seconds">
                </div>

                {{-- Durée par zone — données préparées par le contrôleur --}}
                @if(!empty($durationInputs))
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);margin-bottom:14px;">
                            Durée cible par zone
                        </div>

                        @php $legs = $circuit->legs()->orderBy('order')->get(); @endphp

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

                        @foreach($durationInputs as $input)
                            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--cream-d);">
                                <div style="flex:1;">
                                    <div style="font-size:13px;font-weight:600;color:var(--ink);">
                                        {{ $input['label'] }}
                                        @if($input['is_or'])
                                            <span style="font-size:10px;color:#854F0B;font-weight:400;margin-left:4px;">· OU</span>
                                        @endif
                                    </div>
                                    @if($input['sublabel'])
                                        <div style="font-size:11px;color:var(--muted);margin-top:2px;font-family:var(--mono);">
                                            {{ $input['sublabel'] }}
                                        </div>
                                    @endif
                                </div>

                                <div style="display:flex;align-items:center;gap:6px;">
                                    @foreach($input['leg_ids'] as $legId)
                                        <input type="hidden" name="_zone_input_keys[]" value="{{ $input['key'] }}">
                                    @endforeach

                                    <input type="hidden"
                                        name="leg_objectives[{{ $input['leg_ids'][0] }}]"
                                        id="sec_{{ $input['leg_ids'][0] }}"
                                        @if(count($input['leg_ids']) > 1)
                                            data-mirror-to="leg_objectives[{{ $input['leg_ids'][1] }}]"
                                        @endif>

                                    @if(count($input['leg_ids']) > 1)
                                        <input type="hidden"
                                            name="leg_objectives[{{ $input['leg_ids'][1] }}]"
                                            id="mirror_{{ $input['leg_ids'][1] }}">
                                    @endif

                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                                            <span style="font-size:10px;color:var(--muted);">H</span>
                                            <input type="number" class="dur-h" value="0"
                                                data-target="sec_{{ $input['leg_ids'][0] }}"
                                                style="width:70px;text-align:center;" min="0" placeholder="0">
                                        </div>
                                        <span style="font-size:13px;color:var(--muted);margin-top:16px;">:</span>
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                                            <span style="font-size:10px;color:var(--muted);">min</span>
                                            <input type="number" class="dur-m" value="0"
                                                data-target="sec_{{ $input['leg_ids'][0] }}"
                                                style="width:70px;text-align:center;" min="0" max="59" placeholder="0">
                                        </div>
                                        <span style="font-size:13px;color:var(--muted);margin-top:16px;">:</span>
                                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                                            <span style="font-size:10px;color:var(--muted);">sec</span>
                                            <input type="number" class="dur-s" value="0"
                                                data-target="sec_{{ $input['leg_ids'][0] }}"
                                                style="width:70px;text-align:center;" min="0" max="59" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($circuit->legs->isEmpty())
                    <div style="padding:16px;background:var(--cream);border-radius:7px;text-align:center;color:var(--muted);font-size:12px;margin-bottom:16px;">
                        Ajoutez d'abord des étapes au circuit avant de définir des objectifs.
                    </div>
                @endif

                <div class="grid-2" style="gap:12px;display:none;">
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
    <div class="card">
        <div class="card-header"><span class="card-title">Historique</span></div>
        <div class="card-body">
            @forelse($objectives as $obj)
                <div style="background:var(--panel);border:1px solid var(--border);border-radius:4px;padding:14px;margin-bottom:12px;">

                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div style="flex:1;"></div>
                        <form action="{{ route('circuits.objectives.destroy', [$circuit, $obj]) }}" method="POST" onsubmit="return confirm('Supprimer cet objectif ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;margin-bottom:12px;">
                        <div>
                            <div style="color:var(--muted);">Rotations/mois</div>
                            <div style="font-weight:600;">{{ $obj->target_rotations_per_month ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="color:var(--muted);">Durée totale</div>
                            <div style="font-weight:600;">
                                @if($obj->target_duration_seconds)
                                    @durSec($obj->target_duration_seconds)
                                @else — @endif
                            </div>
                        </div>
                    </div>

                    @if(!empty($obj->leg_objectives))
                        <div style="margin-top:10px;border-top:1px solid var(--border);padding-top:10px;">
                            <div style="color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;">Objectifs par zone</div>

                            @foreach($durationInputs as $input)
                                @php
                                    $val = null;
                                    foreach ($input['all_slot_ids'] as $sid) {
                                        $val = $obj->leg_objectives[$sid] ?? null;
                                        if ($val) break;
                                    }
                                    if (!$val) {
                                        $val = $obj->leg_objectives[$input['leg_ids'][1]] ?? null;
                                    }
                                @endphp

                                @if($val)
                                    <div style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0;">
                                        <span style="color:var(--text-dim);">
                                            Durée : <strong>{{ str_replace('Durée dans la zone : ', '', $input['label']) }}</strong>
                                            @if($input['is_or'])
                                                <span style="font-size:10px;color:#854F0B;"> · OU</span>
                                            @endif
                                        </span>
                                        @durSec($val)
                                    </div>
                                @endif
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
<script>
    document.querySelectorAll('.dur-h, .dur-m, .dur-s').forEach(input => {
        input.addEventListener('input', function () {
            const targetId = this.dataset.target;
            const h = parseInt(document.querySelector(`.dur-h[data-target="${targetId}"]`).value) || 0;
            const m = parseInt(document.querySelector(`.dur-m[data-target="${targetId}"]`).value) || 0;
            const s = parseInt(document.querySelector(`.dur-s[data-target="${targetId}"]`).value) || 0;
            const totalSeconds = h * 3600 + m * 60 + s;
            const hidden = document.getElementById(targetId);
            if (hidden) {
                hidden.value = totalSeconds > 0 ? totalSeconds : '';
            }
        });
    });

    document.querySelectorAll('.total-dur-part').forEach(input => {
        input.addEventListener('input', function () {
            const h = parseInt(document.getElementById('total_dur_h').value) || 0;
            const m = parseInt(document.getElementById('total_dur_m').value) || 0;
            const s = parseInt(document.getElementById('total_dur_s').value) || 0;
            const total = h * 3600 + m * 60 + s;
            document.getElementById('target_duration_seconds').value = total > 0 ? total : '';
        });
    });
</script>
@endsection