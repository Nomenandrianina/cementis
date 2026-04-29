@extends('layouts.app')

@section('title', 'Zones')
@section('page-title', 'Zones géographiques')

@section('topbar-actions')
    <form action="{{ route('zones.sync') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn-sync">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
            Sync GPS
        </button>
    </form>
@endsection

<script>
    function toggleEditZone(id) {
        const el = document.getElementById('edit-zone-' + id);
        if (!el) return;
        const isOpen = el.style.display !== 'none';
        document.querySelectorAll('[id^="edit-zone-"]').forEach(e => {
            e.style.display = 'none';
            e.closest('.zone-row')?.classList.remove('is-editing');
        });
        if (!isOpen) {
            el.style.display = 'block';
            el.closest('.zone-row')?.classList.add('is-editing');
        }
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('[id^="edit-zone-"]') && !e.target.closest('[onclick^="toggleEditZone"]')) {
            document.querySelectorAll('[id^="edit-zone-"]').forEach(el => {
                el.style.display = 'none';
                el.closest('.zone-row')?.classList.remove('is-editing');
            });
        }
    });
</script>


@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<link rel="stylesheet" href="{{ asset('css/zone.css') }}">


<div class="zones-wrap">

    {{-- ===== FORMULAIRE ===== --}}
    <div class="z-card">
        <div class="z-card-header">
            <div class="z-card-title">
                <div class="z-card-title-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                </div>
                Ajouter une zone
            </div>
        </div>
        <div class="z-form-body">
            <form action="{{ route('zones.store') }}" method="POST" style="display:contents;">
                @csrf
                <div class="z-field">
                    <label>Nom</label>
                    <input type="text" name="name" required placeholder="Ex : Zone Tamatave">
                </div>
                <div class="z-field">
                    <label>Type</label>
                    <select name="option">
                        <option value="" disabled selected>— Sélectionner —</option>
                        @foreach(\App\Models\Zone::OPTIONS as $key => $label)
                            <option value="{{ $key }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="z-field">
                    <label>Rôle dans le circuit</label>
                    <select name="role">
                        <option value="">— Générique —</option>
                        <option value="start">Départ</option>
                        <option value="end">Arrivée</option>
                        <option value="waypoint">Étape intermédiaire</option>
                    </select>
                </div> --}}
                <div class="z-field">
                    <label>ID GPS <span style="font-weight:400;color:var(--z-muted);text-transform:none;">(optionnel)</span></label>
                    <input type="text" name="gps_zone_id" placeholder="6163">
                </div>
                <div>
                    <button type="submit" class="z-btn-primary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Créer la zone
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== LISTE ===== --}}
    <div class="z-card">
        <div class="z-card-header">
            <div class="z-card-title">
                <div class="z-card-title-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                </div>
                Zones définies
            </div>
            <span class="z-badge-count">{{ $totalCount }}</span>
        </div>

        @forelse($rootZones as $zone)
            @include('zones.row', [
                'zone'        => $zone,
                'depth'       => 0,
                'parentZones' => $parentZones,
            ])
        @empty
            <div class="z-empty">
                <div class="z-empty-icon">🗺️</div>
                <p>Aucune zone configurée.<br>Synchronisez depuis l'API GPS ou créez-en une.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection