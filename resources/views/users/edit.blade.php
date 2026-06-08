{{-- @extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Edit User</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('users.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('users.index') }}" class="btn btn-default">Cancel</a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection --}}
@extends('layouts.app')

@section('content')
<section class="content-header bg-transparent py-3">
    <div class="container-fluid">
        <!-- La Card Moderne de Modification -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px !important; overflow: hidden;">
            <div class="card-body d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center p-4 bg-white w-100">
                
                <!-- Bloc de gauche : Fil d'Ariane + Titre + Description -->
                <div class="mb-3 mb-sm-0">
                    <!-- Fil d'Ariane (Breadcrumb) -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-2">
                            <li class="breadcrumb-item">
                                <a href="{{ route('users.index') }}" class="text-muted text-decoration-none small">Utilisateurs</a>
                            </li>
                            <li class="breadcrumb-item active small text-secondary" aria-current="page">Modifier</li>
                        </ol>
                    </nav>
                    
                    <!-- Titre Principal -->
                    <h1 class="h3 font-weight-bold text-dark mb-0" style="letter-spacing: -0.5px;">
                        Modifier le profil <span class="text-primary">{{ $user->name ?? '' }}</span>
                    </h1>
                    
                    <!-- Sous-texte -->
                    <p class="text-muted small mb-0 mt-1">
                        Mettez à jour les informations de compte et les rôles de l'utilisateur.
                    </p>
                </div>
                
                <!-- Bloc de droite : Bouton d'action de retour -->
                <div class="ml-sm-auto">
                    <a class="btn btn-outline-secondary px-4 py-2 font-weight-medium"
                       href="{{ route('users.index') }}"
                       style="border-radius: 8px; transition: all 0.2s ease;">
                       <i class="fas fa-arrow-left mr-2 small"></i> Retour à la liste
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
<div class="container-fluid  px-md-5" style="background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; color: #09090b;">
    
    {{-- <div class="mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-2">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-muted text-decoration-none small">Utilisateurs</a></li>
                <li class="breadcrumb-item active small text-secondary" aria-current="page">Modifier</li>
            </ol>
        </nav>
        <h1 class="h3 font-weight-bold mb-1" style="letter-spacing: -0.025em;">
            Modifier le profil
        </h1>
        <p class="text-muted small mb-0">Mettez à jour les informations de compte et les rôles de l'utilisateur.</p>
    </div> --}}

    @if($errors->any())
        <div class="alert border-0 shadow-sm rounded-lg mb-4 p-4 d-flex align-items-start" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
            <i class="fas fa-exclamation-circle mt-1 mr-3" style="font-size: 1.1rem;"></i>
            <div>
                <h5 class="font-weight-bold text-sm mb-1">Veuillez corriger les erreurs suivantes :</h5>
                <ul class="mb-0 pl-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-xl overflow-hidden" style="border-radius: 8px; background: #ffffff; border: 1px solid #e4e4e7 !important;">
        
        {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']) !!}

        <div class="card-body p-4 p-md-5">
            <div class="row row-gap-4">
                @include('users.fields')
            </div>
        </div>

        <div class="px-4 py-4 p-md-5 border-top d-flex flex-row-reverse justify-content-start gap-3 bg-light" style="border-color: #e4e4e7 !important; background-color: #fafafa !important; gap: 12px;">
            {!! Form::submit('Enregistrer les modifications', ['class' => 'btn btn-dark btn-modern shadow-sm font-weight-medium px-4 py-2']) !!}
            
            <a href="{{ route('users.index') }}" class="btn btn-white text-secondary border rounded-lg px-4 py-2 font-weight-medium transition-all">
                Annuler
            </a>
        </div>

        {!! Form::close() !!}

    </div>
</div>

<style>
    .rounded-xl { border-radius: 0.5rem !important; }
    .transition-all { transition: all 0.15s ease-in-out; }
    .row-gap-4 { row-gap: 1.5rem; }
    
    /* Bouton principal Noir/Zinc style Shadcn */
    .btn-dark.btn-modern {
        background-color: #18181b !important;
        border-color: #18181b !important;
        color: #ffffff !important;
        border-radius: 6px !important;
        font-size: 0.875rem;
    }
    .btn-dark.btn-modern:hover {
        background-color: #27272a !important;
        border-color: #27272a !important;
    }
    
    /* Bouton secondaire Blanc/Bordure */
    .btn-white {
        background: #ffffff !important;
        font-size: 0.875rem;
        border-color: #e4e4e7 !important;
        border-radius: 6px !important;
    }
    .btn-white:hover {
        background-color: #f4f4f5 !important;
        color: #09090b !important;
    }
</style>
@endsection