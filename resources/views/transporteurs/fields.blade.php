{{-- <!-- Nom Field -->
<div class="form-group col-sm-5">
    {!! Form::label('nom', __('models/transporteurs.fields.nom') .': *') !!}
    {!! Form::text('nom', null, ['class' => 'form-control','placeholder'=>'Nom']) !!}
</div>

<!-- Adresse Field -->
<div class="form-group col-sm-5">
    {!! Form::label('Adresse', __('models/transporteurs.fields.Adresse').':') !!}
    {!! Form::text('Adresse', null, ['class' => 'form-control','placeholder'=>'Adresse']) !!}
</div> --}}

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('nom', __('models/transporteurs.fields.nom').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!} <span class="text-danger small">*</span>
    {!! Form::text('nom', null, ['class' => 'form-control py-2', 'placeholder' => 'Nom de l\'entreprise', 'required' => 'required', 'style' => 'border-radius: 8px;']) !!}
</div>

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('Adresse', __('models/transporteurs.fields.Adresse').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
    {!! Form::text('Adresse', null, ['class' => 'form-control py-2', 'placeholder' => 'Adresse postale ou siège social', 'style' => 'border-radius: 8px;']) !!}
</div>