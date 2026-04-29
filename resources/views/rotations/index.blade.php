@extends('layouts.app')
@section('title', 'Rotations')
@section('page-title', 'Rotations')

<link rel="stylesheet" href="{{ asset('css/rotation_index.css') }}">
@section('content')
<div class="container-fluid pt-3">
    <div class="card shadow-sm rounded-lg overflow-hidden">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <div class="rot-page-header mb-0">
                <h3 class="card-title mb-0" style="font-size: 1.5rem; font-weight: 600;">
                    Rotations
                    <small class="text-muted d-block" style="font-size: 0.9rem;">
                        Gestion des tournées véhicules
                    </small>
                </h3>
            </div>

            <div class="d-flex align-items-center ml-auto">
                <button type="button" class="rot-btn mr-2" data-toggle="modal" data-target="#modalFilter">
                    <i class="fas fa-filter mr-1"></i> Filtrer
                </button>

                <a href="{{ route('reports.index') }}" class="rot-btn mr-2">
                    <svg class="rot-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/>
                    </svg>
                    Rapports
                </a>
                <button type="button" class="rot-btn rot-btn-primary" data-toggle="modal" data-target="#modalCalcul" style="margin-left: 1rem;">
                    <svg class="rot-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Calculer les rotations
                </button>
            </div>
        </div>
    </div>
    {{-- Statistiques rapides --}}
    @php
        $total      = $rotations->total();
        $completed  = $rotations->getCollection()->where('status','completed')->count();
        $inProgress = $rotations->getCollection()->where('status','in_progress')->count();
        $cancelled  = $rotations->getCollection()->where('status','cancelled')->count();
    @endphp

    <div class="rot-stats mb-18">
        <div class="rot-stat">
            <div class="rot-stat-label">Total rotations</div>
            <div class="rot-stat-value">{{ $total }}</div>
            <div class="rot-stat-sub">Ce mois-ci</div>
        </div>
        <div class="rot-stat stat-success">
            <div class="rot-stat-label">Complètes</div>
            <div class="rot-stat-value">{{ $completed }}</div>
            <div class="rot-stat-sub">
                {{ $total > 0 ? round($completed / $total * 100) : 0 }}% du total
            </div>
        </div>
        <div class="rot-stat stat-info">
            <div class="rot-stat-label">En cours</div>
            <div class="rot-stat-value">{{ $inProgress }}</div>
            <div class="rot-stat-sub">Actives maintenant</div>
        </div>
        <div class="rot-stat stat-danger">
            <div class="rot-stat-label">Annulées</div>
            <div class="rot-stat-value">{{ $cancelled }}</div>
            <div class="rot-stat-sub">
                {{ $total > 0 ? round($cancelled / $total * 100) : 0 }}% du total
            </div>
        </div>
    </div>

    {{-- Calcul de rotations --}}
    <div class="modal fade" id="modalCalcul" tabindex="-1" role="dialog" aria-labelledby="modalCalculLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document"> <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border: none; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center" id="modalCalculLabel" style="font-weight: 600; letter-spacing: 0.5px;">
                        <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <svg class="text-primary" style="width:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        Calculer les rotations
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                
                <form action="{{ route('rotations.calculate') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px; background-color: #e3f2fd; color: #0d47a1;">
                            <small><i class="fas fa-info-circle mr-1"></i> Sélectionnez les paramètres pour générer les rapports mensuels des véhicules.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Circuit de transport</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-route text-primary"></i></span>
                                    </div>
                                    <select name="circuit_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;" required>
                                        <option value="">— Sélectionner —</option>
                                        @foreach($circuits as $c)
                                            <option value="{{ $c->id }}" {{ old('circuit_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Véhicule spécifique</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-truck text-primary"></i></span>
                                    </div>
                                    <select name="vehicle_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les camions</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Période (Année)</label>
                                <input type="number" name="year" class="form-control shadow-none" 
                                    style="border-radius: 8px; height: 45px;"
                                    value="{{ old('year', date('Y')) }}"
                                    min="2020" max="2099" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Période (Mois)</label>
                                <select name="month" class="form-control shadow-none" style="border-radius: 8px; height: 45px;" required>
                                    @foreach(range(1,12) as $m)
                                        <option value="{{ $m }}" @selected($m == (old('month', date('n'))))>
                                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 bg-white">
                        <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal" style="text-decoration: none;">Annuler</button>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px; height: 48px; font-weight: 600; background: #1a73e8;">
                            <i class="fas fa-play-circle mr-2"></i> Lancer le calcul
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFilter" tabindex="-1" role="dialog" aria-labelledby="modalFilterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border: none; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center" id="modalFilterLabel" style="font-weight: 600; letter-spacing: 0.5px;">
                        <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <svg class="text-primary" style="width:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </div>
                        Filtrer les rotations
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                
                <form method="GET" action="{{ route('rotations.index') }}">
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px; background-color: #e3f2fd; color: #0d47a1;">
                            <small><i class="fas fa-filter mr-1"></i> Utilisez les options ci-dessous pour affiner la liste des rotations affichées.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Par Circuit</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-route text-primary"></i></span>
                                    </div>
                                    <select name="circuit_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les circuits</option>
                                        @foreach($circuits as $c)
                                            <option value="{{ $c->id }}" {{ request('circuit_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Par Camion</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-truck text-primary"></i></span>
                                    </div>
                                    <select name="vehicle_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les camions</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Période (Mois)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    </div>
                                    <input type="month" name="month" class="form-control border-left-0 shadow-none" 
                                        style="border-radius: 0 8px 8px 0; height: 45px;"
                                        value="{{ request('month') }}">
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Statut du voyage</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-info-circle text-primary"></i></span>
                                    </div>
                                    <select name="status" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les statuts</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complète</option>
                                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 bg-white d-flex justify-content-between">
                        <a href="{{ route('rotations.index') }}" class="btn btn-link text-muted font-weight-bold" style="text-decoration: none;">
                            <i class="fas fa-undo mr-1"></i> Réinitialiser
                        </a>
                        <div>
                            <button type="button" class="btn btn-link text-muted font-weight-bold mr-3" data-dismiss="modal" style="text-decoration: none;">Annuler</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px; height: 48px; font-weight: 600; background: #1a73e8;">
                                <i class="fas fa-check-circle mr-2"></i> Appliquer les filtres
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Filtres --}}

    {{-- Table des rotations --}}
    <div class="rot-card">
        <div class="rot-card-header">
            <span class="rot-card-title">Liste des rotations</span>
            <span class="rot-badge rot-badge-muted">{{ $rotations->total() }} entrée(s)</span>
        </div>

        <div class="rot-table-wrap">
            <table class="rot-table">
                <thead>
                    <tr>
                        <th>Camion</th>
                        <th>Circuit</th>
                        <th>Mois compté</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Effectif / Objectif</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rotations as $rotation)
                        @php
                            $objSeconds = $rotation->circuit->currentObjective()->target_duration_seconds ?? null;
                            // $objSeconds = $objMinutes ? $objMinutes * 60 : null;
                            $dur        = $rotation->duration_seconds;

                            $pct      = ($objSeconds && $dur) ? min(round($dur / $objSeconds * 100), 140) : 0;
                            $barClass = $pct > 105 ? 'over' : ($pct >= 90 ? 'good' : '');
                        @endphp
                        <tr>
                            <td>
                                <div class="rot-vehicle-name">{{ $rotation->rvehicule->name }}</div>
                                @if($rotation->rvehicule->plate_number)
                                    <div class="rot-vehicle-plate">{{ $rotation->rvehicule->plate_number }}</div>
                                @endif
                            </td>

                            <td>
                                <span class="mono" style="font-size:12px;font-weight:600">
                                    {{ $rotation->circuit->code }}
                                </span>
                            </td>

                            <td>
                                <span class="mono">{{ $rotation->counted_month ?? '—' }}</span>
                            </td>

                            <td>
                                <span class="mono">
                                    {{ $rotation->started_at?->timezone('Africa/Nairobi')->format('d/m/Y H:i:s') ?? '—' }}
                                </span>
                            </td>

                            <td>
                                <span class="mono">
                                    {{ $rotation->completed_at?->timezone('Africa/Nairobi')->format('d/m/Y H:i:s') ?? '—' }}
                                </span>
                            </td>

                            <td>
                                {{-- @if($obj)
                                    <div class="rot-dur-cell">
                                        <span class="rot-dur-text">
                                            @if($dur)
                                                {{ intdiv($dur, 60) }}h{{ str_pad($dur % 60, 2, '0', STR_PAD_LEFT) }}m
                                            @else
                                                —
                                            @endif
                                            <span class="rot-dur-obj">
                                                / {{ intdiv($obj, 60) }}h{{ str_pad($obj % 60, 2, '0', STR_PAD_LEFT) }}m
                                            </span>
                                        </span>
                                        <div class="rot-bar-track">
                                            <div class="rot-bar-fill {{ $barClass }}"
                                                style="width:{{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="mono">—</span>
                                @endif --}}
                                @if($objSeconds)
                                    <div class="rot-dur-cell">
                                        <span class="rot-dur-text">
                                            @if($dur)
                                                @durSec($dur)
                                            @else
                                                —
                                            @endif
                                            <span class="rot-dur-obj">
                                                / @durSec($objSeconds)
                                            </span>
                                        </span>
                                        <div class="rot-bar-track">
                                            <div class="rot-bar-fill {{ $barClass }}"
                                                style="width:{{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="mono">—</span>
                                @endif
                            </td>

                            <td>
                                @switch($rotation->status)
                                    @case('completed')
                                        <span class="rot-badge rot-badge-success">Complète</span>
                                        @break
                                    @case('in_progress')
                                        <span class="rot-badge rot-badge-info">En cours</span>
                                        @break
                                    @case('cancelled')
                                        <span class="rot-badge rot-badge-danger">Annulée</span>
                                        @break
                                    @default
                                        <span class="rot-badge rot-badge-muted">{{ $rotation->status }}</span>
                                @endswitch
                            </td>

                            <td>
                                <div class="rot-actions">
                                    <a href="{{ route('rotations.show', $rotation) }}"
                                    class="rot-btn rot-btn-sm">Détail</a>
                                    <form action="{{ route('rotations.destroy', $rotation) }}"
                                        method="POST"
                                        onsubmit="return confirm('Supprimer cette rotation ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rot-btn rot-btn-sm rot-btn-danger">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;color:var(--muted);padding:40px 16px;font-size:13px;">
                                Aucune rotation trouvée. Lancez un calcul ci-dessus.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rotations->hasPages())
            <div class="rot-pagination">
                <span class="rot-pg-info">
                    Page {{ $rotations->currentPage() }} sur {{ $rotations->lastPage() }}
                    — {{ $rotations->total() }} résultats
                </span>
                <div class="rot-pg-btns">
                    @if($rotations->onFirstPage())
                        <span class="rot-pg-btn" style="opacity:.35;cursor:default">‹</span>
                    @else
                        <a class="rot-pg-btn" href="{{ $rotations->previousPageUrl() }}">‹</a>
                    @endif

                    @foreach($rotations->getUrlRange(
                        max(1, $rotations->currentPage() - 2),
                        min($rotations->lastPage(), $rotations->currentPage() + 2)
                    ) as $page => $url)
                        <a class="rot-pg-btn {{ $page == $rotations->currentPage() ? 'active' : '' }}"
                        href="{{ $url }}">{{ $page }}</a>
                    @endforeach

                    @if($rotations->hasMorePages())
                        <a class="rot-pg-btn" href="{{ $rotations->nextPageUrl() }}">›</a>
                    @else
                        <span class="rot-pg-btn" style="opacity:.35;cursor:default">›</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection