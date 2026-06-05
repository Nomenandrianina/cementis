{{-- <!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:',['class' => 'required']) !!}
    {!! Form::text('email', null, ['class' => 'form-control']) !!}
</div>
<!-- Role Field -->
<div class="form-group col-sm-6">
    {!! Form::label('role', 'Role:') !!}
    <div class="select2-purple">
        {!! Form::select('role_data[]', $roles,null, ['class' => 'select2 form-control select2-purple','multiple'=>'multiple']) !!}
    </div>
</div>
@if(!isset($user))
<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', 'Password:',['class' => 'required']) !!}
    {!! Form::password('password', ['class' => 'form-control']) !!}
</div>
@endif
@push('page_scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush --}}
<div class="form-group col-md-6 mb-4">
    {!! Form::label('name', 'Nom complet', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
    {!! Form::text('name', null, [
        'class' => 'form-control form-modern-input', 
        'placeholder' => 'Ex: Jean Dupont',
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
    ]) !!}
</div>

<div class="form-group col-md-6 mb-4">
    <div class="d-flex justify-content-between">
        {!! Form::label('email', 'Adresse email', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
        <span class="text-danger small">* requis</span>
    </div>
    {!! Form::text('email', null, [
        'class' => 'form-control form-modern-input', 
        'placeholder' => 'adresse@exemple.com',
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
    ]) !!}
</div>

<div class="form-group col-md-6 mb-4">
    {!! Form::label('role', 'Rôles & Permissions', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
    <div class="modern-select2-container">
        {!! Form::select('role_data[]', $roles, null, [
            'class' => 'select2 form-control', 
            'multiple' => 'multiple'
        ]) !!}
    </div>
</div>

@if(!isset($user))
<div class="form-group col-md-6 mb-4">
    <div class="d-flex justify-content-between">
        {!! Form::label('password', 'Mot de passe', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
        <span class="text-danger small">* requis</span>
    </div>
    {!! Form::password('password', [
        'class' => 'form-control form-modern-input', 
        'placeholder' => '••••••••',
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
    ]) !!}
</div>
@endif

@push('page_scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: "Sélectionnez un ou plusieurs rôles"
        });
    });
</script>
@endpush

<style>
    .text-zinc-500 { color: #71717a; }
    
    /* Éléments de formulaire natifs */
    .form-modern-input:focus {
        border-color: #18181b !important;
        box-shadow: 0 0 0 2px rgba(24, 24, 27, 0.05) !important;
        outline: none;
    }

    /* Modernisation totale de Select2 pour ressembler à un composant MultiSelect React */
    .modern-select2-container .select2-container--default .select2-selection--multiple {
        border: 1px solid #e4e4e7 !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        min-height: 42px !important;
        background-color: #ffffff !important;
    }
    
    .modern-select2-container .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #18181b !important;
        box-shadow: 0 0 0 2px rgba(24, 24, 27, 0.05) !important;
    }

    /* Le choix sélectionné (Pill/Badge) */
    .modern-select2-container .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f4f4f5 !important;
        border: 1px solid #e4e4e7 !important;
        color: #18181b !important;
        border-radius: 4px !important;
        padding: 2px 8px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        margin-top: 4px !important;
    }

    /* Bouton de suppression du badge (Croix) */
    .modern-select2-container .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #71717a !important;
        border-right: none !important;
        margin-right: 6px !important;
    }
    .modern-select2-container .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ef4444 !important;
        background: transparent !important;
    }
</style>