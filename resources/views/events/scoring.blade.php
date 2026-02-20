@extends('layouts.app')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');

:root {
    --ac-bordeaux:   #8b1a1a;
    --ac-bordeaux-2: #6b2737;
    --ac-cement:     #4b5563;
    --ac-cement-2:   #374151;
    --ac-light:      #f8f7f6;
    --ac-border:     #e5e3e0;
}

/* ===== PAGE WRAPPER ===== */
.scoring-page {
    font-family: 'DM Sans', sans-serif;
    padding: 24px 28px 32px;
}

/* ===== HEADER CARD ===== */
.scoring-header-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid var(--ac-border);
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
    padding: 20px 24px;
    margin-bottom: 24px;
}

.scoring-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

/* ===== TITLE BLOCK ===== */
.scoring-title-block {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.scoring-eyebrow {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ac-bordeaux-2);
    opacity: 0.75;
}

.scoring-title {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #111827;
    line-height: 1;
    margin: 0;
    background: linear-gradient(135deg, var(--ac-bordeaux) 0%, var(--ac-cement) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ===== CONTROLS ROW ===== */
.scoring-controls {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

/* ===== SELECT ===== */
.scoring-select {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: #374151;
    background: var(--ac-light);
    border: 1.5px solid var(--ac-border);
    border-radius: 10px;
    padding: 9px 36px 9px 14px;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    transition: border-color 0.2s, box-shadow 0.2s;
    min-width: 200px;
}

.scoring-select:focus {
    border-color: var(--ac-bordeaux-2);
    box-shadow: 0 0 0 3px rgba(107,39,55,0.1);
}

/* ===== SEARCH ===== */
.scoring-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.scoring-search-wrap .search-icon {
    position: absolute;
    left: 12px;
    color: #9ca3af;
    pointer-events: none;
    display: flex;
    align-items: center;
}

.scoring-search {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: #374151;
    background: var(--ac-light);
    border: 1.5px solid var(--ac-border);
    border-radius: 10px;
    padding: 9px 14px 9px 36px;
    outline: none;
    width: 220px;
    transition: border-color 0.2s, box-shadow 0.2s, width 0.3s;
}

.scoring-search:focus {
    border-color: var(--ac-bordeaux-2);
    box-shadow: 0 0 0 3px rgba(107,39,55,0.1);
    width: 270px;
}

.scoring-search::placeholder { color: #b0b7c3; }

/* ===== DIVIDER ===== */
.scoring-controls-divider {
    width: 1px;
    height: 28px;
    background: var(--ac-border);
    flex-shrink: 0;
}

/* ===== EXPORT BUTTON ===== */
.btn-export {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #ffffff;
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    border: none;
    border-radius: 10px;
    padding: 9px 16px;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
    white-space: nowrap;
}

.btn-export:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(22,163,74,0.30);
    color: #ffffff;
    text-decoration: none;
    opacity: 0.95;
}

.btn-export:active { transform: translateY(0); }

/* ===== SCORE COLOR BADGES ===== */
.scoring-green  { background-color: #6dac10; color: #000; }
.scoring-yellow { background-color: #f7d117; color: #000; }
.scoring-orange { background-color: #f58720; color: #000; }
.scoring-red    { background-color: #f44336; color: #fff; }

/* ===== CONTENT AREA ===== */
.scoring-content {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--ac-border);
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .scoring-page { padding: 16px 14px; }
    .scoring-header-inner { flex-direction: column; align-items: flex-start; }
    .scoring-controls { width: 100%; }
    .scoring-select, .scoring-search { width: 100%; min-width: unset; }
    .scoring-search:focus { width: 100%; }
    .scoring-controls-divider { display: none; }
    .btn-export { width: 100%; justify-content: center; }
}
</style>

<div class="scoring-page">

    {{-- ===== HEADER ===== --}}
    <div class="scoring-header-card">
        <div class="scoring-header-inner">

            {{-- Titre --}}
            <div class="scoring-title-block">
                <span class="scoring-eyebrow">Tableau de bord</span>
                <h1 class="scoring-title">Scoring Card</h1>
            </div>

            {{-- Contrôles --}}
            <div class="scoring-controls">

                {{-- Sélecteur planning --}}
                <select class="scoring-select" name="planning" id="planning">
                    <option value="">Choisir un planning</option>
                    @foreach($import_calendar as $calendar)
                        <option value="{{ $calendar->id }}" {{ $calendar->id == $selectedPlanning ? 'selected' : '' }}>
                            {{ $calendar->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Séparateur --}}
                <div class="scoring-controls-divider"></div>

                {{-- Recherche --}}
                <div class="scoring-search-wrap">
                    <span class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input class="scoring-search" type="text" id="searchInput" placeholder="Rechercher un chauffeur...">
                </div>

                {{-- Séparateur --}}
                <div class="scoring-controls-divider"></div>

                {{-- Export --}}
                @can('export.excel.scoring')
                    <a id="export-link" class="btn-export"
                       href="{{ route('export.excel.scoring', ['planning' => $selectedPlanning, 'alphaciment_driver' => $alphaciment_driver]) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Exporter Excel
                    </a>
                @endcan

            </div>
        </div>
    </div>

    {{-- ===== TABLE CONTENT ===== --}}
    <div class="scoring-content">
        @include('events.scoring_filtre')
    </div>

</div>

@endsection