@extends('layouts.app')
@section('title', 'Rotations')
@section('page-title', 'Rotations')

<link rel="stylesheet" href="{{ asset('css/rotation_index.css') }}">
<style>
    .ac-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .ac-field {
        margin-bottom: 1.25rem;
    }

    .ac-label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 11px;
    }

    .ac-select,
    .ac-input {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        border: 0.5px solid #ccc;
        border-radius: 8px;
        background: #f8f8f8;
        color: #1a1a1a;
        font-size: 15px;
        font-family: 'Barlow', sans-serif;
        font-weight: 400;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
    }

    .ac-select-wrap {
        position: relative;
    }

    .ac-select-wrap::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #999;
        pointer-events: none;
    }

    .ac-select:focus,
    .ac-input:focus {
        border-color: #7c1d1d;
        box-shadow: 0 0 0 3px rgba(124, 29, 29, 0.12);
        background: #ffffff;
    }

    .ac-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .ac-divider {
        height: 0.5px;
        background: #e8e8e8;
        margin: 1.25rem 0;
    }

    .ac-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        height: 50px;
        background: #7c1d1d;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
        text-decoration: none;
    }

    .ac-btn:hover {
        background: #A32D2D;
    }

    .ac-btn:active {
        transform: scale(0.99);
    }
    /* ── Fix Select2 dans un input-group avec icône à gauche ── */

    /* Le select2-container doit se comporter comme un form-control dans le flex de l'input-group */
    #vehicle_id + .select2-container {
        flex: 1 1 auto;
        width: 1% !important; /* force le shrink/grow comme un form-control Bootstrap */
    }

    /* La boîte visible (celle qu'on voit et qu'on clique) */
    #vehicle_id + .select2-container .select2-selection--single {
        height: 45px !important;
        min-height: 45px;
        border: 1px solid #ced4da;
        border-left: 0;                 /* pas de double bordure avec l'icône */
        border-radius: 0 8px 8px 0 !important;
        background-color: #fff;
        display: flex;
        align-items: center;
        padding-left: 0.75rem;
    }

    /* Le texte sélectionné, centré verticalement, sans padding par défaut de Select2 */
    #vehicle_id + .select2-container .select2-selection__rendered {
        padding-left: 0;
        line-height: normal;
    }

    /* La flèche */
    #vehicle_id + .select2-container .select2-selection__arrow {
        height: 45px !important;
    }

    /* Quand le select2 est focus, simuler le focus Bootstrap classique */
    #vehicle_id + .select2-container--default.select2-container--focus .select2-selection--single,
    #vehicle_id + .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #80bdff;
        box-shadow: none; /* vous avez shadow-none sur le select d'origine */
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Select2 sur le véhicule (recherche dans la liste) ──────────────────
        $('#vehicle_id').select2({
            placeholder: '— Sélectionner —',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#vehicle_id').closest('.input-group'),
        });

    });
