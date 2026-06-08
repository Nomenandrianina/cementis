@extends('layouts.app')

@section('content')
    <section class="content-header bg-transparent py-3">
        <div class="container-fluid">
            <!-- La Card Moderne -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
                <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center p-4 bg-white w-100">
                    
                    <!-- Bloc de gauche : Titre + Description -->
                    <div class="mb-3 mb-sm-0">
                        <h1 class="h3 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                            Créer une permission
                        </h1>
                        <p class="text-muted small mb-0 mt-1">Déclarez une nouvelle clé d'autorisation pour restreindre ou accorder l'accès aux fonctionnalités</p>
                    </div>
                    
                    <!-- Bloc de droite : Bouton de retour (Poussé tout à fait à droite grâce à ml-sm-auto) -->
                    <div class="ml-sm-auto">
                        <a class="btn btn-outline-secondary px-4 py-2 font-weight-medium"
                        href="{{ route('permissions.index') }}"
                        style="border-radius: 8px; transition: all 0.2s ease;">
                        <i class="fas fa-arrow-left mr-2 small"></i> Retour à la liste
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::open(['route' => 'permissions.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('permissions.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Enregistrer', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('permissions.index') }}" class="btn btn-default">Annuler</a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
