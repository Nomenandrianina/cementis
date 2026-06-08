@extends('layouts.app')

@section('content')
    {{-- <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h1>@lang('models/chauffeurs.plural')</h1>
                </div>

                <div class="col-md-6 d-flex justify-content-end gap-2">
                    <div class="mr-2">
                        <select id="filter-planning" class="form-control">
                            <option value="">Filtrer par planning</option>
                            @foreach($plannings as $planning)
                                <option value="{{ $planning->id }}" {{ $selected_planning == $planning->id ? 'selected' : '' }}>{{ $planning->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @can('chauffeurs.create')
                        <a class="btn btn-primary" href="{{ route('chauffeurs.create') }}">
                            @lang('crud.add_new')
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </section> --}}
    <section class="content-header bg-transparent py-3">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center p-4 bg-white w-100">
                    
                    <div class="mb-3 mb-sm-0">
                        <h1 class="h3 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            Gestion des chauffeurs
                        </h1>
                        <p class="text-muted small mb-0 mt-1">Consultez l'annuaire des conducteurs, leurs numéros de badge et leurs transporteurs d'appartenance</p>
                    </div>
                    
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 ml-sm-auto">
                        
                        <div class="mb-2 mb-sm-0 mr-sm-2">
                            <select id="filter-planning" class="form-control shadow-sm" style="border-radius: 8px; min-width: 200px;">
                                <option value="">Filtrer par planning</option>
                                @foreach($plannings as $planning)
                                    <option value="{{ $planning->id }}" {{ $selected_planning == $planning->id ? 'selected' : '' }}>
                                        {{ $planning->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @can('chauffeurs.create')
                            <div>
                                <a class="btn btn-primary px-4 py-2 font-weight-medium shadow-sm w-100"
                                href="{{ route('chauffeurs.create') }}"
                                style="border-radius: 8px; transition: all 0.2s ease; white-space: nowrap;">
                                <i class="fas fa-plus mr-2 small"></i> @lang('crud.add_new')
                                </a>
                            </div>
                        @endcan

                    </div>

                </div>
            </div>
        </div>
    </section>


    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-0">
                @include('chauffeurs.table')

                <div class="card-footer clearfix float-right">
                    <div class="float-right">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('filter-planning').addEventListener('change', function() {
            let planningId = this.value;
            let url = "{{ route('chauffeurs.index') }}"; // Remplace par la route de ta page
            if (planningId) {
                window.location.href = url + '?id_planning=' + planningId;
            } else {
                window.location.href = url; // si "aucun filtre"
            }
        });
    </script>

@endsection


