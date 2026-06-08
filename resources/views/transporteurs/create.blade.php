{{-- @extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                     <h1>@lang('models/transporteurs.singular')</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::open(['route' => 'transporteurs.store', 'onsubmit' => 'return submitForm();']) !!}

            <div class="card-body">
                <div class="row">
                    @include('transporteurs.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('transporteurs.index') }}" class="btn btn-default">
                 @lang('crud.cancel')
                </a>
            </div>

            {!! Form::close() !!}

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
                            Créer un transporteur
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Enregistrez une nouvelle entreprise de transport et configurez ses coordonnées principales.
                        </p>
                    </div>
                    
                    <div class="ml-sm-auto">
                        <a class="btn btn-outline-secondary px-4 py-2 font-weight-medium"
                           href="{{ route('transporteurs.index') }}"
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

        <div class="card border-0 shadow-sm mb-5" style="border-radius: 12px !important; overflow: hidden;">
            
            {!! Form::open(['route' => 'transporteurs.store', 'onsubmit' => 'return submitForm();']) !!}
            
            <div class="card-body p-4 bg-white">
                <div class="row row-gap-3">
                    @include('transporteurs.fields')
                </div>
            </div>

            <div class="card-footer bg-light border-top-0 d-flex justify-content-end gap-2 p-4">
                <a href="{{ route('transporteurs.index') }}" class="btn btn-light px-4 py-2 font-weight-medium text-secondary mr-2" style="border-radius: 8px;">
                    @lang('crud.cancel')
                </a>
                {!! Form::submit(__('crud.save'), ['class' => 'btn btn-primary px-5 py-2 font-weight-medium shadow-sm', 'style' => 'border-radius: 8px;']) !!}
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection