<link rel="stylesheet" href="{{ asset('css/event.css') }}">
@extends('layouts.app')
@section('title', 'Événements GPS')
@section('page-title', 'Visualisation des événements GPS')
<meta name="route-fetch-events" content="{{ route('rotations.event.fetch') }}">
@section('content')
<script src="{{ asset('js/event.js') }}"></script>
    


{{-- ── Formulaire de recherche ──────────────────────────────────────────── --}}
<div class="card mb-16">
    <div class="card-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--bordeaux)">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0"/>
        </svg>
        <span class="card-title">Récupérer les événements GPS</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end;">
            <div class="form-group" style="margin:0;">
                <label>Véhicule</label>
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
            <div class="form-group" style="margin:0;">
                <label>Date début</label>
                <input type="date" id="date-from" value="{{ date('Y-m-01') }}">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Date fin</label>
                <input type="date" id="date-to" value="{{ date('Y-m-d') }}">
            </div>
            <button class="btn btn-primary" onclick="fetchEvents()">
                ↺ Charger
            </button>
        </div>

        {{-- Mode test ──────────────────────────────────────────────────── --}}
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--cream-d);">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:10px;">
                <input type="checkbox" id="chk-test" style="accent-color:var(--bordeaux);width:15px;height:15px;">
                <span style="font-size:12px;font-weight:600;color:var(--bordeaux);">
                    Utiliser les données de test (TestRawEvents)
                </span>
            </label>
            <div id="test-options" style="display:none;gap:8px;flex-wrap:wrap;">
                @foreach([
                    'complete'    => 'Rotation complète (Tsihadino)',
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

{{-- ── Résultats ─────────────────────────────────────────────────────────── --}}
<div id="results" style="display:none;">

    {{-- Stats ───────────────────────────────────────────────────────────── --}}
    <div class="stats-grid mb-16" style="grid-template-columns:repeat(5,1fr);">
        <div class="stat-card">
            <div class="stat-label">Véhicule</div>
            <div id="stat-vehicle" style="font-family:var(--serif);font-size:15px;font-weight:700;color:var(--bordeaux);"></div>
            <div id="stat-imei" class="stat-sub mono"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Période</div>
            <div id="stat-period" style="font-family:var(--mono);font-size:12px;color:var(--ink);"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Événements bruts</div>
            <div id="stat-raw" class="stat-value"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Filtrés (zone/marker)</div>
            <div id="stat-filtered" class="stat-value"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Normalisés (BDD)</div>
            <div id="stat-normalized" class="stat-value"></div>
        </div>
    </div>

    {{-- Filtres rapides ──────────────────────────────────────────────────── --}}
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap;">
        <span style="font-size:11px;color:var(--muted);font-weight:600;">Filtrer :</span>
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

        <div style="margin-left:auto;font-size:11px;color:var(--muted);">
            <span id="count-visible">—</span> événement(s) affiché(s)
        </div>
    </div>

    {{-- Tableau des événements ───────────────────────────────────────────── --}}
    <div class="card">
        <div class="table-wrap">
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
                <tbody id="events-tbody">
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Loading ────────────────────────────────────────────────────────────── --}}
<div id="loading" style="display:none;text-align:center;padding:48px;">
    <div style="display:inline-block;width:32px;height:32px;border:3px solid var(--cream-dd);
                border-top-color:var(--bordeaux);border-radius:50%;animation:spin 0.8s linear infinite;"></div>
    <div style="margin-top:12px;font-size:13px;color:var(--muted);">Chargement des événements GPS…</div>
</div>

{{-- Message erreur ──────────────────────────────────────────────────────── --}}
<div id="error-msg" style="display:none;"></div>

@endsection

