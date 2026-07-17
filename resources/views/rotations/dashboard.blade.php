{{-- resources/views/rotations/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
    :root {
        --bordeaux: #8B1A1A;
        --bordeaux2: #A52020;
        --bordeaux-light: rgba(139,26,26,0.08);
        --cream: #F5F0E8;
        --cream-dd: #E8E0D0;
        --ink: #1A1208;
        --muted: #9CA3AF;
        --success: #2D7A4A;
        --danger: #C0272D;
        --warning: #D97706;
        --info: #1D6FA4;
        --white: #FFFFFF;
        --radius: 10px;
        --shadow: 0 2px 12px rgba(26,18,8,0.07);
    }

    .db-wrap { padding: 24px; max-width: 1400px; margin: 0 auto; }

    /* Header */
    .db-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .db-title { font-size: 22px; font-weight: 700; color: var(--bordeaux); margin: 0; }
    .db-period { font-size: 13px; color: var(--muted); margin-top: 3px; }

    /* Filtres */
    .filter-bar {
        background: var(--white);
        border: 1px solid var(--cream-dd);
        border-radius: var(--radius);
        padding: 16px 20px;
        margin-bottom: 24px;
        box-shadow: var(--shadow);
    }
    .filter-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; min-width: 140px; }
    .filter-group label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .filter-group select,
    .filter-group input {
        border: 1px solid var(--cream-dd);
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 13px;
        color: var(--ink);
        background: var(--cream);
        outline: none;
        transition: border-color .15s;
    }
    .filter-group select:focus,
    .filter-group input:focus { border-color: var(--bordeaux2); }
    .btn-filter {
        background: var(--bordeaux);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        white-space: nowrap;
    }
    .btn-filter:hover { background: var(--bordeaux2); }

    /* Tabs filtre type */
    .filter-tabs { display: flex; gap: 6px; margin-bottom: 14px; }
    .filter-tab {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid var(--cream-dd);
        background: var(--white);
        color: var(--muted);
        transition: all .15s;
    }
    .filter-tab.active {
        background: var(--bordeaux);
        color: #fff;
        border-color: var(--bordeaux);
    }

    /* Stat cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    @media (max-width: 1100px) {
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 700px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .stat-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 14px 12px;
        box-shadow: var(--shadow);
        border-left: 4px solid transparent;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .stat-card.total    { border-left-color: var(--bordeaux); }
    .stat-card.done     { border-left-color: var(--success); }
    .stat-card.cancel   { border-left-color: var(--danger); }
    .stat-card.pending { border-left-color: var(--warning); }
    .stat-card.accept   { border-left-color: var(--info); }

    .stat-label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; }
    .stat-value { font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1; }
    .stat-card.total  .stat-value { color: var(--bordeaux); }
    .stat-card.done   .stat-value { color: var(--success); }
    .stat-card.cancel .stat-value { color: var(--danger); }
    .stat-card.pending .stat-value { color: var(--warning); }
    .stat-card.accept .stat-value  { color: var(--info); }

    /* Charts grid */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }

    .chart-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow);
    }
    .chart-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--bordeaux);
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .chart-container { position: relative; height: 220px; }

    /* Circuit breakdown */
    .circuit-list { display: flex; flex-direction: column; gap: 10px; }
    .circuit-row { display: flex; align-items: center; gap: 10px; }
    .circuit-name { font-size: 13px; color: var(--ink); flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .circuit-bar-wrap { flex: 2; background: var(--cream); border-radius: 4px; height: 8px; overflow: hidden; }
    .circuit-bar { height: 100%; background: var(--bordeaux); border-radius: 4px; transition: width .4s; }
    .circuit-count { font-size: 13px; font-weight: 700; color: var(--bordeaux); min-width: 32px; text-align: right; }
</style>

<div class="db-wrap">

    {{-- Header --}}
    <div class="db-header">
        <div>
            <h1 class="db-title">Dashboard Rotations</h1>
            <div class="db-period">
                {{ $periodStart->translatedFormat('d F Y') }} → {{ $periodEnd->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('rotations.dashboard') }}" class="filter-bar" id="filterForm">

        <div class="filter-tabs">
            <button type="button" class="filter-tab {{ $filterType === 'month' ? 'active' : '' }}"
                onclick="setFilter('month')">Mois</button>
            <button type="button" class="filter-tab {{ $filterType === 'range' ? 'active' : '' }}"
                onclick="setFilter('range')">Période</button>
        </div>

        <input type="hidden" name="filter_type" id="filter_type" value="{{ $filterType }}">

        <div class="filter-row">

            {{-- Circuit --}}
            <div class="filter-group">
                <label>Circuit</label>
                <select name="circuit_id">
                    <option value="">Tous les circuits</option>
                    @foreach($circuits as $c)
                        <option value="{{ $c->id }}" {{ $circuitId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mois --}}
            <div class="filter-group" id="group-month" style="{{ $filterType === 'range' ? 'display:none' : '' }}">
                <label>Mois</label>
                <input type="month" name="month_input" value="{{ sprintf('%04d-%02d', $year, $month) }}"
                    onchange="document.querySelector('[name=year]').value=this.value.split('-')[0];
                              document.querySelector('[name=month]').value=this.value.split('-')[1];">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
            </div>

            {{-- Plage de dates --}}
            <div class="filter-group" id="group-from" style="{{ $filterType !== 'range' ? 'display:none' : '' }}">
                <label>Du</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="filter-group" id="group-to" style="{{ $filterType !== 'range' ? 'display:none' : '' }}">
                <label>Au</label>
                <input type="date" name="date_to" value="{{ $dateTo }}">
            </div>

            <button type="submit" class="btn-filter">Appliquer</button>
        </div>
    </form>

    {{-- Stat cards --}}
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-label">Total rotations</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card done">
            <div class="stat-label">Complètes</div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat-card accept">
            <div class="stat-label">Acceptables</div>
            <div class="stat-value">{{ $stats['acceptable'] }}</div>
        </div>
        <div class="stat-card cancel">
            <div class="stat-label">Annulées</div>
            <div class="stat-value">{{ $stats['cancelled'] }}</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-label">En cours</div>
            <div class="stat-value">{{ $stats['in_progress'] }}</div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="charts-grid">

        {{-- Par jour --}}
        <div class="chart-card">
            <div class="chart-title">Rotations par jour</div>
            <div class="chart-container">
                <canvas id="chartDay"></canvas>
            </div>
        </div>

        {{-- Par semaine --}}
        <div class="chart-card">
            <div class="chart-title">Rotations par semaine</div>
            <div class="chart-container">
                <canvas id="chartWeek"></canvas>
            </div>
        </div>

        {{-- Par circuit --}}
        @if(!$circuitId && $byCircuit->count() > 1)
        <div class="chart-card">
            <div class="chart-title">Par circuit</div>
            @php $maxCircuit = $byCircuit->max('count') ?: 1; @endphp
            <div class="circuit-list">
                @foreach($byCircuit->sortByDesc('count') as $item)
                <div class="circuit-row">
                    <div class="circuit-name" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                    <div class="circuit-bar-wrap">
                        <div class="circuit-bar" style="width: {{ round($item['count'] / $maxCircuit * 100) }}%"></div>
                    </div>
                    <div class="circuit-count">{{ $item['count'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Répartition statuts --}}
        <div class="chart-card">
            <div class="chart-title">Répartition des statuts</div>
            <div class="chart-container">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>

    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    // Données PHP → JS
    const dayLabels  = @json($byDay->keys());
    const dayData    = @json($byDay->values());
    const weekLabels = @json($byWeek->pluck('week'));
    const weekData   = @json($byWeek->pluck('count'));
    const stats      = @json($stats);

    const BORDEAUX = '#8B1A1A';
    const SUCCESS  = '#2D7A4A';
    const DANGER   = '#C0272D';
    const WARNING  = '#D97706';
    const INFO     = '#1D6FA4';
    const MUTED    = '#9CA3AF';

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1A1208',
                titleFont: { size: 12 },
                bodyFont: { size: 12 },
                padding: 10,
                cornerRadius: 6,
            }
        },
        scales: {
            x: { grid: { color: '#F0EAE0' }, ticks: { color: MUTED, font: { size: 11 } } },
            y: { grid: { color: '#F0EAE0' }, ticks: { color: MUTED, font: { size: 11 }, stepSize: 1 }, beginAtZero: true }
        }
    };

    // Chart par jour
    new Chart(document.getElementById('chartDay'), {
        type: 'bar',
        data: {
            labels: dayLabels,
            datasets: [{
                data: dayData,
                backgroundColor: BORDEAUX + 'CC',
                borderColor: BORDEAUX,
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: { ...chartDefaults }
    });

    // Chart par semaine
    new Chart(document.getElementById('chartWeek'), {
        type: 'line',
        data: {
            labels: weekLabels,
            datasets: [{
                data: weekData,
                borderColor: BORDEAUX,
                backgroundColor: BORDEAUX + '18',
                borderWidth: 2.5,
                pointBackgroundColor: BORDEAUX,
                pointRadius: 5,
                fill: true,
                tension: 0.3,
            }]
        },
        options: { ...chartDefaults }
    });

    // Chart statuts (donut)
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Complètes', 'Acceptables', 'Annulées', 'En cours'],
            datasets: [{
                data: [
                    stats.completed - stats.acceptable,
                    stats.acceptable,
                    stats.cancelled,
                    stats.in_progress
                ],
                backgroundColor: [SUCCESS, INFO, DANGER, WARNING],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: { color: '#1A1208', font: { size: 12 }, padding: 12, boxWidth: 12 }
                },
                tooltip: {
                    backgroundColor: '#1A1208',
                    padding: 10,
                    cornerRadius: 6,
                }
            }
        }
    });

    // Gestion des filtres
    function setFilter(type) {
        document.getElementById('filter_type').value = type;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');

        document.getElementById('group-month').style.display = type === 'month' ? '' : 'none';
        document.getElementById('group-from').style.display  = type === 'range' ? '' : 'none';
        document.getElementById('group-to').style.display    = type === 'range' ? '' : 'none';
    }
</script>
@endsection