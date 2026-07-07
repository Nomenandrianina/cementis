@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                   <h1>Liste des importations des chauffeurs à jour </h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-secondary float-right"
                       href="{{ route('import.installation.affichage') }}">
                       Téléverser un fichier
                    </a>
                    <a class="btn btn-primary float-right" style="margin-right: 3px;"
                       href="{{ route('import.installation.last') }}">
                       Utiliser le dernier fichier téléversé
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info">
                {{ session('info') }}
            </div>
        @endif


        <div class="clearfix"></div>

        <div class="card">
            <div class="card-body p-0">
                @include('import_name_installations.table')

                <div class="card-footer clearfix float-right">
                    <div class="float-right">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


