@extends('layouts.app')

@section('title', 'Checkpoints')
@section('page-title', 'Points de contrôle')

@section('topbar-actions')
    <form action="{{ route('checkpoints.sync') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-ghost btn-sm">↺ Sync GPS</button>
    </form>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="grid-2" style="gap:24px;">

    {{-- Formulaire création --}}
    <div class="card">
        <div class="card-header"><span class="card-title">Ajouter un checkpoint</span></div>
        <div class="card-body">
            <form action="{{ route('checkpoints.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="name" required placeholder="Check point Ambodimita">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="2"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                    <div class="form-group" style="margin:0;">
                        <label>Latitude</label>
                        <input type="number" name="lat" step="0.0000001" required placeholder="-18.865963">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Longitude</label>
                        <input type="number" name="lng" step="0.0000001" required placeholder="47.486343">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Rayon (km)</label>
                        <input type="number" name="radius" step="0.001" value="0.1" required>
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Checkpoints définis</span>
            <span class="badge badge-muted">{{ $checkpoints->count() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Lat</th>
                        <th>Lng</th>
                        <th>Rayon</th>
                        <th>GPS ID</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checkpoints as $cp)
                        <tr>
                            <td style="font-weight:600;">{{ $cp->name }}</td>
                            <td class="mono" style="font-size:11px;">{{ $cp->lat }}</td>
                            <td class="mono" style="font-size:11px;">{{ $cp->lng }}</td>
                            <td class="mono">{{ $cp->radius }} km</td>
                            <td class="mono" style="color:var(--muted);font-size:11px;">{{ $cp->gps_marker_id ?? '—' }}</td>
                            <td>
                                <form action="{{ route('checkpoints.destroy', $cp) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--muted);padding:24px;">
                                Aucun checkpoint. Synchronisez depuis l'API GPS.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection