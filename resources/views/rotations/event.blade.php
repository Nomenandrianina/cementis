@extends('layouts.app')
@section('title', 'Événements GPS')
@section('page-title', 'Visualisation des événements GPS')
<style>
    @keyframes spin { to { transform: rotate(360deg); } }

    .filter-btn {
        padding: 5px 12px;
        border-radius: 20px;
        border: 1px solid var(--cream-dd);
        background: var(--cream);
        color: var(--muted);
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--sans);
        transition: all 0.15s;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .filter-btn:hover { background: var(--cream-d); color: var(--ink); }
    .filter-btn.active {
        background: var(--bordeaux);
        color: #fff;
        border-color: var(--bordeaux);
    }

    .ev-row { transition: background 0.1s; }
    .ev-row:hover td { background: rgba(139,26,26,0.03) !important; }
    .ev-row.hidden { display: none; }

    .type-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-family: var(--sans);
    }
    .badge-enter   { background: rgba(45,122,74,0.12);  color: #2D7A4A; }
    .badge-leave   { background: rgba(184,114,10,0.12); color: #B8720A; }
    .badge-cp      { background: rgba(26,18,8,0.08);    color: #3D2E1A; }
    .badge-indb    { background: rgba(45,122,74,0.1);   color: #2D7A4A; }
    .badge-notindb { background: rgba(192,39,45,0.1);   color: #C0272D; }
    .badge-raw     { background: var(--cream-d);        color: var(--muted); font-family: var(--mono); }
</style>
@section('content')

<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
let allEvents = [];

// Toggle mode test
// document.getElementById('chk-test').addEventListener('change', function() {
//     document.getElementById('test-options').style.display = this.checked ? 'flex' : 'none';
// });
document.addEventListener('DOMContentLoaded', function() {
    const chkTest = document.getElementById('chk-test');
    const testOptions = document.getElementById('test-options');

    // On vérifie que l'élément existe pour éviter l'erreur
    if (chkTest) {
        chkTest.addEventListener('change', function() {
            if (testOptions) {
                testOptions.style.display = this.checked ? 'flex' : 'none';
            }
        });
    }
});

async function fetchEvents() {
    const imei     = document.getElementById('sel-vehicle').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo   = document.getElementById('date-to').value;
    const useTest  = document.getElementById('chk-test').checked;
    const testMode = document.querySelector('input[name="test-mode"]:checked')?.value ?? 'complete';

    if (!useTest && !imei) {
        showError('Sélectionnez un véhicule ou activez le mode test.');
        return;
    }
    if (!dateFrom || !dateTo) {
        showError('Sélectionnez une période.');
        return;
    }

    document.getElementById('results').style.display  = 'none';
    document.getElementById('error-msg').style.display = 'none';
    document.getElementById('loading').style.display   = 'block';

    try {
        const resp = await fetch("{{ route('rotations.event.fetch') }}", {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                imei:      imei || '865135061356851',
                date_from: dateFrom,
                date_to:   dateTo,
                use_test:  useTest ? 1 : 0,
                test_mode: testMode,
            }),
        });

        const data = await resp.json();
        document.getElementById('loading').style.display = 'none';

        if (!data.success) {
            showError(data.message ?? 'Erreur inconnue.');
            return;
        }

        allEvents = data.events;
        renderStats(data);
        renderTable(allEvents);
        document.getElementById('results').style.display = 'block';

    } catch(e) {
        document.getElementById('loading').style.display = 'none';
        showError('Erreur de connexion : ' + e.message);
    }
}

function renderStats(data) {
    document.getElementById('stat-vehicle').textContent  = data.vehicle.name;
    document.getElementById('stat-imei').textContent     = data.vehicle.imei;
    document.getElementById('stat-period').innerHTML     =
        `<span>${data.period.from}</span><br><span>→ ${data.period.to}</span>`;
    document.getElementById('stat-raw').textContent        = data.raw_count;
    document.getElementById('stat-filtered').textContent   = data.filtered_count;
    document.getElementById('stat-normalized').textContent = data.normalized_count;

    // Source badge
    const srcBadge = data.source === 'test'
        ? '<span style="font-size:10px;background:rgba(184,114,10,0.12);color:#B8720A;padding:2px 8px;border-radius:10px;font-weight:700;">TEST</span>'
        : '<span style="font-size:10px;background:rgba(45,122,74,0.12);color:#2D7A4A;padding:2px 8px;border-radius:10px;font-weight:700;">API GPS</span>';
    document.getElementById('stat-vehicle').insertAdjacentHTML('beforebegin',
        `<div style="margin-bottom:4px;">${srcBadge}</div>`
    );
}

function renderTable(events) {
    const tbody = document.getElementById('events-tbody');
    tbody.innerHTML = '';

    if (!events.length) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px;">Aucun événement.</td></tr>`;
        updateCount(0);
        return;
    }

    events.forEach((ev, idx) => {
        const typeClass = {
            'enter_zone':      'badge-enter',
            'leave_zone':      'badge-leave',
            'pass_checkpoint': 'badge-cp',
        }[ev.normalized_type] ?? '';

        const rawClass = {
            'zone_in':   'badge-enter',
            'zone_out':  'badge-leave',
            'marker_in': 'badge-cp',
        }[ev.raw_type] ?? 'badge-raw';

        const inDbHtml = ev.in_db
            ? '<span class="type-badge badge-indb">✓ BDD</span>'
            : '<span class="type-badge badge-notindb">⚠ Hors BDD</span>';

        const idHtml = ev.zone_id
            ? `<span class="mono" style="font-size:11px;color:var(--bordeaux);">zone #${ev.zone_id}</span>`
            : ev.checkpoint_id
                ? `<span class="mono" style="font-size:11px;color:#2D7A4A;">cp #${ev.checkpoint_id}</span>`
                : '<span style="color:var(--muted);">—</span>';

        const tr = document.createElement('tr');
        tr.className = 'ev-row';
        tr.dataset.type  = ev.normalized_type;
        tr.dataset.in_db = ev.in_db ? '1' : '0';

        tr.innerHTML = `
            <td class="mono" style="color:var(--muted);font-size:11px;">${idx + 1}</td>
            <td class="mono" style="font-size:12px;white-space:nowrap;">${ev.dt ?? '—'}</td>
            <td><span class="type-badge ${rawClass}">${ev.raw_type}</span></td>
            <td><span class="type-badge ${typeClass}">${ev.normalized_type}</span></td>
            <td style="font-weight:600;font-size:13px;">${ev.reference_name ?? '—'}</td>
            <td>${inDbHtml}</td>
            <td>${idHtml}</td>
            <td class="mono" style="font-size:11px;color:var(--muted);">${ev.lat ?? '—'}</td>
            <td class="mono" style="font-size:11px;color:var(--muted);">${ev.lng ?? '—'}</td>
        `;
        tbody.appendChild(tr);
    });

    updateCount(events.length);
}

function filterEvents(type) {
    // Mettre à jour les boutons
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.type === type);
    });

    const rows = document.querySelectorAll('.ev-row');
    let visible = 0;

    rows.forEach(row => {
        let show = false;
        if (type === 'all')            show = true;
        else if (type === 'not_in_db') show = row.dataset.in_db === '0';
        else                           show = row.dataset.type === type;

        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    updateCount(visible);
}

function updateCount(n) {
    document.getElementById('count-visible').textContent = n;
}

function showError(msg) {
    const el = document.getElementById('error-msg');
    el.style.display = 'block';
    el.innerHTML = `<div class="alert alert-error">${msg}</div>`;
}
</script>
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
            <div id="test-options" style="display:none;display:flex;gap:8px;flex-wrap:wrap;">
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
                        <th>#</th>
                        <th>Date / Heure</th>
                        <th>Type brut</th>
                        <th>Type normalisé</th>
                        <th>Zone / Checkpoint</th>
                        <th>En BDD ?</th>
                        <th>ID BDD</th>
                        <th>Lat</th>
                        <th>Lng</th>
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

@push('styles')

@endpush

@push('scripts')

@endpush