</script>
@section('content')
<div class="container-fluid pt-3">
    <div class="card shadow-sm rounded-lg overflow-hidden">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <div class="rot-page-header mb-0">
                <h3 class="card-title mb-0" style="font-size: 1.5rem; font-weight: 600;">
                    Rotations
                    <small class="text-muted d-block" style="font-size: 0.9rem;">
                        Gestion des tournées véhicules
                    </small>
                </h3>
            </div>

            <div class="d-flex align-items-center ml-auto">
                <button type="button" class="rot-btn mr-2" data-toggle="modal" data-target="#modalFilter">
                    <i class="fas fa-filter mr-1"></i> Filtrer
                </button>

                {{-- <a href="{{ route('reports.index') }}" class="rot-btn mr-2">
                    <svg class="rot-icon" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/>
                    </svg>
                    Rapports
                </a> --}}

                <button type="button" class="rot-btn rot-btn-success" data-toggle="modal" data-target="#exportExcelModal">
                    <svg class="rot-icon" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48">
                        <path fill="#169154" d="M29,6H15.744C14.781,6,14,6.781,14,7.744v7.259h15V6z"></path><path fill="#18482a" d="M14,33.054v7.202C14,41.219,14.781,42,15.743,42H29v-8.946H14z"></path><path fill="#0c8045" d="M14 15.003H29V24.005000000000003H14z"></path><path fill="#17472a" d="M14 24.005H29V33.055H14z"></path><g><path fill="#29c27f" d="M42.256,6H29v9.003h15V7.744C44,6.781,43.219,6,42.256,6z"></path><path fill="#27663f" d="M29,33.054V42h13.257C43.219,42,44,41.219,44,40.257v-7.202H29z"></path><path fill="#19ac65" d="M29 15.003H44V24.005000000000003H29z"></path><path fill="#129652" d="M29 24.005H44V33.055H29z"></path></g><path fill="#0c7238" d="M22.319,34H5.681C4.753,34,4,33.247,4,32.319V15.681C4,14.753,4.753,14,5.681,14h16.638 C23.247,14,24,14.753,24,15.681v16.638C24,33.247,23.247,34,22.319,34z"></path><path fill="#fff" d="M9.807 19L12.193 19 14.129 22.754 16.175 19 18.404 19 15.333 24 18.474 29 16.123 29 14.013 25.07 11.912 29 9.526 29 12.719 23.982z"></path>
                    </svg>
                    Excel
                </button>

                <button type="button" class="rot-btn rot-btn-primary" data-toggle="modal" data-target="#modalCalcul" style="margin-left: 1rem;">
                    <svg class="rot-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Calculer les rotations
                </button>
            </div>
        </div>
    </div>
    {{-- Statistiques rapides --}}
    @php
        $total      = $stats['total'];
        $completed  = $stats['completed']; 
        $inProgress = $stats['in_progress']; 
        $cancelled  = $stats['cancelled'];
    @endphp


    <div class="rot-stats mb-18">
        <div class="rot-stat">
            <div class="rot-stat-label">Total rotations</div>
            <div class="rot-stat-value">{{ $total }}</div>
            <div class="rot-stat-sub">Ce mois-ci</div>
        </div>
        <div class="rot-stat stat-success">
            <div class="rot-stat-label">Complètes</div>
            <div class="rot-stat-value">{{ $completed }}</div>
            <div class="rot-stat-sub">
                {{ $total > 0 ? round($completed / $total * 100) : 0 }}% du total
            </div>
        </div>
        <div class="rot-stat stat-info">
            <div class="rot-stat-label">En cours</div>
            <div class="rot-stat-value">{{ $inProgress }}</div>
            <div class="rot-stat-sub">Actives maintenant</div>
        </div>
        <div class="rot-stat stat-danger">
            <div class="rot-stat-label">Annulées</div>
            <div class="rot-stat-value">{{ $cancelled }}</div>
            <div class="rot-stat-sub">
                {{ $total > 0 ? round($cancelled / $total * 100) : 0 }}% du total
            </div>
        </div>
    </div>

    {{-- Calcul de rotations --}}
    <div class="modal fade" id="modalCalcul" tabindex="-1" role="dialog" aria-labelledby="modalCalculLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document"> <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border: none; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center" id="modalCalculLabel" style="font-weight: 600; letter-spacing: 0.5px;">
                        <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <svg class="text-primary" style="width:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        Calculer les rotations
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                
                <form action="{{ route('rotations.calculate') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px; background-color: #e3f2fd; color: #0d47a1;">
                            <small><i class="fas fa-info-circle mr-1"></i> Sélectionnez les paramètres pour générer les rapports mensuels des véhicules.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Circuit de transport</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-route text-primary"></i></span>
                                    </div>
                                    <select name="circuit_id"  class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;" required>
                                        <option value="">— Sélectionner —</option>
                                        @foreach($circuits as $c)
                                            <option value="{{ $c->id }}" {{ old('circuit_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Véhicule spécifique</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-truck text-primary"></i></span>
                                    </div>
                                    <select name="vehicle_id" id="vehicle_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les camions</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Période (Année)</label>
                                <input type="number" name="year" class="form-control shadow-none" 
                                    style="border-radius: 8px; height: 45px;"
                                    value="{{ old('year', date('Y')) }}"
                                    min="2020" max="2099" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Période (Mois)</label>
                                <select name="month" class="form-control shadow-none" style="border-radius: 8px; height: 45px;" required>
                                    @foreach(range(1,12) as $m)
                                        <option value="{{ $m }}" @selected($m == (old('month', date('n'))))>
                                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 bg-white">
                        <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal" style="text-decoration: none;">Annuler</button>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px; height: 48px; font-weight: 600; background: #1a73e8;">
                            <i class="fas fa-play-circle mr-2"></i> Lancer le calcul
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="modal fade" id="modalFilter" tabindex="-1" role="dialog" aria-labelledby="modalFilterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border: none; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center" id="modalFilterLabel" style="font-weight: 600; letter-spacing: 0.5px;">
                        <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <svg class="text-primary" style="width:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </div>
                        Filtrer les rotations
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                
                <form method="GET" action="{{ route('rotations.index') }}">
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px; background-color: #e3f2fd; color: #0d47a1;">
                            <small><i class="fas fa-filter mr-1"></i> Utilisez les options ci-dessous pour affiner la liste des rotations affichées.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Par Circuit</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-route text-primary"></i></span>
                                    </div>
                                    <select name="circuit_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les circuits</option>
                                        @foreach($circuits as $c)
                                            <option value="{{ $c->id }}" {{ request('circuit_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Par Camion</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-truck text-primary"></i></span>
                                    </div>
                                    <select name="vehicle_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les camions</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Période (Mois)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-calendar-alt text-primary"></i></span>
                                    </div>
                                    <input type="month" name="month" class="form-control border-left-0 shadow-none" 
                                        style="border-radius: 0 8px 8px 0; height: 45px;"
                                        value="{{ request('month') }}">
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Statut du voyage</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-info-circle text-primary"></i></span>
                                    </div>
                                    <select name="status" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les statuts</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complète</option>
                                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 bg-white d-flex justify-content-between">
                        <a href="{{ route('rotations.index') }}" class="btn btn-link text-muted font-weight-bold" style="text-decoration: none;">
                            <i class="fas fa-undo mr-1"></i> Réinitialiser
                        </a>
                        <div>
                            <button type="button" class="btn btn-link text-muted font-weight-bold mr-3" data-dismiss="modal" style="text-decoration: none;">Annuler</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px; height: 48px; font-weight: 600; background: #1a73e8;">
                                <i class="fas fa-check-circle mr-2"></i> Appliquer les filtres
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Export Excel --}}
    <div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #0c7238 0%, #98b9a1 100%); border: none; padding: 1.5rem;">
                    <h5 class="modal-title d-flex align-items-center" id="exportExcelModalLabel" style="font-weight: 600; letter-spacing: 0.5px;">
                        <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <svg class="text-primary" style="width:24px;" xmlns="http://www.w3.org/2000/svg" fill="none"  viewBox="0 0 48 48">
                                <path fill="#169154" d="M29,6H15.744C14.781,6,14,6.781,14,7.744v7.259h15V6z"></path><path fill="#18482a" d="M14,33.054v7.202C14,41.219,14.781,42,15.743,42H29v-8.946H14z"></path><path fill="#0c8045" d="M14 15.003H29V24.005000000000003H14z"></path><path fill="#17472a" d="M14 24.005H29V33.055H14z"></path><g><path fill="#29c27f" d="M42.256,6H29v9.003h15V7.744C44,6.781,43.219,6,42.256,6z"></path><path fill="#27663f" d="M29,33.054V42h13.257C43.219,42,44,41.219,44,40.257v-7.202H29z"></path><path fill="#19ac65" d="M29 15.003H44V24.005000000000003H29z"></path><path fill="#129652" d="M29 24.005H44V33.055H29z"></path></g><path fill="#0c7238" d="M22.319,34H5.681C4.753,34,4,33.247,4,32.319V15.681C4,14.753,4.753,14,5.681,14h16.638 C23.247,14,24,14.753,24,15.681v16.638C24,33.247,23.247,34,22.319,34z"></path><path fill="#fff" d="M9.807 19L12.193 19 14.129 22.754 16.175 19 18.404 19 15.333 24 18.474 29 16.123 29 14.013 25.07 11.912 29 9.526 29 12.719 23.982z"></path>
                            </svg>
                        </div>
                        Extraction des rotations vers Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                
                <form method="GET" action="{{ route('reports.export_excel') }}">
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">
                        <div class="row">
                            <div class="col-md-6 mb-6">
                                <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Par Circuit</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-route text-success"></i></span>
                                    </div>
                                    <select name="circuit_id" class="form-control border-left-0 shadow-none" style="border-radius: 0 8px 8px 0; height: 45px;">
                                        <option value="">Tous les circuits</option>
                                        @foreach($circuits as $c)
                                            <option value="{{ $c->id }}" {{ request('circuit_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="ac-row">
                                <div class="ac-field">
                                    <label class="ac-label" for="year">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                            <rect x="1.5" y="2.5" width="9" height="8" rx="1" stroke="currentColor" stroke-width="1.2"/>
                                            <path d="M1.5 5.5h9M4 1.5v2M8 1.5v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                        </svg>
                                        Année
                                    </label>
                                    <input
                                        type="number"
                                        name="year"
                                        id="year"
                                        class="ac-input"
                                        value="{{ old('year', date('Y')) }}"
                                        min="2020"
                                        max="2099"
                                        required
                                    >
                                </div>

                                <div class="ac-field">
                                    <label class="ac-label" for="month">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                            <circle cx="6" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.2"/>
                                            <path d="M6 4.5v2.5l1.5 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Mois
                                    </label>
                                    <div class="ac-select-wrap">
                                        <select name="month" id="month" class="ac-select" required>
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ $m == old('month', date('n')) ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-4 bg-white d-flex justify-content-between">
                        <div style="display: contents">
                            <button type="button" class="btn btn-link text-muted font-weight-bold mr-3" data-dismiss="modal" style="text-decoration: none;">Annuler</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px; height: 48px; font-weight: 600; background: #0c7238;">
                                <i class="fas fa-check-circle mr-2"></i> Exporter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    

    

    {{-- Table des rotations --}}
    <div class="rot-card">
        <div class="rot-card-header">
            <span class="rot-card-title">Liste des rotations</span>
            <span class="rot-badge rot-badge-muted">{{ $rotations->total() }} entrée(s)</span>
        </div>

        <div class="rot-table-wrap">
            <table class="rot-table">
                <thead>
                    <tr>
                        <th>Camion</th>
                        <th>Circuit</th>
                        <th>Mois compté</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Effectif / Objectif</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rotations as $rotation)
                        @php
                            $objSeconds = $rotation->circuit->currentObjective()->target_duration_seconds ?? null;
                            $dur        = $rotation->duration_seconds;

                            $pct      = ($objSeconds && $dur) ? min(round($dur / $objSeconds * 100), 140) : 0;
                            $barClass = $pct > 105 ? 'over' : ($pct >= 90 ? 'good' : '');
                        @endphp
                        <tr>
                            <td>
                                <div class="rot-vehicle-name">{{ $rotation->rvehicule->name }}</div>
                                @if($rotation->rvehicule->plate_number)
                                    <div class="rot-vehicle-plate">{{ $rotation->rvehicule->plate_number }}</div>
                                @endif
                            </td>

                            <td>
                                <span class="mono" style="font-size:12px;font-weight:600">
                                    {{ $rotation->circuit->code }}
                                </span>
                            </td>

                            <td>
                                <span class="mono">{{ $rotation->counted_month ?? '—' }}</span>
                            </td>

                            <td>
                                <span class="mono">
                                    {{ $rotation->started_at?->timezone('Africa/Nairobi')->format('d/m/Y H:i:s') ?? '—' }}
                                </span>
                            </td>

                            <td>
                                <span class="mono">
                                    {{ $rotation->completed_at?->timezone('Africa/Nairobi')->format('d/m/Y H:i:s') ?? '—' }}
                                </span>
                            </td>

                            <td>
                                @if($objSeconds)
                                    <div class="rot-dur-cell">
                                        <span class="rot-dur-text">
                                            @if($dur)
                                                @durSec($dur)
                                            @else
                                                —
                                            @endif
                                            <span class="rot-dur-obj">
                                                / @durSec($objSeconds)
                                            </span>
                                        </span>
                                        <div class="rot-bar-track">
                                            <div class="rot-bar-fill {{ $barClass }}"
                                                style="width:{{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="mono">—</span>
                                @endif
                            </td>

                            <td>
                                @switch($rotation->status)
                                    @case('completed')
                                        <span class="rot-badge rot-badge-success">Complète</span>
                                        @break
                                    @case('in_progress')
                                        <span class="rot-badge rot-badge-info">En cours</span>
                                        @break
                                    @case('cancelled')
                                        <span class="rot-badge rot-badge-danger">Annulée</span>
                                        @break
                                    @default
                                        <span class="rot-badge rot-badge-muted">{{ $rotation->status }}</span>
                                @endswitch
                            </td>

                            <td>
                                <div class="rot-actions">
                                    <a href="{{ route('rotations.show', $rotation) }}"
                                    class="rot-btn rot-btn-sm">Détail</a>
                                    <form action="{{ route('rotations.destroy', $rotation) }}"
                                        method="POST"
                                        onsubmit="return confirm('Supprimer cette rotation ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rot-btn rot-btn-sm rot-btn-danger">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;color:var(--muted);padding:40px 16px;font-size:13px;">
                                Aucune rotation trouvée. Lancez un calcul ci-dessus.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rotations->hasPages())
            <div class="rot-pagination">
                <span class="rot-pg-info">
                    Page {{ $rotations->currentPage() }} sur {{ $rotations->lastPage() }}
                    — {{ $rotations->total() }} résultats
                </span>
                <div class="rot-pg-btns">
                    @if($rotations->onFirstPage())
                        <span class="rot-pg-btn" style="opacity:.35;cursor:default">‹</span>
                    @else
                        <a class="rot-pg-btn" href="{{ $rotations->previousPageUrl() }}">‹</a>
                    @endif

                    @foreach($rotations->getUrlRange(
                        max(1, $rotations->currentPage() - 2),
                        min($rotations->lastPage(), $rotations->currentPage() + 2)
                    ) as $page => $url)
                        <a class="rot-pg-btn {{ $page == $rotations->currentPage() ? 'active' : '' }}"
                        href="{{ $url }}">{{ $page }}</a>
                    @endforeach

                    @if($rotations->hasMorePages())
                        <a class="rot-pg-btn" href="{{ $rotations->nextPageUrl() }}">›</a>
                    @else
                        <span class="rot-pg-btn" style="opacity:.35;cursor:default">›</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection