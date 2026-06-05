{{-- @extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Role Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('roles.index') }}">
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
                    @include('roles.show_fields')
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
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-muted text-decoration-none small">Rôles</a></li>
                    <li class="breadcrumb-item active small text-secondary" aria-current="page">Détails</li>
                </ol>
            </nav>
            <h1 class="h3 font-weight-bold mb-0" style="letter-spacing: -0.025em;">
                Détails du Rôle
            </h1>
        </div>
        
        <div>
            <a class="btn btn-white text-secondary border rounded-lg px-4 py-2 shadow-sm font-weight-medium transition-all" 
               style="background: #ffffff; font-size: 0.9rem; border-color: #e4e4e7 !important;"
               href="{{ route('roles.index') }}">
                <i class="fas fa-chevron-left mr-2" style="font-size: 0.8rem;"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-xl overflow-hidden" style="border-radius: 8px; background: #ffffff; border: 1px solid #e4e4e7 !important;">
        @include('roles.show_fields')
    </div>
</div>

<style>
    .rounded-xl { border-radius: 0.5rem !important; }
    .transition-all { transition: all 0.15s ease-in-out; }
    .btn-white:hover { background-color: #f4f4f5 !important; color: #09090b !important; }
    .gap-3 { gap: 1rem; }
</style>
@endsection
