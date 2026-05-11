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
    <button type="button" class="btn-add-zone" onclick="openCreateModal()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajouter une zone
    </button>
@endsection

{{-- ===== SCRIPTS ===== --}}
<script>
    /* ---------- Modal création ---------- */
    function openCreateModal() {
        document.getElementById('modal-create-zone').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('input-zone-name').focus(), 50);
    }

    function closeCreateModal() {
        document.getElementById('modal-create-zone').style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeCreateModal();
    });

    /* ---------- Edit inline ---------- */
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

    /* ---------- Expand/collapse sous-zones ---------- */
    function toggleChildren(id) {
        const children = document.querySelectorAll('.children-of-' + id);
        const btn = document.getElementById('expand-btn-' + id);
        const icon = document.getElementById('expand-icon-' + id);
        const isOpen = btn?.dataset.open === '1';

        children.forEach(el => {
            el.style.display = isOpen ? 'none' : '';
        });

        if (btn) btn.dataset.open = isOpen ? '0' : '1';
        if (icon) icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
    }

    /* ---------- Recherche live ---------- */
    function filterZones() {
        const q = document.getElementById('zone-search').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.zone-row');

        if (!q) {
            rows.forEach(r => r.style.display = '');
            return;
        }

        rows.forEach(row => {
            const name = row.querySelector('.zone-name')?.textContent.toLowerCase() || '';
            row.style.display = name.includes(q) ? '' : 'none';
        });
    }
</script>

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<link rel="stylesheet" href="{{ asset('css/zone_new.css') }}">

{{-- ===== MODAL CRÉATION ===== --}}
<div id="modal-create-zone"
     style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(10,14,30,0.45); align-items:center; justify-content:center; padding:20px;"
     onclick="if(event.target===this) closeCreateModal()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <div class="modal-title-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                Nouvelle zone
            </div>
            <button type="button" class="modal-close" onclick="closeCreateModal()" aria-label="Fermer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form action="{{ route('zones.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="z-field modal-full">
                        <label>Nom de la zone</label>
                        <input id="input-zone-name" type="text" name="name" required placeholder="Ex : Zone Tamatave">
                    </div>
                    <div class="z-field">
                        <label>Type</label>
                        <select name="option">
                            <option value="" disabled selected>— Sélectionner —</option>
                            @foreach(\App\Models\Zone::OPTIONS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="z-field">
                        <label>Zone parente</label>
                        <select name="parent_id">
                            <option value="">— Aucune (racine) —</option>
                            @foreach($parentZones as $pz)
                                <option value="{{ $pz->id }}">{{ $pz->parent_id ? '└ ' : '' }}{{ $pz->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="z-field">
                        <label>ID GPS <span class="label-optional">(optionnel)</span></label>
                        <input type="text" name="gps_zone_id" placeholder="6163">
                    </div>
                    <div class="z-field">
                        <label>Actif</label>
                        <select name="active">
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ze-btn-cancel" onclick="closeCreateModal()">Annuler</button>
                <button type="submit" class="ze-btn-save">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Créer la zone
                </button>
            </div>
        </form>
    </div>
</div>

<div class="zones-wrap">

    {{-- ===== LISTE ===== --}}
    <div class="z-card">
        <div class="z-card-header">
            <div class="z-card-title">
                <div class="z-card-title-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                </div>
                Zones définies
            </div>
            <div class="z-header-right">
                <span class="z-badge-count">{{ $totalCount }}</span>
                <div class="z-searchbar">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input id="zone-search" type="text" placeholder="Rechercher une zone…" oninput="filterZones()">
                </div>
            </div>
        </div>

        {{-- Onglets filtres --}}
        <div class="z-filter-tabs">
            <a href="{{ request()->fullUrlWithQuery(['filter' => null]) }}"
               class="z-tab {{ !request('filter') ? 'active' : '' }}">
                Toutes
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'roots']) }}"
               class="z-tab {{ request('filter') === 'roots' ? 'active' : '' }}">
                Racines <span class="z-tab-count">{{ $rootZones->total() }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'obligatoire']) }}"
               class="z-tab {{ request('filter') === 'obligatoire' ? 'active' : '' }}">
                Obligatoires
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'optionnel']) }}"
               class="z-tab {{ request('filter') === 'optionnel' ? 'active' : '' }}">
                Optionnelles
            </a>
        </div>

        {{-- Bandeau info si >200 zones --}}
        @if($totalCount > 200)
        <div class="z-perf-hint">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $totalCount }} zones — affichage paginé. Cliquez sur <strong>▶</strong> pour développer les sous-zones.
        </div>
        @endif

        {{-- Liste des zones racines --}}
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

        {{-- Pagination --}}
        @if($rootZones->hasPages())
            <div class="z-pagination">
                <span class="z-pagination-info">
                    {{ $rootZones->firstItem() }}–{{ $rootZones->lastItem() }} sur {{ $rootZones->total() }} zones racines
                </span>
                <div class="z-pagination-nav">
                    {{-- Précédent --}}
                    @if($rootZones->onFirstPage())
                        <span class="z-page-btn disabled">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"/></svg>
                        </span>
                    @else
                        <a href="{{ $rootZones->previousPageUrl() }}" class="z-page-btn">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 18 9 12 15 6"/></svg>
                        </a>
                    @endif

                    {{-- Pages --}}
                    @foreach($rootZones->getUrlRange(1, $rootZones->lastPage()) as $page => $url)
                        @if($rootZones->lastPage() > 7)
                            @if($page === 1 || $page === $rootZones->lastPage() || abs($page - $rootZones->currentPage()) <= 1)
                                <a href="{{ $url }}" class="z-page-btn {{ $page === $rootZones->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                            @elseif(abs($page - $rootZones->currentPage()) === 2)
                                <span class="z-page-btn disabled">…</span>
                            @endif
                        @else
                            <a href="{{ $url }}" class="z-page-btn {{ $page === $rootZones->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Suivant --}}
                    @if($rootZones->hasMorePages())
                        <a href="{{ $rootZones->nextPageUrl() }}" class="z-page-btn">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    @else
                        <span class="z-page-btn disabled">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>
@endsection