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


    
@endsection