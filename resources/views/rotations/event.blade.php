<link rel="stylesheet" href="{{ asset('css/event.css') }}">
@extends('layouts.app')
@section('title', 'Événements GPS')
@section('page-title', 'Visualisation des événements GPS')
<meta name="route-fetch-events" content="{{ route('rotations.event.fetch') }}">
<style>
    /* Aligner la hauteur avec les autres inputs */
    .select2-container--default .select2-selection--single {
        min-height: 38px;  /* ajustez cette valeur pour matcher vos inputs */
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        width: 100%;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        top: 0;
        right: 6px;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 16px;
        color: var(--muted, #999);
        font-size: 16px;
        line-height: 1;
        margin-top: 6px;
    }

    /* Dropdown */
    .select2-dropdown {
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        font-size: 13px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 10px;
        font-size: 12px;
        outline: none;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--bordeaux, #8B1A1A);
        color: white;
    }

    .select2-results__option[aria-selected=true] {
        background-color: rgba(139,26,26,0.08);
        color: var(--bordeaux, #8B1A1A);
    }

    /* Placeholder */
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--muted, #aaa);
    }

    /* Largeur cohérente avec le form-group parent */
    .select2-container {
        width: 100% !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Select2 sur le véhicule (recherche dans la liste) ──────────────────
        $('#sel-vehicle').select2({
            placeholder: '— Sélectionner —',
            allowClear: true,
            width: '100%',
        });

    });
</script>
@section('content')
    <script src="{{ asset('js/event.js') }}"></script>
    <div class="page">
    <h2 class="sr-only">Tableau de bord des événements GPS</h2>

    <div class="page-header">
        <div class="header-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20.586L3.707 15.293A1 1 0 013.415 14h.876a1 1 0 00.95-.684l1.055-3.158A1 1 0 017.246 9.5h9.508a1 1 0 01.95.658l1.055 3.158a1 1 0 00.95.684h.876a1 1 0 01.707 1.707L15 20.586A4 4 0 0112 22a4 4 0 01-3-1.414zM12 2a4 4 0 014 4c0 3-4 8-4 8S8 9 8 6a4 4 0 014-4z"/>
        </svg>
        </div>
        <div>
        <div class="page-title">Événements GPS</div>
        <div class="page-sub">Visualisation & analyse des données de tracking</div>
        </div>
    </div>

    <!-- Search form -->
    <div class="card">
        <div class="card-header">
        <div class="card-title-dot"></div>
        <span class="card-title">Récupérer les événements GPS</span>
        </div>
        <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
            <label class="form-label">Véhicule</label>
            <select id="sel-vehicle">
                <option value="">— Sélectionner —</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->imei }}"
                            data-name="{{ $v->name }}"
                            data-plate="{{ $v->plate_number }}">
                        {{ $v->name }}
                        @if($v->plate_number) ({{ $v->plate_number }}) @endif
                    </option>
                @endforeach
            </select>
            </div>
            <div class="form-group">
            <label class="form-label">Date début</label>
            <input type="date" id="date-from" value="{{ date('Y-m-01') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Date fin</label>
                <input type="date" id="date-to" value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <button class="btn-primary" onclick="fetchEvents()">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Charger
                </button>
            </div>
        </div>

        <div class="test-section">
            <label class="test-toggle">
            <input type="checkbox" id="chk-test" style="accent-color:var(--bx);width:14px;height:14px;" onchange="toggleTestOptions(this.checked)">
            <span class="test-toggle-label">Données de test (TestRawEvents)</span>
            </label>
            <div id="test-options" class="test-options" style="display:none;">
                @foreach([
                    'Tsiadino'    => 'Rotation complète (Tsihadino)',
                    'antonio'     => 'Rotation Antonio (2 rotations)',
                    'incomplete'  => 'Rotation incomplète',
                    'cancelled'   => 'Rotation annulée',
                    'real_sample' => 'Données réelles API',
                ] as $key => $label)
                    <label style="display:flex;align-items:center;gap:5px;font-size:12px;cursor:pointer;
                                background:var(--cream);border:1px solid var(--cream-dd);border-radius:5px;
                                padding:5px 10px;">
                        <input type="radio" name="test-mode" value="{{ $key }}"
                            {{ $key === 'complete' ? 'checked' : '' }}
                            style="accent-color:var(--bordeaux);">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" style="display:none;">
        <div class="loading">
        <div class="spinner"></div>
        <div class="loading-text">Chargement des événements GPS…</div>
        </div>
    </div>

    <!-- Results -->
    <div id="results" class="results-hidden">
        <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Véhicule</div>
            <div class="stat-text" id="stat-vehicle">—</div>
            <div class="stat-mono" id="stat-imei">—</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Période</div>
            <div class="stat-mono" id="stat-period" style="font-size:12px;color:var(--ink);margin-top:4px;">—</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Événements bruts</div>
            <div class="stat-value" id="stat-raw">—</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Filtrés</div>
            <div class="stat-value" id="stat-filtered">—</div>
            <div class="stat-mono">zone / marker</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Normalisés</div>
            <div class="stat-value" id="stat-normalized">—</div>
            <div class="stat-mono">en base</div>
        </div>
        </div>

        <div class="filter-bar">
            <span class="filter-bar-label">Filtrer :</span>
            <button class="filter-btn active" data-type="all"       onclick="filterEvents('all')">Tous</button>
            <button class="filter-btn"        data-type="enter_zone" onclick="filterEvents('enter_zone')">
                ↓ Entrée zone
            </button>
            <button class="filter-btn"        data-type="leave_zone" onclick="filterEvents('leave_zone')">
                ↑ Sortie zone
            </button>
            <button class="filter-btn"        data-type="pass_checkpoint" onclick="filterEvents('pass_checkpoint')">
                ● Checkpoint
            </button>
            <button class="filter-btn"        data-type="not_in_db" onclick="filterEvents('not_in_db')">
                ⚠ Hors BDD
            </button>
            <div class="filter-count"><span id="count-visible">0</span> événement(s)</div>
        </div>

        <div class="card">
            <div class="card-header">
                    <table id="events-table">
                    <thead>
                        <tr>
                        <th>Date / Heure</th>
                        <th>Type brut</th>
                        <th>Type normalisé</th>
                        <th>Zone / Checkpoint</th>
                        <th>En BDD</th>
                        <th>Coordonnées</th>
                        </tr>
                    </thead>
                    <tbody id="events-tbody"></tbody>
                    </table>
            </div>
            
            <div class="card-footer">
                <div id="pagination-container" class="pagination-wrapper"></div>
            </div>
        </div>
    </div>
    <div id="error-msg" style="display:none;"></div>
</div>
@endsection
