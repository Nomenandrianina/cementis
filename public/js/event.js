const CSRF = document.querySelector('meta[name=csrf-token]').content;
let allEvents = [];

document.addEventListener('DOMContentLoaded', function() {
    const chkTest = document.getElementById('chk-test');
    const testOptions = document.getElementById('test-options');

    if (chkTest && testOptions) {
        // Fonction réutilisable pour mettre à jour l'affichage
        const toggleOptions = () => {
            testOptions.style.display = chkTest.checked ? 'flex' : 'none';
        };

        // Ecouter les changements
        chkTest.addEventListener('change', toggleOptions);

        // Lancer une fois au démarrage (au cas où la case est cochée par défaut)
        toggleOptions();
    }
});

async function fetchEvents() {
    const routeUrl = document.querySelector('meta[name="route-fetch-events"]')?.content;
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
        const resp = await fetch(routeUrl, {
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
        let displayDate = '—';

        if (ev.dt) {
            let dateObj = new Date(ev.dt);
            dateObj.setHours(dateObj.getHours() + 3);

            const pad = (n) => String(n).padStart(2, '0');

            const day = pad(dateObj.getDate());
            const month = pad(dateObj.getMonth() + 1);
            const year = dateObj.getFullYear();
            const hours = pad(dateObj.getHours());
            const minutes = pad(dateObj.getMinutes());
            const seconds = pad(dateObj.getSeconds()); 

            displayDate = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        }

        tr.innerHTML = `
            <td class="mono" style="font-size:12px;white-space:nowrap;">${displayDate}</td>
            <td><span class="type-badge ${rawClass}">${ev.raw_type}</span></td>
            <td><span class="type-badge ${typeClass}">${ev.normalized_type}</span></td>
            <td style="font-weight:600;font-size:13px;">${ev.reference_name ?? '—'}</td>
            <td>${inDbHtml}</td>
            <td class="mono" style="font-size:11px;color:var(--muted);">${ev.lat + ',' + ev.lng ?? '—'}</td>
        `;
        tbody.appendChild(tr);
    });

    updateCount(events.length);
}

function filterEvents(type) {
    // 1. Mise à jour visuelle des boutons
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(b => b.classList.remove('active'));
    
    const activeBtn = document.querySelector(`.filter-btn[data-type="${type}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    // 2. Ciblage des lignes
    const rows = document.querySelectorAll('#events-tbody .ev-row');
    let visible = 0;

    rows.forEach(row => {
        const rowType = row.dataset.type;
        const inDb = row.dataset.in_db; // C'est toujours une string dans le dataset

        let show = false;

        if (type === 'all') {
            show = true;
        } else if (type === 'not_in_db') {
            // On vérifie '0', 'false' ou vide pour être sûr
            show = (inDb === '0' || inDb === 'false' || !inDb);
        } else {
            show = (rowType === type);
        }

        // Utilisation de style.display pour éviter les conflits de classes CSS
        row.style.display = show ? '' : 'none';
        
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