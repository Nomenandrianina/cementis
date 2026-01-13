@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">@lang('models/dashboards.header.index')</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">@lang('models/dashboards.header.home')</a></li>
                    <li class="breadcrumb-item active">@lang('models/dashboards.header.index')</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        {{-- <div class="card shadow-sm rounded">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <!-- Colonne gauche : Filtres et boutons -->
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap align-items-center gap-3">

                            <div class="form-group">
                                <label for="planning" class="form-label">Planning</label>
                                <select class="form-control custom-select w-auto" name="planning" id="planning">
                                    <option value="">Veuillez choisir le planning</option>
                                    @foreach($import_calendar as $calendar)
                                        <option value="{{ $calendar->id }}" {{ $calendar->id == $selectedPlanning ? 'selected' : '' }}>
                                            {{ $calendar->name }}
                                        </option>    
                                    @endforeach
                                </select>
                            </div>
                            @if (Auth::user()->role_text != "transporteur")    
                                <div class="form-group">
                                    <label for="planning" class="form-label">Transporteur</label>
                                    <select class="form-control custom-select w-auto" name="transporteur" id="transporteur">
                                        <option value="" selected>Veuillez choisir un transporteur</option>
                                        @foreach($transporteurs as $transporteur)
                                            <option value="{{ $transporteur->id }}">
                                                {{ $transporteur->nom }}
                                            </option>    
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

       
        <div class="row">
            <!-- Première ligne -->
            <div class="col-md-3">
                <a href="{{ route('transporteurs.index') }}" class="text-decoration-none">
                    <div class="card card-custom transporteur">
                        <div class="card-body card-body-transporteurs">
                            <div>
                                <h4 class="card-title-custom">Transporteurs</h4>
                                <h3>{{$totalTransporteurs}}</h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-city"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        
            <div class="col-md-3">
                <a id="vehicule-link" href="{{ route('vehicules.index', ['selectedTransporteur' => $selectedTransporteur]) }}" class="text-decoration-none">
                    <div class="card card-custom vehicule">
                        <div class="card-body card-body-vehicules">
                            <div>
                                <h4 class="card-title-custom">Véhicules</h4>
                                <h3 id="total_vehicule">{{ $totalVehicules }}</h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-truck"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        
            <div class="col-md-3">
                <a id="driver-link" href="{{ route('chauffeurs.index', ['selectedTransporteur' => $selectedTransporteur]) }}" class="text-decoration-none">
                    <div class="card card-custom chauffeur">
                        <div class="card-body card-body-chauffeurs">
                            <div>
                                <h4 class="card-title-custom">Chauffeurs</h4>
                                <h3 id="total_chauffeur">{{ $totalChauffeurs }}</h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a id="truck-calendar-link" href="{{ route('detail.truck-calendar') }}" class="text-decoration-none">
                    <div class="card card-custom chauffeur">
                        <div class="card-body card-body-chauffeurs">
                            <div>
                                <h4 class="card-title-custom">Véhicules dans le calendrier</h4>
                                <h3 id="truck_in_calendar">{{ $truck_in_calendar }}</h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <a id="driver-has-scoring-link" href="{{ route('driver.score') }}" class="text-decoration-none">
                    <div class="card card-custom scoring">
                        <div class="card-body card-body-custom">
                            <div>
                                <h4 class="card-title-custom">Nombre de chauffeurs avec score</h4>
                                <h3 id="driver_has_score">{{ $driver_has_score }}</h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Deuxième ligne -->
            <div class="col-md-3">
                <a id="driver-have-not-scoring-link" href="{{ route('detail.driver-have-not-scoring') }}" class="text-decoration-none">
                    <div class="card card-custom no-scoring">
                        <div class="card-body card-body-custom">
                            <div>
                                <h4 class="card-title-custom">Nombre de chauffeur sans score</h4>
                                <h3 id="driver_not_has_score">{{ $driver_not_has_score }}</h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a id="badge-calendar-link" href="{{ route('detail.badge-calendar') }}" class="text-decoration-none">
                    <div class="card card-custom no-scoring">
                        <div class="card-body card-body-custom">
                            <div>
                                <h4 class="card-title-custom">Nombre de badge dans le calendrier</h4>
                                <h3 id="badge_numbers_in_calendars">
                                    {{ $drivers_badge_in_calendars }}
                                </h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fas fa-id-badge"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a id="driver-match-rfid-link" href="{{ route('detail.driver-match-rfid') }}" class="text-decoration-none">
                    <div class="card card-custom no-scoring">
                        <div class="card-body card-body-custom">
                            <div>
                                <h4 class="card-title-custom">Taux d'utilisation RFID</h4>
                                <h3 id="driver_match_rfid">
                                    {{ $match_rfid->match_percentage !== null ? $match_rfid->match_percentage . ' %' : 0 }}
                                </h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a id="score-zero-link" href="{{ route('driver.detail.score.zero') }}" class="text-decoration-none">
                    <div class="card card-custom no-scoring">
                        <div class="card-body card-body-custom">
                            <div>
                                <h4 class="card-title-custom">Nombre de cas avec score 0</h4>
                                <h3 id="score_zero">
                                    {{ $score_zero }}
                                </h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-circle"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a id="score-zero-more-than-3-planning-link" href="{{ route('driver.detail.score.zero.more.than.3.plannings') }}" class="text-decoration-none">
                    <div class="card card-custom no-scoring">
                        <div class="card-body card-body-custom">
                            <div>
                                <h4 class="card-title-custom">Nombre score 0 plus de 3 trajets</h4>
                                <h3 id="score_zero_more_than_3_planning">
                                    {{ $score_zero_more_than_3_planning }}
                                </h3>
                            </div>
                            <div class="icon-container">
                                <i class="nav-icon fas fa-circle"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        
        </div> --}}

        <!-- Filters Card -->
        {{-- <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <!-- Planning Filter -->
                    <div class="col-md-4">
                        <label for="planning" class="form-label fw-semibold text-secondary mb-2">
                            <i class="fas fa-calendar-alt me-2"></i> Planning
                        </label>
                        <select class="form-select custom-select w-auto" name="planning" id="planning">
                            <option value="">Sélectionner un planning</option>
                            @foreach($import_calendar as $calendar)
                                <option value="{{ $calendar->id }}" {{ $calendar->id == $selectedPlanning ? 'selected' : '' }}>
                                    {{ $calendar->name }}
                                </option>    
                            @endforeach
                        </select>
                    </div>

                    <!-- Transporteur Filter -->
                    @if (Auth::user()->role_text != "transporteur")
                    <div class="col-md-6">
                        <label for="transporteur" class="form-label fw-semibold text-secondary mb-2">
                            <i class="fas fa-truck me-2"></i> Transporteur
                        </label>
                        <select class="form-select custom-select w-auto shadow-sm" name="transporteur" id="transporteur">
                            <option value="" selected>Sélectionner un transporteur</option>
                            @foreach($transporteurs as $transporteur)
                                <option value="{{ $transporteur->id }}">
                                    {{ $transporteur->nom }}
                                </option>    
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>
        </div> --}}
       <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <!-- Ligne principale : bouton à gauche, selects à droite -->
                <div class="d-flex align-items-end justify-content-between">
                    <!-- Bouton filtre à gauche -->
                    <button class="btn btn-outline-secondary" type="button" id="filterToggle">
                        <i class="fas fa-filter"> Filtres</i>
                    </button>

                    <!-- Contenu du filtre (hidden au départ) -->
                    <div id="filterContent" class="d-flex align-items-end gap-4" style="display: none;">
                        <!-- Planning Filter -->
                        <div class="d-flex align-items-center gap-2" style="padding: 0px 13px 0px 0px;">
                            <i class="fas fa-calendar-alt text-secondary" style="padding: 0px 7px 0px 0px;"></i>
                            <span class="text-secondary fw-semibold" style="padding: 0px 7px 0px 0px;"> Planning</span>
                            <select class="form-select custom-select w-auto shadow-sm" name="planning" id="planning">
                                <option value="">Sélectionner un planning</option>
                                @foreach($import_calendar as $calendar)
                                    <option value="{{ $calendar->id }}" {{ $calendar->id == $selectedPlanning ? 'selected' : '' }}>
                                        {{ $calendar->name }}
                                    </option>    
                                @endforeach
                            </select>
                        </div>

                        <!-- Transporteur Filter -->
                        @if (Auth::user()->role_text != "transporteur")
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-truck text-secondary" style="padding: 0px 7px 0px 0px;"></i>
                                <span class="text-secondary fw-semibold" style="padding: 0px 7px 0px 0px;"> Transporteur</span>
                                <select class="form-select custom-select w-auto shadow-sm" name="transporteur" id="transporteur">
                                    <option value="" selected>Sélectionner un transporteur</option>
                                    @foreach($transporteurs as $transporteur)
                                        <option value="{{ $transporteur->id }}">
                                            {{ $transporteur->nom }}
                                        </option>    
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>



        <!-- KPI Cards Grid -->
        <div class="row g-4 mb-4">
            
            <!-- Transporteurs Card -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('transporteurs.index') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-transporteur">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Transporteurs</p>
                                <h2 class="kpi-value">{{ $totalTransporteurs }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Véhicules Card -->
            <div class="col-xl-3 col-md-6">
                <a id="vehicule-link" href="{{ route('vehicules.index', ['selectedTransporteur' => $selectedTransporteur]) }}" class="text-decoration-none">
                    <div class="kpi-card kpi-vehicule">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Véhicules</p>
                                <h2 class="kpi-value" id="total_vehicule">{{ $totalVehicules }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Chauffeurs Card -->
            <div class="col-xl-3 col-md-6">
                <a id="driver-link" href="{{ route('chauffeurs.index', ['selectedTransporteur' => $selectedTransporteur]) }}" class="text-decoration-none">
                    <div class="kpi-card kpi-chauffeur">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Chauffeurs</p>
                                <h2 class="kpi-value" id="total_chauffeur">{{ $totalChauffeurs }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Véhicules dans calendrier Card -->
            <div class="col-xl-3 col-md-6">
                <a id="truck-calendar-link" href="{{ route('detail.truck-calendar') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-calendar">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Véhicules au calendrier</p>
                                <h2 class="kpi-value" id="truck_in_calendar">{{ $truck_in_calendar }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-neutral">
                                    <i class="fas fa-minus"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- Second Row - Performance Metrics -->
        <div class="row g-4 mb-4">

            <!-- Chauffeurs avec score -->
            <div class="col-xl-3 col-md-6">
                <a id="driver-has-scoring-link" href="{{ route('driver.score') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-success">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Chauffeurs avec score</p>
                                <h2 class="kpi-value" id="driver_has_score">{{ $driver_has_score }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Chauffeurs sans score -->
            <div class="col-xl-3 col-md-6">
                <a id="driver-have-not-scoring-link" href="{{ route('detail.driver-have-not-scoring') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-warning">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Chauffeurs sans score</p>
                                <h2 class="kpi-value" id="driver_not_has_score">{{ $driver_not_has_score }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-down">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Badge dans calendrier -->
            <div class="col-xl-3 col-md-6">
                <a id="badge-calendar-link" href="{{ route('detail.badge-calendar') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-info">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Badges au calendrier</p>
                                <h2 class="kpi-value" id="badge_numbers_in_calendars">{{ $drivers_badge_in_calendars }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-neutral">
                                    <i class="fas fa-minus"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Taux utilisation RFID -->
            <div class="col-xl-3 col-md-6">
                <a id="driver-match-rfid-link" href="{{ route('detail.driver-match-rfid') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-primary">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Taux utilisation RFID</p>
                                <h2 class="kpi-value" id="driver_match_rfid">
                                    {{ $match_rfid->match_percentage !== null ? $match_rfid->match_percentage . '%' : '0%' }}
                                </h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <!-- Third Row - Alert Metrics -->
        <div class="row g-4">

            <!-- Score 0 -->
            <div class="col-xl-6 col-md-6">
                <a id="score-zero-link" href="{{ route('driver.detail.score.zero') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-danger">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Cas avec score 0</p>
                                <h2 class="kpi-value" id="score_zero">{{ $score_zero }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-down">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

            <!-- Score 0 + de 3 trajets -->
            <div class="col-xl-6 col-md-6">
                <a id="score-zero-more-than-3-planning-link" href="{{ route('driver.detail.score.zero.more.than.3.plannings') }}" class="text-decoration-none">
                    <div class="kpi-card kpi-danger-dark">
                        <div class="kpi-content">
                            <div class="kpi-icon-bg">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="kpi-details">
                                <p class="kpi-label">Score 0 sur +3 trajets</p>
                                <h2 class="kpi-value" id="score_zero_more_than_3_planning">{{ $score_zero_more_than_3_planning }}</h2>
                            </div>
                            {{-- <div class="kpi-trend">
                                <span class="trend-badge trend-alert">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                            </div> --}}
                        </div>
                    </div>
                </a>
            </div>

        </div>
        
        
        <!-- /.row -->
        {{-- <div class="row">
            <div class="col-12 col-sm-12 col-md-12">
                <div class="card">
                    <!-- Header de la carte avec les tabs -->
                    <div class="card-header d-flex  align-items-center">
                        <!-- Navigation des Tabs dans le header -->
                        
                        <ul class="nav nav-tabs card-header-tabs flex-grow-1" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">
                                    <strong>Classement des scores</strong> 
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="vehicule-tab" data-bs-toggle="tab" data-bs-target="#vehicule" type="button" role="tab" aria-controls="vehicule" aria-selected="false">
                                    <strong>Répartition des véhicules par transporteurs</strong> 
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="chauffeur-tab" data-bs-toggle="tab" data-bs-target="#chauffeur" type="button" role="tab" aria-controls="chauffeur" aria-selected="false">
                                    <strong>Répartition des chauffeurs par transporteurs</strong> 
                                </button>
                            </li>
                        </ul>
        
                        <!-- Boutons de gestion de la carte -->
                        <div class="card-tools d-flex justify-content-end">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
        
                    <div class="card-body">
                        <!-- Contenu des Tabs -->
                        <div class="tab-content mt-3" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h1 class="card-title" style="padding-left: 31px;"><i class="fas fa-medal" style="color: #eded3c;"></i> Meilleur Scoring </h1>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                        
                                            <div class="card-body">
                                                <div class="card-body" id="best_scoring_container">
                                                    @include('dashboard.best_scoring', ['best_scoring' => $best_scoring, 'selectedPlanning' => $selectedPlanning])
                                                </div>
                                            </div>
                                            <!-- /.card-header -->
                                        </div>
                                        <!-- /.card -->
                                    </div>
                        
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h1 class="card-title" style="padding-left: 31px;"><i class="fas fa-exclamation-triangle" style="color: red;"></i> Moins Bon Scoring </h1>
                        
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                        
                                            <div class="card-body">
                                                <div class="card-body" id="bad_scoring_container">
                                                    @include('dashboard.bad_scoring', ['best_scoring' => $bad_scoring, 'selectedPlanning' => $selectedPlanning])
                                                </div>
                                            </div>
                                            <!-- /.card-header -->
                                        </div>
                                        <!-- /.card -->
                                    </div>
                                </div> 
                            </div>
                            <div class="tab-pane fade" id="vehicule" role="tabpanel" aria-labelledby="vehicule-tab">
                                <canvas id="vehiculeChart" ></canvas>
                            </div>
                            <div class="tab-pane fade" id="chauffeur" role="tabpanel" aria-labelledby="chauffeur-tab">
                                <canvas id="chauffeurChart" ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>

        </div> --}}

        <div class="row" style="padding: 5px;">
            <div class="col-12">
                <!-- Main Dashboard Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    
                    <!-- Modern Tabs Header -->
                    <div class="card-header bg-transparent border-0 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <ul class="nav nav-pills modern-tabs" id="dashboardTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-link active" id="scores-tab" data-bs-toggle="tab" data-bs-target="#scores" type="button" role="tab" aria-controls="scores" aria-selected="true">
                                        <i class="fas fa-trophy me-2"></i>
                                        <span>Classement des Scores</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-link" id="vehicules-tab" data-bs-toggle="tab" data-bs-target="#vehicules" type="button" role="tab" aria-controls="vehicules" aria-selected="false">
                                        <i class="fas fa-truck me-2"></i>
                                        <span>Répartition Véhicules</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tab-link" id="chauffeurs-tab" data-bs-toggle="tab" data-bs-target="#chauffeurs" type="button" role="tab" aria-controls="chauffeurs" aria-selected="false">
                                        <i class="fas fa-users me-2"></i>
                                        <span>Répartition Chauffeurs</span>
                                    </button>
                                </li>
                            </ul>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light btn-sm rounded-circle" data-card-widget="collapse" title="Réduire">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-light btn-sm rounded-circle" data-card-widget="remove" title="Fermer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Content -->
                    <div class="card-body p-4">
                        <div class="tab-content" id="dashboardTabsContent">
                            
                            <!-- Tab 1: Classement des Scores -->
                            <div class="tab-pane fade show active" id="scores" role="tabpanel" aria-labelledby="scores-tab">
                                <div class="row g-4">
                                    
                                    <!-- Meilleur Scoring -->
                                    <div class="col-lg-6">
                                        <div class="ranking-card best-ranking">
                                            <div class="ranking-header">
                                                <div class="ranking-icon best">
                                                    <i class="fas fa-trophy"></i>
                                                </div>
                                                <div class="ranking-title">
                                                    <h4 class="mb-1 fw-bold">Meilleurs Scores</h4>
                                                    <p class="text-muted mb-0 small">Top performers du classement</p>
                                                </div>
                                                <div class="ranking-actions ms-auto">
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="ranking-body" id="best_scoring_container">
                                                @include('dashboard.best_scoring', ['best_scoring' => $best_scoring, 'selectedPlanning' => $selectedPlanning])
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Moins Bon Scoring -->
                                    <div class="col-lg-6">
                                        <div class="ranking-card worst-ranking">
                                            <div class="ranking-header">
                                                <div class="ranking-icon worst">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </div>
                                                <div class="ranking-title">
                                                    <h4 class="mb-1 fw-bold">Scores à Améliorer</h4>
                                                    <p class="text-muted mb-0 small">Nécessitent une attention particulière</p>
                                                </div>
                                                <div class="ranking-actions ms-auto">
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="ranking-body" id="bad_scoring_container">
                                                @include('dashboard.bad_scoring', ['best_scoring' => $bad_scoring, 'selectedPlanning' => $selectedPlanning])
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Tab 2: Répartition Véhicules -->
                            <div class="tab-pane fade" id="vehicules" role="tabpanel" aria-labelledby="vehicules-tab">
                                <div class="chart-container">
                                    <div class="chart-header mb-4">
                                        <h5 class="fw-bold text-dark mb-1">
                                            <i class="fas fa-truck text-primary me-2"></i>
                                            Répartition des Véhicules par Transporteur
                                        </h5>
                                        <p class="text-muted mb-0">Analyse de la distribution de la flotte</p>
                                    </div>
                                    <div class="chart-wrapper">
                                        <canvas id="vehiculeChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Répartition Chauffeurs -->
                            <div class="tab-pane fade" id="chauffeurs" role="tabpanel" aria-labelledby="chauffeurs-tab">
                                <div class="chart-container">
                                    <div class="chart-header mb-4">
                                        <h5 class="fw-bold text-dark mb-1">
                                            <i class="fas fa-users text-success me-2"></i>
                                            Répartition des Chauffeurs par Transporteur
                                        </h5>
                                        <p class="text-muted mb-0">Analyse de la distribution du personnel</p>
                                    </div>
                                    <div class="chart-wrapper">
                                        <canvas id="chauffeurChart"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- /.row -->
    </div>
    <!--/. container-fluid -->
</section>

<!-- /.content -->
@endsection

@push('third_party_scripts')
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js" integrity="sha512-asxKqQghC1oBShyhiBwA+YgotaSYKxGP1rcSYTDrB0U6DxwlJjU59B67U8+5/++uFjcuVM8Hh5cokLjZlhm3Vg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
@endpush
@push('page_scripts')

<script>

    $(document).ready(function () {
        function updateDashboard(selectedPlanning, selectedTransporteur) {
            $('#overlay').show();
            $('#loader').show();
            $.ajax({
                url: "{{ route('dashboard') }}",
                type: "GET",
                data: { 
                    selectedPlanning: selectedPlanning,
                    selectedTransporteur: selectedTransporteur
                },
                success: function (response) {
                    console.log(response);
                    $('#driver_has_score').text(response.driver_has_score);
                    $('#driver_not_has_score').text(response.driver_not_has_score);
                    $('#truck_in_calendar').text(response.truck_in_calendar);
                    $('#total_chauffeur').text(response.total_chauffeur);
                    $('#total_vehicule').text(response.total_vehicule);
                    $('#badge_numbers_in_calendars').text(response.drivers_badge_in_calendars);
                    $('#driver_in_calendar').text(response.driver_in_calendar);
                    $('#best_scoring_container').html(response.best_scoring);
                    $('#bad_scoring_container').html(response.bad_scoring);
                    $('#score_zero').html(response.score_zero);
                    $('#score_zero_more_than_3_planning').html(response.score_zero_more_than_3_planning);

                    let percentage = response.match_rfid.match_percentage;
                    let displayValue = (percentage !== null && percentage !== undefined) ? percentage + ' %' : '0';
                    $('#driver_match_rfid').html(displayValue);

                    $('#overlay').hide();
                    $('#loader').hide();
                }
            });
        }

        // Filtre Planning
        $('#planning').change(function () {
            let selectedPlanning = $(this).val();
            let selectedTransporteur = $('#transporteur').val();
            updateDashboard(selectedPlanning, selectedTransporteur);
        });

        // Filtre Transporteur
        $('#transporteur').change(function () {
            let selectedPlanning = $('#planning').val();
            let selectedTransporteur = $(this).val();
            updateDashboard(selectedPlanning, selectedTransporteur);
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const planningSelect = document.getElementById("planning");
        const transporteurSelect = document.getElementById("transporteur");
        
        // Récupérer tous les liens à mettre à jour
        const linksToUpdate = [
            document.getElementById("driver-match-rfid-link"),
            document.getElementById("score-zero-link"),
            document.getElementById("score-zero-more-than-3-planning-link"),
            document.getElementById("badge-calendar-link"),
            document.getElementById("driver-has-scoring-link"),
            document.getElementById("driver-have-not-scoring-link"),
            document.getElementById("truck-calendar-link"),
            document.getElementById("driver-link"),
            document.getElementById("vehicule-link"),
        ];

        function updateLinks() {
            const planning = planningSelect.value;
            const transporteur = transporteurSelect.value;

            const params = [];
            if (planning) params.push(`id_planning=${planning}`);
            if (transporteur) params.push(`id_transporteur=${transporteur}`);

            // Parcourir tous les liens à mettre à jour
            linksToUpdate.forEach(link => {
                if (link) {
                    // Récupérer l'URL existante du lien
                    const currentUrl = new URL(link.href);  // Crée un objet URL à partir du href actuel
                    
                    // Ajouter ou mettre à jour les paramètres existants
                    params.forEach(param => {
                        // Extraire le nom du paramètre
                        const paramName = param.split('=')[0];
                        const paramValue = param.split('=')[1];

                        // Vérifier si le paramètre existe déjà dans l'URL
                        if (currentUrl.searchParams.has(paramName)) {
                            // Si le paramètre existe déjà, le mettre à jour
                            currentUrl.searchParams.set(paramName, paramValue);
                        } else {
                            // Sinon, ajouter le paramètre
                            currentUrl.searchParams.append(paramName, paramValue);
                        }
                    });

                    // Mettre à jour le href du lien avec les nouveaux paramètres
                    link.href = currentUrl.toString();

                    console.log("Lien mis à jour : ", link.href);  // Pour vérifier
                }
            });
        }

        // Mise à jour au chargement
        updateLinks();

        // Mise à jour à chaque changement
        planningSelect.addEventListener("change", updateLinks);
        transporteurSelect.addEventListener("change", updateLinks);
    });




    // document.addEventListener("DOMContentLoaded", function() {
    //     const planningSelect = document.getElementById("planning");
    //     const transporteurSelect = document.getElementById("transporteur");
    //     const rfidLink = document.getElementById("driver-match-rfid-link");
    //     console.log(rfidLink);
    //     function updateRfidLink() {
    //         const planning = planningSelect.value;
    //         const transporteur = transporteurSelect.value;

    //         const params = [];
    //         if (planning) params.push(`id_planning=${planning}`);
    //         if (transporteur) params.push(`id_transporteur=${transporteur}`);

    //         rfidLink.href = params.length 
    //             ? `{{ route('detail.driver-match-rfid') }}?${params.join("&")}` 
    //             : `{{ route('detail.driver-match-rfid') }}`;

    //         console.log("Lien RFID mis à jour :", rfidLink.href); // pour vérifier
    //     }

    //     // Mise à jour au chargement
    //     updateRfidLink();

    //     // Mise à jour à chaque changement
    //     planningSelect.addEventListener("change", updateRfidLink);
    //     transporteurSelect.addEventListener("change", updateRfidLink);
    // });

    // document.addEventListener("DOMContentLoaded", function() {
    //     let select = document.getElementById("planning");
    //     let links = {
    //         "driver-not-having-scoring": "{{ route('detail.driver-have-not-scoring') }}",
    //         "driver-having-scoring": "{{ route('detail.driver-has-scoring') }}",
    //         "truck-in-calendar": "{{ route('detail.truck-calendar') }}",
    //         "badge-in-calendar": "{{ route('detail.badge-calendar') }}",
    //     };

    //     function updateLinks() {
    //         let selectedValue = select.value;
    //         for (let id in links) {
    //             let linkElement = document.getElementById(id);
    //             if (linkElement) {
    //                 linkElement.href = selectedValue ? `${links[id]}?id_planning=${selectedValue}` : links[id];
    //             }
    //         }
    //     }

    //     // Mettre à jour les liens au chargement de la page
    //     updateLinks();

    //     // Mettre à jour les liens lorsqu'on change la sélection
    //     select.addEventListener("change", updateLinks);
    // });

    
    Chart.register(ChartDataLabels);
// ---------------------------------- CHART TRANSPORTEUR VEHICULE-------------------------------------------
    var ctx = document.getElementById('vehiculeChart').getContext('2d');
    var vehicules = @json($vehicule_transporteur); // On récupère les données

    // Extraire les noms, le nombre de véhicules et de chauffeurs
    var labels = vehicules.map(t => t.nom);
    var vehiculesData = vehicules.map(t => t.vehicule_count);

    var vehiculeChart  = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                    {
                        label: 'Nombre de véhicules',
                        data: vehiculesData, // Véhicules par transporteur
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', // Rouge
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
        },
        options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                animation: {
                    duration: 0 // Désactivation des animations pour améliorer les performances
                },
                scales: {
                    x: {
                        beginAtZero: true,
                    }
                },
                plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 16, // Taille de la police pour la légende
                            family: 'Arial', // Police de caractères
                            weight: 'bold', // Poids de la police (ex. 'normal', 'bold')
                            lineHeight: 1.2 // Hauteur de ligne
                        },
                        color: '#333' // Couleur de la légende
                    }
                },
                datalabels: {
                    anchor: 'center', // Positionnement du texte
                    align: 'center', // Alignement du texte
                    color: '#000', // Couleur du texte
                    font: {
                        size: 15, // Taille de la police pour la légende
                        family: 'Arial', // Police de caractères
                        weight: 'bold', // Poids de la police (ex. 'normal', 'bold')
                        lineHeight: 1.2 // Hauteur de ligne
                    },
                }
            }
        }
    });
// ---------------------------------------------------------------------------------------------------

// ---------------------------------- CHART TRANSPORTEUR CHAUFFEUR-------------------------------------------
var ctx = document.getElementById('chauffeurChart').getContext('2d');
    var chauffeurs = @json($driver_transporteur); // On récupère les données

    // Extraire les noms, le nombre de véhicules et de chauffeurs
    var labels = chauffeurs.map(t => t.nom);
    var chauffeurData = chauffeurs.map(t => t.chauffeurs_count);

    
    var chauffeurChart  = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                    {
                        label: 'Nombre de chauffeurs',
                        data: chauffeurData, 
                        backgroundColor: 'rgba(75, 192, 75, 0.2)', 
                        borderColor: 'rgba(75, 192, 75, 1)', 
                        borderWidth: 1,
                    }
                ]
        },
        options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        beginAtZero: true,
                    }
                },
                plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 16, // Taille de la police pour la légende
                            family: 'Arial', // Police de caractères
                            weight: 'bold', // Poids de la police (ex. 'normal', 'bold')
                            lineHeight: 1.2 // Hauteur de ligne
                        },
                        color: '#333' // Couleur de la légende
                    }
                },

                datalabels: {
                    anchor: 'center', // Positionnement du texte
                    align: 'center', // Alignement du texte
                    color: '#000', // Couleur du texte
                    font: {
                        size: 15, // Taille de la police pour la légende
                        family: 'Arial', // Police de caractères
                        weight: 'bold', // Poids de la police (ex. 'normal', 'bold')
                        lineHeight: 1.2 // Hauteur de ligne
                    },
                }
            }
        }
    });
