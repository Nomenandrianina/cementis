{{-- @extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>@lang('models/chauffeurs.singular')</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('chauffeurs.index') }}">
                         @lang('crud.back')
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                @include('chauffeurs.show_fields')
            </div>
        </div>
    </div>
@endsection --}}
@extends('layouts.app')

@section('content')
    <section class="content-header bg-transparent py-3">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center p-4 bg-white w-100">
                    
                    <div class="mb-3 mb-sm-0">
                        <h1 class="h3 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            Détails du chauffeur <span class="text-primary">{{ $chauffeur_actuel->nom }}</span>
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Fiche d'identité du conducteur et historique complet des affectations de badges.
                        </p>
                    </div>
                    
                    <div class="ml-sm-auto">
                        <a class="btn btn-outline-secondary px-4 py-2 font-weight-medium"
                           href="{{ route('chauffeurs.index') }}"
                           style="border-radius: 8px; transition: all 0.2s ease;">
                           <i class="fas fa-arrow-left mr-2 small"></i> @lang('crud.back')
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('chauffeurs.show_fields')
    </div>
@endsection