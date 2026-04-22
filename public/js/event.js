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

let currentPage = 1;
const rowsPerPage = 15; // Nombre d'éléments par page
let filteredEvents = [];

function renderTable(events) {
    console.log('Rendering table with events:', events);
    filteredEvents = events;
    const tbody = document.getElementById('events-tbody');

    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedEvents = filteredEvents.slice(startIndex, endIndex);

    document.getElementById('count-visible').textContent = filteredEvents.length;
    if (!paginatedEvents.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted);font-family:var(--mono);font-size:12px;">Aucun événement correspondant</td></tr>';
        renderPaginationControls();
        return;
    }
    tbody.innerHTML = paginatedEvents.map(e => {
        if (e.dt) {
            let dateObj = new Date(e.dt);
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
        const typeBadge = e.normalized_type === 'enter_zone'
        ? `<span class="badge badge-enter">↓ enter_zone</span>`
        : e.normalized_type === 'leave_zone'
        ? `<span class="badge badge-leave">↑ leave_zone</span>`
        : `<span class="badge badge-check">● checkpoint</span>`;
        const normBadge = typeBadge;
        const dbBadge = e.in_db
        ? `<span class="badge badge-ok"><span class="dot dot-ok"></span> BDD</span>`
        : `<span class="badge badge-warn"><span class="dot dot-warn"></span> Hors BDD</span>`;
        return `<tr>
        <td class="mono-small">${displayDate}</td>
        <td class="mono-small" style="color:var(--ink)">${e.raw_type}</td>
        <td>${typeBadge}</td>
        <td style="font-size:12px;font-weight:600;">${e.reference_name}</td>
        <td>${dbBadge}</td>
        <td class="mono-small">${e.lat.toFixed(4)}, ${e.lng.toFixed(4)}</td>
        </tr>`;
    }).join('');

    renderPaginationControls();
}

function renderPaginationControls() {
    const totalPages = Math.ceil(filteredEvents.length / rowsPerPage);
    const container = document.getElementById('pagination-container');
    
    let html = `
        <button onclick="changePage(1)" ${currentPage === 1 ? 'disabled' : ''}>&laquo;</button>
        <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>&lsaquo;</button>
        <span class="page-info">Page ${currentPage} sur ${totalPages || 1}</span>
        <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>&rsaquo;</button>
        <button onclick="changePage(${totalPages})" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}>&raquo;</button>
    `;
    container.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    renderTable(filteredEvents);
}