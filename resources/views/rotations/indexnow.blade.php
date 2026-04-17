@extends('layouts.app')
@section('title', 'Rotations')
@section('page-title', 'Rotations')

@section('topbar-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/>
        </svg>
        Rapports
    </a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
{{-- Calcul de rotations --}}
<div class="card mb-16">
    <div class="card-header">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span class="card-title">Calculer les rotations</span>
    </div>
    <div class="card-body">
        <form action="{{ route('rotations.calculate') }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns: 2fr 2fr 1fr 1fr auto; gap:12px; align-items:end;">
                <div class="form-group" style="margin:0">
                    <label>Circuit</label>
                    <select name="circuit_id" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($circuits as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Camion (optionnel)</label>
                    <select name="vehicle_id">
                        <option value="">— Tous les camions —</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Année</label>
                    <input type="number" name="year" value="{{ date('Y') }}" min="2020" max="2099" required>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Mois</label>
                    <select name="month" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" @selected($m == date('n'))>
                                {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Calculer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-16">
    <div class="card-body" style="padding:14px 18px;">
        <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin:0; min-width:180px;">
                <label>Circuit</label>
                <select name="circuit_id">
                    <option value="">Tous</option>
                    @foreach($circuits as $c)
                        <option value="{{ $c->id }}" @selected(request('circuit_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0; min-width:160px;">
                <label>Camion</label>
                <select name="vehicle_id">
                    <option value="">Tous</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(request('vehicle_id') == $v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0; min-width:120px;">
                <label>Mois (YYYY-MM)</label>
                <input type="text" name="month" value="{{ request('month') }}" placeholder="{{ date('Y-m') }}">
            </div>
            <div class="form-group" style="margin:0; min-width:130px;">
                <label>Statut</label>
                <select name="status">
                    <option value="">Sélectionner un statut</option>
                    <option value="completed" @selected(request('status') === 'completed')>Complète</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Annulée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-ghost btn-sm">Filtrer</button>
            <a href="{{ route('rotations.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
        </form>
    </div>
</div>

{{-- Table des rotations --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Liste des rotations</span>
        <span class="badge badge-muted">{{ $rotations->total() }} entrée(s)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Camion</th>
                    <th>Circuit</th>
                    <th>Mois compté</th>
                    <th>Début </th>
                    <th>Fin</th>
                    <th>Objectif</th>
                    <th>Effectif</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rotations as $rotation)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $rotation->rvehicule->name }}</div>
                            @if($rotation->rvehicule->plate_number)
                                <div style="font-size:11px;color:var(--muted);" class="mono">{{ $rotation->rvehicule->plate_number }}</div>
                            @endif
                        </td>
                        <td>{{ $rotation->circuit->code }}</td>
                        <td class="mono">{{ $rotation->counted_month ?? '—' }}</td>
                        <td class="mono">{{ $rotation->started_at?->timezone('Africa/Nairobi')->format('d/m/Y H:i:s') ?? '—' }}</td>
                        <td class="mono">{{ $rotation->completed_at?->timezone('Africa/Nairobi')->format('d/m/Y H:i:s') ?? '—' }}</td>
                        <td class="mono">
                            @if($rotation->circuit->currentObjective()->target_duration_minutes)
                                {{ intdiv($rotation->circuit->currentObjective()->target_duration_minutes, 60) }}h{{ $rotation->circuit->currentObjective()->target_duration_minutes % 60 }}m
                            @else
                                —
                            @endif
                        </td>
                        <td class="mono">
                            @if($rotation->duration_minutes)
                                {{ intdiv($rotation->duration_minutes, 60) }}h{{ $rotation->duration_minutes % 60 }}m
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @switch($rotation->status)
                                @case('completed')
                                    <span class="badge badge-success">Complète</span>
                                    @break
                                @case('in_progress')
                                    <span class="badge badge-blue">En cours</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge badge-danger">Annulée</span>
                                    @break
                                @default
                                    <span class="badge badge-muted">{{ $rotation->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('rotations.show', $rotation) }}" class="btn btn-ghost btn-sm">Détail</a>
                                <form action="{{ route('rotations.destroy', $rotation) }}" method="POST" onsubmit="return confirm('Supprimer cette rotation ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;color:var(--muted);padding:32px;">
                            Aucune rotation trouvée. Lancez un calcul ci-dessus.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rotations->hasPages())
        <div style="padding:14px 18px;border-top:1px solid var(--border);">
            {{ $rotations->links() }}
        </div>
    @endif
</div>
@endsection