@extends('layouts.app')

@section('title', 'Zones')
@section('page-title', 'Zones géographiques')

@section('topbar-actions')
    <form action="{{ route('zones.sync') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-ghost btn-sm">↺ Sync GPS</button>
    </form>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="grid-2" style="gap:24px;">


    {{-- Formulaire --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Ajouter une zone</span></div>
        <div class="card-body">
            <form action="{{ route('zones.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="name" required placeholder="Ex: Zone Tamatave">
                </div>
                <div class="form-group">
                    <label>Rôle dans le circuit</label>
                    <select name="role">
                        <option value="">— Générique —</option>
                        <option value="start">Départ</option>
                        <option value="end">Arrivée</option>
                        <option value="waypoint">Étape intermédiaire</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID GPS (optionnel)</label>
                    <input type="text" name="gps_zone_id" placeholder="6163">
                </div>
                <button type="submit" class="btn btn-primary">Créer</button>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Zones définies</span>
            <span class="badge badge-muted">{{ $zones->count() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Rôle</th>
                        <th>GPS ID</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zones as $zone)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @if($zone->color)
                                        <div style="width:12px;height:12px;border-radius:3px;background:{{ $zone->color }};flex-shrink:0;"></div>
                                    @endif
                                    <span style="font-weight:600;">{{ $zone->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($zone->role)
                                    <span class="badge badge-blue">{{ $zone->role }}</span>
                                @else
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </td>
                            <td class="mono" style="font-size:11px;color:var(--muted);">{{ $zone->gps_zone_id ?? '—' }}</td>
                            <td>
                                @if($zone->active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-muted">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('zones.destroy', $zone) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;color:var(--muted);padding:24px;">
                                Aucune zone. Synchronisez depuis l'API GPS.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection