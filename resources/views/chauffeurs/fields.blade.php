{{-- <div class="form-group col-sm-6">
    {!! Form::label('nom', __('models/chauffeurs.fields.nom').':', ['class' => 'required']) !!}
    {!! Form::text('nom', null, ['class' => 'form-control','placeholder'=>'Nom']) !!}
</div>


<!-- Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('transporteur_id', __('models/vehicules.fields.id_transporteur').':', ['class' => 'required']) !!}
    @if(isset($transporteur))
        @if($action === "edit")
            {!! Form::text('transporteur_id', $transporteur->nom, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
        @endif
        @if($action === "create")
            <select name="transporteur_id" id="" class="form-control" required>
                <option value="">Choisissez un transporteur</option>
                @foreach($transporteur as $transporteurs):
                    <option value="{{$transporteurs->id}}">{{$transporteurs->nom}}</option>
                @endforeach
            </select>
        @endif
    @else
        {!! Form::text('transporteur_id', null, ['class' => 'form-control', 'required'=> 'required']) !!}
    @endif
</div>

<!-- Rfid Field -->
@if(auth()->user()->role == 'supper-admin')
    <div class="form-group col-sm-6">
        {!! Form::label('rfid', __('models/chauffeurs.fields.rfid').':', ['class' => 'required']) !!}
        {!! Form::text('rfid', null, ['class' => 'form-control','placeholder'=>'Rfid']) !!}
    </div>
@endif

<div class="form-group col-sm-6">
    {!! Form::label('rfid_physique', __('models/chauffeurs.fields.rfid_physique').':') !!}
    {!! Form::text('rfid_physique', null, ['class' => 'form-control','placeholder'=>'Rfid physique']) !!}
</div>

<div class="form-group col-sm-6">
    {!! Form::label('numero_badge', __('models/chauffeurs.fields.numero_badge').':', ['class' => 'required']) !!}
    {!! Form::text('numero_badge', null, ['class' => 'form-control','placeholder'=>'Numéro du badge' ,'required'=> 'required']) !!}
</div>

<!-- Nom Field -->


<!-- Contact Field -->
<div class="form-group col-sm-6">
    {!! Form::label('contact', __('models/chauffeurs.fields.contact').':',['class' => 'required']) !!}
    {!! Form::text('contact', null, ['class' => 'form-control','placeholder'=>'Numéro téléphone','required'=> 'required']) !!}
</div> --}}

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('nom', __('models/chauffeurs.fields.nom').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
    {!! Form::text('nom', null, ['class' => 'form-control py-2', 'placeholder' => 'Nom complet', 'style' => 'border-radius: 8px;']) !!}
</div>

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('transporteur_id', __('models/vehicules.fields.id_transporteur').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
    @if(isset($transporteur))
        @if($action === "edit")
            {!! Form::text('transporteur_id', $transporteur->nom, ['class' => 'form-control py-2', 'disabled' => 'disabled', 'style' => 'border-radius: 8px; bg-light']) !!}
        @endif
        @if($action === "create")
            <select name="transporteur_id" class="form-control py-2" style="border-radius: 8px;" required>
                <option value="">Choisissez un transporteur</option>
                @foreach($transporteur as $transporteurs)
                    <option value="{{ $transporteurs->id }}">{{ $transporteurs->nom }}</option>
                @endforeach
            </select>
        @endif
    @else
        {!! Form::text('transporteur_id', null, ['class' => 'form-control py-2', 'required' => 'required', 'style' => 'border-radius: 8px;']) !!}
    @endif
</div>

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('numero_badge', __('models/chauffeurs.fields.numero_badge').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
    {!! Form::text('numero_badge', null, ['class' => 'form-control py-2', 'placeholder' => 'Ex: BDG-9876', 'required' => 'required', 'style' => 'border-radius: 8px;']) !!}
</div>

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('contact', __('models/chauffeurs.fields.contact').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
    {!! Form::text('contact', null, ['class' => 'form-control py-2', 'placeholder' => 'Numéro de téléphone', 'required' => 'required', 'style' => 'border-radius: 8px;']) !!}
</div>

@if(auth()->user()->role == 'supper-admin')
    <div class="form-group col-sm-6 mb-4">
        {!! Form::label('rfid', __('models/chauffeurs.fields.rfid').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
        {!! Form::text('rfid', null, ['class' => 'form-control py-2', 'placeholder' => 'Code RFID système', 'style' => 'border-radius: 8px;']) !!}
    </div>
@endif

<div class="form-group col-sm-6 mb-4">
    {!! Form::label('rfid_physique', __('models/chauffeurs.fields.rfid_physique').':', ['class' => 'form-label font-weight-bold text-secondary small']) !!}
    {!! Form::text('rfid_physique', null, ['class' => 'form-control py-2', 'placeholder' => 'Numéro gravé sur la carte', 'style' => 'border-radius: 8px;']) !!}
</div>