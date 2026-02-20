@extends('layouts.app')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA1f_TK4EnA9ZIQIv6_o5piA48iW8tuHoQ"></script>
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');

:root {
    --ac-bordeaux:  #8b1a1a;
    --ac-bordeaux-2:#6b2737;
    --ac-cement:    #4b5563;
    --ac-cement-2:  #374151;
    --ac-light:     #f8f7f6;
    --ac-border:    #e5e3e0;
}

/* ===== PAGE ===== */
.detail-page {
    font-family: 'DM Sans', sans-serif;
    padding: 24px 28px 32px;
}

/* ===== HEADER CARD ===== */
.detail-header-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--ac-border);
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
    padding: 18px 24px;
    margin-bottom: 24px;
}

.detail-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
}

/* Title */
.detail-title-block { display: flex; flex-direction: column; gap: 3px; }

.detail-eyebrow {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ac-bordeaux-2);
    opacity: 0.75;
}

.detail-title {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0;
    background: linear-gradient(135deg, var(--ac-bordeaux) 0%, var(--ac-cement) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.1;
}

/* Action buttons */
.detail-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.btn-ac {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    border-radius: 10px;
    padding: 8px 15px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
    white-space: nowrap;
}

.btn-ac:hover {
    transform: translateY(-1px);
    text-decoration: none;
    opacity: 0.92;
}

.btn-ac-back {
    background: var(--ac-light);
    color: var(--ac-cement-2);
    border: 1.5px solid var(--ac-border);
}
.btn-ac-back:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.10); color: var(--ac-cement-2); }

.btn-ac-pdf {
    background: linear-gradient(135deg, var(--ac-bordeaux) 0%, var(--ac-bordeaux-2) 100%);
    color: #fff;
}
.btn-ac-pdf:hover { box-shadow: 0 6px 16px rgba(107,39,55,0.30); color: #fff; }

.btn-ac-excel {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: #fff;
}
.btn-ac-excel:hover { box-shadow: 0 6px 16px rgba(22,163,74,0.30); color: #fff; }

/* ===== TABLE CARD ===== */
.detail-table-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--ac-border);
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* ===== TABLE ===== */
#tableau-score {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

#tableau-score thead tr {
    background: linear-gradient(135deg, var(--ac-bordeaux) 0%, var(--ac-bordeaux-2) 40%, var(--ac-cement) 100%);
}

#tableau-score thead th {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.90);
    padding: 13px 10px;
    border: none;
    vertical-align: middle;
    text-align: center;
}

#tableau-score tbody td {
    padding: 10px 10px;
    border-bottom: 1px solid #f0eeec;
    vertical-align: middle;
    text-align: center;
    color: #374151;
}

#tableau-score tbody tr:hover {
    background: #faf9f8;
}

#tableau-score tbody tr:last-child td { border-bottom: none; }

/* Total row */
#tableau-score tbody tr.total-row {
    background: #f3f4f6;
    font-weight: 600;
    color: #111827;
}

#tableau-score tbody tr.total-row td {
    border-top: 2px solid var(--ac-border);
    border-bottom: none;
}

/* GPS link */
#tableau-score a {
    color: var(--ac-bordeaux-2);
    font-weight: 500;
    text-decoration: none;
    border-bottom: 1px dashed rgba(107,39,55,0.3);
    transition: color 0.15s;
}
#tableau-score a:hover { color: var(--ac-bordeaux); border-bottom-style: solid; }

