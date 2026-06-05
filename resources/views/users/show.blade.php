{{-- @extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('users.index') }}">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @include('users.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid py-5 px-md-5" style="background-color: #f8fafc; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif;">
    
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-5 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-muted text-decoration-none small">Utilisateurs</a></li>
                    <li class="breadcrumb-item active small text-secondary" aria-current="page">Profil</li>
                </ol>
            </nav>
            <h1 class="h3 font-weight-bold text-dark mb-0" style="letter-spacing: -0.025em; color: #0f172a !important;">
                Compte Utilisateur
            </h1>
        </div>
        
        <div>
            <a class="btn btn-white text-secondary border rounded-lg px-4 py-2 shadow-sm font-weight-medium transition-all" 
               style="background: #ffffff; font-size: 0.9rem; border-color: #e2e8f0 !important;"
               href="{{ route('users.index') }}">
                <i class="fas fa-chevron-left mr-2" style="font-size: 0.8rem;"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-xl overflow-hidden" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0 !important;">
        @include('users.show_fields')
    </div>
</div>

<style>
    /* Simulation des classes utilitaires de Tailwind */
    .rounded-xl { border-radius: 1rem !important; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .btn-white:hover { background-color: #f8fafc !important; color: #0f172a !important; }
    .gap-3 { gap: 1rem; }
</style>
@endsection
