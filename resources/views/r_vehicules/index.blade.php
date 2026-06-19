{{-- @extends('layouts.app')

@section('title', 'Camions')
@section('page-title', 'Camions')

@section('topbar-actions')
    <form action="{{ route('vehicles.sync') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">
            ↺ Synchroniser depuis GPS
        </button>
    </form>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Immatriculation</th>
                    <th>Modèle</th>
                    <th>IMEI</th>
                    <th>SIM</th>
                    <th>Rotations</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td style="font-weight:600;">{{ $vehicle->name }}</td>
                        <td class="mono">{{ $vehicle->plate_number ?? '—' }}</td>
                        <td style="color:var(--muted);">{{ $vehicle->model ?? '—' }}</td>
                        <td class="mono" style="font-size:11px;color:var(--muted);">{{ $vehicle->imei }}</td>
                        <td class="mono" style="font-size:11px;">{{ $vehicle->sim_number ?? '—' }}</td>
                        <td>{{ $vehicle->rotations_count }}</td>
                        <td>
                            @if($vehicle->active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-muted">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('vehicles.toggle', $vehicle) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-ghost btn-sm">
                                    {{ $vehicle->active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;color:var(--muted);padding:32px;">
                            Aucun camion. Synchronisez depuis l'API GPS.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
    
@endsection --}}

@extends('layouts.app')

@section('title', 'Camions')
@section('page-title', 'Camions')

@section('topbar-actions')
    <form action="{{ route('vehicles.sync') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">
            ↺ Synchroniser depuis GPS
        </button>
    </form>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; gap: 20px; flex-wrap: wrap;">
    
    <div class="card" style="width: 200px; padding: 12px 15px; margin: 0;">
        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">Total Flotte</div>
        <div style="font-size: 22px; font-weight: 700; color: var(--dark);">{{ $vehicles->total() }}</div>
    </div>

    <form action="{{ request()->url() }}" method="GET" style="margin: 0; flex-grow: 1; max-width: 400px;">
        <div class="search-group">
            <input 
                type="text" 
                name="search" 
                value="{{ $search ?? '' }}" 
                placeholder="Rechercher un camion, immatriculation, IMEI..."
                class="search-input"
            >
            @if($search)
                <a href="{{ request()->url() }}" class="search-clear-btn" title="Effacer la recherche">✕</a>
            @endif
            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 0 6px Block 6px 0; height: 38px; padding: 0 15px;">
                Filtrer
            </button>
        </div>
    </form>

</div>

<div class="card">
    <div class="table-wrap">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Immatriculation</th>
                    <th>Modèle</th>
                    <th>IMEI / SIM</th>
                    <th style="text-align: center;">Rotations</th>
                    <th style="text-align: center;">Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td style="font-weight: 600; vertical-align: middle;">{{ $vehicle->name }}</td>
                        <td class="mono" style="vertical-align: middle;">
                            <span class="badge-immat">{{ $vehicle->plate_number ?? '—' }}</span>
                        </td>
                        <td style="color: var(--muted); vertical-align: middle;">{{ $vehicle->model ?? '—' }}</td>
                        <td style="vertical-align: middle;">
                            <div style="font-size: 11px; font-family: monospace; color: var(--muted);">IMEI: {{ $vehicle->imei }}</div>
                            <div style="font-size: 11px; font-family: monospace; color: #a0aec0;">SIM: {{ $vehicle->sim_number ?? '—' }}</div>
                        </td>
                        <td style="text-align: center; vertical-align: middle; font-weight: bold;">
                            {{ $vehicle->rotations_count }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if($vehicle->active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-muted">Inactif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--muted); padding: 40px 20px;">
                            @if($search)
                                Aucun résultat trouvé pour la recherche "<strong>{{ $search }}</strong>".
                            @else
                                Aucun camion enregistré. Synchronisez depuis l'API GPS.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($vehicles->hasPages())
        <div class="pagination-container">
            {{ $vehicles->links() }}
        </div>
    @endif
</div>

<style>
    /* Design de la barre de recherche intégrée */
    .search-group {
        display: flex;
        align-items: center;
        position: relative;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        transition: border-color 0.2s;
    }
    .search-group:focus-within {
        border-color: #4a5568;
    }
    .search-input {
        border: none !important;
        background: transparent !important;
        padding: 8px 35px 8px 12px !important;
        font-size: 13px;
        flex-grow: 1;
        outline: none !important;
        height: 36px;
        box-shadow: none !important;
    }
    .search-clear-btn {
        position: absolute;
        right: 80px;
        color: #a0aec0;
        text-decoration: none !important;
        font-size: 12px;
        cursor: pointer;
        padding: 5px;
    }
    .search-clear-btn:hover {
        color: #e53e3e;
    }
    
    /* Styles généraux conservés */
    .badge-immat {
        background-color: #f7fafc;
        border: 1px solid #e2e8f0;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
    .pagination-container {
        padding: 15px 20px;
        border-top: 1px solid #edf2f7;
        background-color: #fcfcfc;
    }
    .pagination-container nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection