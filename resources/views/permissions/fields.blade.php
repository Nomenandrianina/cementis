{{-- <!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:',['class' => 'required']) !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control']) !!}
</div>

<!-- Guard Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('guard_name', 'Guard Name:') !!}
    {!! Form::select('guard_name', ['web' => 'web', 'api' => 'api'], null, ['class' => 'form-control custom-select']) !!}
</div>

<!-- Module Field -->
<div class="form-group col-sm-6">
    {!! Form::label('module', 'Module:') !!}
    {!! Form::text('module', null, ['class' => 'form-control']) !!}
</div>


<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control','rows'=>5]) !!}
</div> --}}

<div class="form-group col-md-6 mb-4">
    <div class="d-flex justify-content-between">
        {!! Form::label('name', 'Code système (Name)', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
        <span class="text-danger small">* requis</span>
    </div>
    {!! Form::text('name', null, [
        'class' => 'form-control form-modern-input text-monospace', 
        'placeholder' => 'Ex: users.create',
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
    ]) !!}
</div>

<div class="form-group col-md-6 mb-4">
    {!! Form::label('title', 'Nom affiché (Title)', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
    {!! Form::text('title', null, [
        'class' => 'form-control form-modern-input', 
        'placeholder' => 'Ex: Créer des utilisateurs',
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
    ]) !!}
</div>

<div class="form-group col-md-6 mb-4">
    {!! Form::label('guard_name', 'Guard Name', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
    <div class="position-relative">
        {!! Form::select('guard_name', ['web' => 'web', 'api' => 'api'], null, [
            'class' => 'form-control custom-select form-modern-input',
            'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7; height: auto; appearance: none;'
        ]) !!}
    </div>
</div>

<div class="form-group col-md-6 mb-4">
    {!! Form::label('module', 'Module', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
    {!! Form::text('module', null, [
        'class' => 'form-control form-modern-input', 
        'placeholder' => 'Ex: Utilisateurs, Facturation...',
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7;'
    ]) !!}
</div>

<div class="form-group col-12 mb-4">
    {!! Form::label('description', 'Description', ['class' => 'text-zinc-500 font-weight-medium small mb-2 d-block']) !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control form-modern-input', 
        'placeholder' => 'Décrivez brièvement les droits accordés par cette permission...',
        'rows' => 3,
        'style' => 'border-radius: 6px; padding: 10px 14px; font-size: 0.9rem; border: 1px solid #e4e4e7; resize: none;'
    ]) !!}
</div>

<style>
    .text-zinc-500 { color: #71717a; }
    
    /* Input & Select focus style */
    .form-modern-input:focus {
        border-color: #18181b !important;
        box-shadow: 0 0 0 2px rgba(24, 24, 27, 0.05) !important;
        outline: none;
    }
    
    /* Harmonisation du select de Bootstrap */
    select.form-modern-input {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px 12px;
    }
</style>
