@extends('layouts.app')

@section('title', 'Zones')
@section('page-title', 'Zones géographiques')

@section('topbar-actions')
    <form action="{{ route('zones.sync') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-ghost btn-sm">↺ Sync GPS</button>
    </form>
@endsection
@push('scripts')
<script>
    function toggleEditZone(id) {
        const el = document.getElementById('edit-zone-' + id);
        if (!el) return;
        const isOpen = el.style.display !== 'none';
        document.querySelectorAll('[id^="edit-zone-"]').forEach(e => e.style.display = 'none');
        if (!isOpen) el.style.display = 'block';
    }
    // Fermer en cliquant ailleurs
    document.addEventListener('click', e => {
        if (!e.target.closest('[id^="edit-zone-"]') && !e.target.closest('[onclick^="toggleEditZone"]')) {
            document.querySelectorAll('[id^="edit-zone-"]').forEach(el => el.style.display = 'none');
        }
    });
</script>
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
            <span class="badge badge-muted">{{ $totalCount }}</span>
        </div>
        
        @forelse($rootZones as $zone)
            @include('zones.row', [
                'zone'        => $zone,
                'depth'       => 0,
                'parentZones' => $parentZones,
            ])
        @empty
            <div style="text-align:center;color:var(--muted);padding:40px;font-size:13px;">
                Aucune zone. Synchronisez depuis l'API GPS.
            </div>
        @endforelse
    </div>
</div>
@endsection