// ---------------------------------------------------------------------------------------------------
    
</script>



<style>
    /* KPI Card Styles */
    .kpi-card {
        position: relative;
        border-radius: 1.25rem;
        padding: 1.75rem;
        background: white;
        border: 2px solid #e5e7eb;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        height: 100%;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        transition: height 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        border-color: transparent;
    }

    .kpi-card:hover::before {
        height: 8px;
    }

    .kpi-content {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
    }

    .kpi-icon-bg {
        width: 70px;
        height: 70px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-icon-bg {
        transform: scale(1.1) rotate(5deg);
    }

    .kpi-details {
        flex-grow: 1;
    }

    .kpi-label {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        opacity: 0.8;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }

    .kpi-trend {
        position: absolute;
        top: -0.5rem;
        right: -0.5rem;
    }

    .trend-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Color Themes */

    /* Transporteur - Blue */
    .kpi-transporteur::before { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .kpi-transporteur .kpi-icon-bg { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; }
    .kpi-transporteur .kpi-label { color: #1e40af; }
    .kpi-transporteur .kpi-value { color: #1e3a8a; }

    /* Vehicule - Purple */
    .kpi-vehicule::before { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .kpi-vehicule .kpi-icon-bg { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; }
    .kpi-vehicule .kpi-label { color: #6d28d9; }
    .kpi-vehicule .kpi-value { color: #5b21b6; }

    /* Chauffeur - Indigo */
    .kpi-chauffeur::before { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .kpi-chauffeur .kpi-icon-bg { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4338ca; }
    .kpi-chauffeur .kpi-label { color: #4338ca; }
    .kpi-chauffeur .kpi-value { color: #3730a3; }

    /* Calendar - Teal */
    .kpi-calendar::before { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .kpi-calendar .kpi-icon-bg { background: linear-gradient(135deg, #ccfbf1, #99f6e4); color: #0f766e; }
    .kpi-calendar .kpi-label { color: #0f766e; }
    .kpi-calendar .kpi-value { color: #115e59; }

    /* Success - Green */
    .kpi-success::before { background: linear-gradient(135deg, #10b981, #059669); }
    .kpi-success .kpi-icon-bg { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #047857; }
    .kpi-success .kpi-label { color: #047857; }
    .kpi-success .kpi-value { color: #065f46; }

    /* Warning - Orange */
    .kpi-warning::before { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .kpi-warning .kpi-icon-bg { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; }
    .kpi-warning .kpi-label { color: #b45309; }
    .kpi-warning .kpi-value { color: #92400e; }

    /* Info - Cyan */
    .kpi-info::before { background: linear-gradient(135deg, #06b6d4, #0891b2); }
    .kpi-info .kpi-icon-bg { background: linear-gradient(135deg, #cffafe, #a5f3fc); color: #0e7490; }
    .kpi-info .kpi-label { color: #0e7490; }
    .kpi-info .kpi-value { color: #155e75; }

    /* Primary - Blue Gradient */
    .kpi-primary::before { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }
    .kpi-primary .kpi-icon-bg { background: linear-gradient(135deg, #dbeafe, #ede9fe); color: #4338ca; }
    .kpi-primary .kpi-label { color: #4338ca; }
    .kpi-primary .kpi-value { color: #3730a3; }

    /* Danger - Red */
    .kpi-danger::before { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .kpi-danger .kpi-icon-bg { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #b91c1c; }
    .kpi-danger .kpi-label { color: #b91c1c; }
    .kpi-danger .kpi-value { color: #991b1b; }

    /* Danger Dark - Deep Red */
    .kpi-danger-dark::before { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .kpi-danger-dark .kpi-icon-bg { background: linear-gradient(135deg, #fecaca, #fca5a5); color: #991b1b; }
    .kpi-danger-dark .kpi-label { color: #991b1b; }
    .kpi-danger-dark .kpi-value { color: #7f1d1d; }

    /* Trend Badges */
    .trend-up { background: linear-gradient(135deg, #10b981, #059669); color: white; }
    .trend-down { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
    .trend-neutral { background: linear-gradient(135deg, #6b7280, #4b5563); color: white; }
    .trend-alert { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }

    /* Animations */
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
        50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
    }

    .kpi-card:hover .trend-badge {
        animation: pulse-glow 2s infinite;
    }

    /* Form Select Enhancements */
    .form-select {
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .kpi-card {
            padding: 1.25rem;
        }
        
        .kpi-icon-bg {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .kpi-value {
            font-size: 1.75rem;
        }
        
        .kpi-label {
            font-size: 0.75rem;
        }
    }

    /* Link Hover Effect */
    a:hover .kpi-card {
        border-color: currentColor;
    }
</style>

<style>
    /* Modern Tabs Navigation */
    .modern-tabs {
        background: #f8f9fa;
        padding: 0.5rem;
        border-radius: 1rem;
        gap: 0.5rem;
    }

    

    .modern-tabs .tab-link {
        border: none;
        background: transparent;
        color: #6b7280;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .modern-tabs .tab-link:hover {
        background: white;
        color: #3b82f6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .modern-tabs .tab-link.active {
        background: white;
        color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }

    .modern-tabs .tab-link i {
        font-size: 1rem;
    }

    /* Ranking Cards */
    .ranking-card {
        background: white;
        border-radius: 1.25rem;
        border: 2px solid #e5e7eb;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }

    .ranking-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .ranking-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-bottom: 2px solid #e5e7eb;
    }

    .ranking-icon {
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .ranking-icon.best {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
        animation: shine 3s infinite;
    }

    .ranking-icon.worst {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .ranking-title h4 {
        font-size: 1.25rem;
        color: #1f2937;
    }

    .ranking-body {
        padding: 1.5rem;
        max-height: 500px;
        overflow-y: auto;
    }

    /* Best Ranking Accent */
    .best-ranking {
        border-top: 4px solid #fbbf24;
    }

    .best-ranking:hover {
        border-color: #f59e0b;
    }

    /* Worst Ranking Accent */
    .worst-ranking {
        border-top: 4px solid #ef4444;
    }

    .worst-ranking:hover {
        border-color: #dc2626;
    }

    /* Chart Container */
    .chart-container {
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        border: 2px solid #e5e7eb;
    }

    .chart-header {
        padding-bottom: 1rem;
        border-bottom: 2px solid #f3f4f6;
    }

    .chart-wrapper {
        position: relative;
        min-height: 400px;
        padding: 1rem 0;
    }

    .chart-wrapper canvas {
        max-height: 450px;
    }

    /* Action Buttons */
    .btn-light.rounded-circle {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    .btn-light.rounded-circle:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: scale(1.1);
    }

    /* Animations */
    /* @keyframes shine {
        0%, 100% {
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
        }
        50% {
            box-shadow: 0 4px 25px rgba(251, 191, 36, 0.7);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tab-pane.show {
        animation: fadeIn 0.4s ease;
    } */

    .ranking-body::-webkit-scrollbar {
        width: 6px;
    }

    .ranking-body::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 10px;
    }

    .ranking-body::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    .ranking-body::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .modern-tabs {
            flex-direction: column;
            align-items: stretch;
        }

        .modern-tabs .tab-link {
            justify-content: center;
        }

        .ranking-header {
            flex-wrap: wrap;
        }

        .ranking-actions {
            width: 100%;
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
        }
    }

    @media (max-width: 768px) {
        .modern-tabs .tab-link span {
            font-size: 0.875rem;
        }

        .ranking-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        .ranking-title h4 {
            font-size: 1rem;
        }

        .chart-wrapper {
            min-height: 300px;
        }
    }

    /* Card Shadow Enhancement */
    .card.shadow-sm {
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
    }


    .tab-content {
        padding-top: 1rem;
    }


    .ranking-card .ranking-body:hover {
        background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    }


    .chart-wrapper.loading {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chart-wrapper.loading::after {
        content: "Chargement des données...";
        color: #9ca3af;
        font-size: 1rem;
    }
</style>

<script>
    // Optional: Add smooth scroll behavior for ranking bodies
    document.addEventListener('DOMContentLoaded', function() {
        const rankingBodies = document.querySelectorAll('.ranking-body');
        
        rankingBodies.forEach(body => {
            body.style.scrollBehavior = 'smooth';
        });
        
        // Optional: Log tab changes
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(e) {
                console.log('Tab switched to:', e.target.getAttribute('aria-controls'));
            });
        });
    });

    const filterToggle = document.getElementById('filterToggle');
    const filterContent = document.getElementById('filterContent');

    filterToggle.addEventListener('click', () => {
        filterContent.style.display = filterContent.style.display === "none" ? "flex" : "none";
    });

     
</script>


@endpush
