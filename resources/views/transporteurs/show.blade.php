{{-- @extends('layouts.app')

@section('content')
    
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="h3 mb-2 text-gray-800" style="font-weight: 400;">@lang('models/transporteurs.singular') : {{ $transporteur->nom }}</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('transporteurs.index') }}">
                         @lang('crud.back')
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-header py-3">
                <h5 class="-0 font-weight-bold text-primary">Liste chauffeurs</h5>
            
            </div>

            <div class="card-body">
                <div class="row">
                    <table class="table table-striped table-bordered dataTable no-footer">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <input  type="checkbox" id="select-all">
                                </th>
                                <th scope="col">Rfid</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Transporteur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chauffeur as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-checkbox" name="selected_chauffeurs[]" value="{{ $item->id }}">
                                    </td>
                                    <td>{{ $item->rfid }}</td>
                                    <td>{{ $item->nom }}</td>
                                    <td>
                                        @if ($item->transporteur)
                                            {{ $item->transporteur->nom }}
                                        @else
                                            
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary" id="get-selected" onclick="update_transporteurid({{ $transporteur->id }})">Valider</button>
                </div>
            </div>
        </div>
    </div>

@endsection --}}

@extends('layouts.app')

@section('content')
    <div class="container-fluid py-5 px-md-5" style="background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; color: #09090b;">
        
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-5 gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('transporteurs.index') }}" class="text-muted text-decoration-none small">Transporteurs</a></li>
                        <li class="breadcrumb-item active small text-secondary" aria-current="page">Affectation</li>
                    </ol>
                </nav>
                <h1 class="h3 font-weight-bold mb-1" style="letter-spacing: -0.025em;">
                    @lang('models/transporteurs.singular') : <span class="text-secondary font-weight-medium">{{ $transporteur->nom }}</span>
                </h1>
                <p class="text-muted small mb-0">Sélectionnez les chauffeurs à associer à ce transporteur.</p>
            </div>
            
            <div>
                <a class="btn btn-white text-secondary border rounded-lg px-4 py-2 shadow-sm font-weight-medium transition-all" 
                   style="background: #ffffff; font-size: 0.9rem; border-color: #e4e4e7 !important;"
                   href="{{ route('transporteurs.index') }}">
                    <i class="fas fa-chevron-left mr-2" style="font-size: 0.8rem;"></i> @lang('crud.back')
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                
                <div class="card border-0 shadow-sm rounded-xl overflow-hidden mb-4" style="border-radius: 8px; background: #ffffff; border: 1px solid #e4e4e7 !important;">
                    
                    <div class="px-4 py-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                        <h5 class="m-0 font-weight-semibold text-dark d-flex align-items-center" style="font-size: 1rem; letter-spacing: -0.01em;">
                            <i class="fas fa-users mr-2 text-muted" style="font-size: 0.9rem;"></i> Liste des chauffeurs disponibles
                        </h5>
                        <span id="selected-count" class="badge badge-light border text-secondary px-2.5 py-1.5 rounded-pill font-weight-medium" style="font-size: 0.75rem;">
                            0 sélectionné(s)
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table alpha-modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="modern-checkbox-wrapper">
                                                <input type="checkbox" id="select-all" class="modern-cb">
                                            </div>
                                        </th>
                                        <th scope="col">Code RFID</th>
                                        <th scope="col">Nom complet</th>
                                        <th scope="col">Transporteur actuel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($chauffeur as $item)
                                        <tr>
                                            <td>
                                                <div class="modern-checkbox-wrapper">
                                                    <input type="checkbox" class="select-checkbox modern-cb" name="selected_chauffeurs[]" value="{{ $item->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-monospace bg-light px-2 py-1 rounded text-secondary" style="font-size: 0.85rem; border: 1px solid #f1f1f4;">
                                                    {{ $item->rfid }}
                                                </span>
                                            </td>
                                            <td class="font-weight-medium text-dark">{{ $item->nom }}</td>
                                            <td>
                                                @if ($item->transporteur)
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-light border text-secondary" style="border-radius: 9999px;">
                                                        {{ $item->transporteur->nom }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small-italic" style="font-size: 0.85rem; font-style: italic;">Aucun</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-4 py-4 border-top d-flex flex-row-reverse bg-light" style="border-color: #e4e4e7 !important; background-color: #fafafa !important;">
                        <button type="submit" 
                                class="btn btn-dark btn-modern shadow-sm font-weight-medium px-4 py-2" 
                                id="get-selected" 
                                onclick="update_transporteurid({{ $transporteur->id }})">
                            <i class="fas fa-check mr-2" style="font-size: 0.85rem;"></i> Valider les affectations
                        </button>
                    </div>

                </div> 
            </div> 
        </div> 
    </div>
@endsection