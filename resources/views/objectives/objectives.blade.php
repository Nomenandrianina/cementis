@extends('layouts.app')

@section('title', 'Objectifs – ' . $circuit->name)
@section('page-title', 'Objectifs : ' . $circuit->name)

@section('topbar-actions')
    <a href="{{ route('circuits.edit', $circuit) }}" class="btn btn-ghost btn-sm">← Circuit</a>
@endsection

@section('content')

<div class="grid-2" style="gap:24px;">

    {{-- Créer un objectif --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Nouvel objectif</span></div>
        <div class="card-body">
            <form action="{{ route('circuits.objectives.store', $circuit) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Rotations cibles / mois</label>
                    <input type="number" name="target_rotations_per_month" min="1" placeholder="Ex: 8">
                </div>
                <div class="form-group">
                    <label>Durée totale cible (minutes)</label>
                    <input type="number" name="target_duration_minutes" min="1" placeholder="Ex: 2880 (= 48h)">
                </div>

                <div style="margin-bottom:12px;">
                    <label>Durée cible par étape (minutes)</label>
                    @foreach($circuit->legs as $leg)
                        <div style="display:flex;align-items:center;gap:10px;margin-top:8px;">
                            <span style="flex:1;font-size:12px;">
                                <span class="mono" style="color:var(--accent);">T{{ $leg->order }}</span>
                                {{ $leg->label }}
                            </span>
                            <input type="number" name="leg_objectives[{{ $leg->id }}]"
                                   style="width:100px;" min="1" placeholder="min">
                        </div>
                    @endforeach
                    @if($circuit->legs->isEmpty())
                        <p style="color:var(--muted);font-size:12px;">Ajoutez d'abord des étapes au circuit.</p>
                    @endif
                </div>

                <div class="form-group">
                    <label>Date d'effet</label>
                    <input type="date" name="effective_from" value="{{ date('Y-m-01') }}" required>
                </div>
                <div class="form-group">
                    <label>Date de fin (optionnel)</label>
                    <input type="date" name="effective_until">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Créer l'objectif</button>
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
                                        <span style="color:var(--text-dim);">T{{ $leg->order }} {{ $leg->label }}</span>
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
    </div>

</div>
@endsection