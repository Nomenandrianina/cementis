@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>@lang('models/events.plural')</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('events.index') }}">
                         @lang('crud.back')
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'events.import', 'method' => 'post', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return submitForm();']) !!}
                @csrf
                
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <div class="form-group ">
                                {!! Form::label('file_upload', __('models/fileUploads.fields.file_upload').':') !!}
                                <div class="input-group">
                                    <div class="custom-file">
                                        {!! Form::file('excel_file', ['class' => 'custom-file-input','id'=>'excel_file']) !!}
                                        {!! Form::label('excel_file', 'Veuillez importer le fichier excel', ['class' => 'custom-file-label']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    {!! Form::submit(__('crud.save'), ['class' => 'btn btn-primary']) !!}
                    <a href="{{ route('events.index') }}" class="btn btn-default">
                    @lang('crud.cancel')
                    </a>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection