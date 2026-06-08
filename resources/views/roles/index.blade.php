@extends('layouts.app')

@section('content')
    <section class="content-header bg-transparent py-3">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center p-4 bg-white w-100">
                    
                    <div class="mb-3 mb-sm-0">
                        <h1 class="h3 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            Gestion des rôles
                        </h1>
                        <p class="text-muted small mb-0 mt-1">Configurez et attribuez les permissions de l'application</p>
                    </div>
                    
                    <div class="ml-sm-auto">
                        <a class="btn btn-primary px-4 py-2 font-weight-medium shadow-sm"
                        href="{{ route('roles.create') }}"
                        style="border-radius: 8px; transition: all 0.2s ease;">
                        <i class="fas fa-plus mr-2 small"></i> @lang('crud.add_new')
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-0">
                @include('roles.table')

                {{-- <div class="card-footer clearfix">
                    <div class="float-right">
                        
                    </div>
                </div> --}}
            </div>

        </div>
    </div>

@endsection