/* ===== BADGES SCORING ===== */
.ac-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 100px;
    min-width: 40px;
}
.ac-badge-success { background: #dcfce7; color: #15803d; }
.ac-badge-warning { background: #fef9c3; color: #a16207; }
.ac-badge-danger  { background: #ffedd5; color: #c2410c; }
.ac-badge-dark    { background: #fee2e2; color: #b91c1c; }

/* ===== COLOR CELLS ===== */
.scoring-green  { background-color: #6dac10; color: #000; }
.scoring-yellow { background-color: #f7d117; color: #000; }
.scoring-orange { background-color: #f58720; color: #000; }
.scoring-red    { background-color: #f44336; color: #fff; }

/* ===== MODAL ===== */
.modal-content { border-radius: 14px; border: none; }
.modal-header {
    background: linear-gradient(135deg, var(--ac-bordeaux) 0%, var(--ac-cement) 100%);
    border-radius: 14px 14px 0 0;
    padding: 14px 20px;
}
.modal-title { color: #fff; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 15px; }
.modal-header .close { color: #fff; opacity: 0.8; text-shadow: none; }
.modal-header .close:hover { opacity: 1; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .detail-page { padding: 14px 12px; }
    .detail-header-inner { flex-direction: column; align-items: flex-start; }
    .detail-actions { width: 100%; }
    .btn-ac { flex: 1; justify-content: center; }
}
</style>

<div class="detail-page">

    {{-- ===== HEADER ===== --}}
    <div class="detail-header-card">
        <div class="detail-header-inner">
            <div class="detail-title-block">
                <span class="detail-eyebrow">Scoring Card</span>
                <h1 class="detail-title">Détail du score card</h1>
            </div>
            <div class="detail-actions">
                <a href="{{ route('new.scoring') }}" class="btn-ac btn-ac-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    Retour
                </a>
                <button type="button" class="btn-ac btn-ac-pdf" onclick="exportToPDF()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    PDF
                </button>
                <a class="btn-ac btn-ac-excel" href="{{ route('export.excel.detail.scoring', ['imei' => $imei, 'badge' => $badge, 'id_planning' => $id_planning]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Excel
                </a>
            </div>
        </div>
    </div>

    {{-- ===== MODAL CARTE ===== --}}
    <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Localisation GPS</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="map" style="height: 420px; border-radius: 0 0 14px 14px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TABLE ===== --}}
    <div class="detail-table-card">
        <div class="table-responsive">
            <table id="tableau-score" class="table mb-0">
                <thead>
                    <tr>
                        <th>Chauffeur</th>
                        <th>Transporteur</th>
                        <th>Infraction</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Coord. GPS</th>
                        <th style="width:120px">Durée infraction / effectuée</th>
                        <th style="width:90px">Insuff. / Excès</th>
                        <th>Distance</th>
                        <th>Points</th>
                        <th>Scoring Card</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total_point = 0; $scoringClass = ''; $scoring_card = 0; @endphp

                    @if (!$scoring->isEmpty())
                        @foreach ($scoring as $result)
                            @php $chauffeur_calendar = getDriverByNumberBadge($result->badge_calendar, $id_planning); @endphp
                            <tr>
                                <td>{{ $chauffeur_calendar }}</td>
                                <td>{{ get_transporteur($result->imei, $result->camion) }}</td>
                                <td>{{ trim($result->infraction) }}</td>
                                <td>{{ \Carbon\Carbon::parse($result->date_debut.' '.$result->heure_debut)->format('d-m-Y H:i:s') }}</td>
                                <td>{{ \Carbon\Carbon::parse($result->date_fin.' '.$result->heure_fin)->format('d-m-Y H:i:s') }}</td>
                                <td>
                                    <a href="#" onclick="showMapModal('{{ $result->gps_debut }}', '{{ $result->infraction }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:3px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        {{ $result->gps_debut }}
                                    </a>
                                </td>
                                <td>{{ convertMinuteHeure($result->duree_infraction) }}</td>
                                <td>{{ $result->insuffisance ? convertMinuteHeure($result->insuffisance) : '—' }}</td>
                                <td>0 Km</td>
                                <td><strong>{{ $result->point }}</strong></td>
                                <td></td>
                                @php $total_point += $result->point; @endphp
                            </tr>
                        @endforeach

                        @php
                            $scoring_card = $total_point;
                            $badgeClass = match(true) {
                                $scoring_card <= 2  => 'ac-badge ac-badge-success',
                                $scoring_card <= 5  => 'ac-badge ac-badge-warning',
                                $scoring_card <= 10 => 'ac-badge ac-badge-danger',
                                default             => 'ac-badge ac-badge-dark'
                            };
                        @endphp
                        <tr class="total-row">
                            <td colspan="8" class="text-right">Total</td>
                            <td>0 Km</td>
                            <td><strong>{{ $total_point }}</strong></td>
                            <td><span class="{{ $badgeClass }}">{{ $scoring_card }}</span></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="11" style="padding: 40px; color: #9ca3af;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Aucun élément
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<script src="{{ asset('js/plugins/jquery.rowspanizer.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $(".driver-row").each(function () {
            $(this).click(function () {
                $(this).next(".driver-details").slideToggle();
                var icon = $(this).find(".expand-icon");
                icon.toggleClass("fa-plus-circle fa-minus-circle");
            });
        });
    });

    function initMap(latitude, longitude, type) {
        var map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            zoom: 15
        });
        new google.maps.Marker({
            position: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            map: map,
            title: type
        });
    }

    function showMapModal(gps, type) {
        var tab = gps.split(',');
        initMap(tab[0], tab[1], type);
        $('#mapModal').modal('show');
    }

    function exportToPDF() {
        const element = document.getElementById('tableau-score');
        html2pdf().set({
            margin: 0.5,
            filename: 'scoring-detail.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 4 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
        }).from(element).save();
    }

    $("#tableau-score").rowspanizer({ columns: [0, 1, 2], vertical_align: 'middle' });
</script>

@endsection