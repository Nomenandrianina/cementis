{{-- <!-- Id Field -->
<div class="col-sm-12">
    {!! Form::label('id', 'Id:') !!}
    <p>{{ $role->id }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $role->name }}</p>
</div>

<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $role->title }}</p>
</div>

<!-- Guard Name Field -->
<div class="col-sm-12">
    {!! Form::label('guard_name', 'Guard Name:') !!}
    <p>{{ $role->guard_name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $role->description }}</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $role->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $role->updated_at }}</p>
</div> --}}
<div class="p-4 p-md-5 border-bottom d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="border-color: #e4e4e7 !important;">
    <div class="d-flex align-items-center">
        <div class="rounded-lg d-flex align-items-center justify-content-center text-white shadow-sm" 
             style="width: 56px; height: 56px; font-size: 1.25rem; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 8px;">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <div class="ml-4">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h2 class="h4 font-weight-bold mb-0" style="color: #09090b; letter-spacing: -0.02em;">
                    {{ $role->title ?? $role->name }}
                </h2>
            </div>
            <p class="text-muted mb-0 mt-1" style="font-size: 0.9rem;">Clé système : <code class="text-monospace bg-light px-1 py-0.5 rounded text-danger" style="font-size: 0.85rem;">{{ $role->name }}</code></p>
        </div>
    </div>

    {{-- <div class="mt-3 mt-md-0">
        <span class="px-3 py-1.5 rounded text-monospace small" style="background: #f4f4f5; color: #18181b; border-radius: 6px; font-size: 0.8rem;">
            ID: #{{ $role->id }}
        </span>
    </div> --}}
</div>

<div class="p-4 p-md-5" style="background: #ffffff;">
    
    <div class="mb-4">
        <h3 class="h6 font-weight-bold text-uppercase mb-1" style="color: #09090b; letter-spacing: 0.05em; font-size: 0.8rem;">
            Configuration du rôle
        </h3>
        <p class="text-muted small">Paramètres et portée d'application du rôle utilisateur.</p>
    </div>

    <div class="border rounded-lg overflow-hidden mb-5" style="border-color: #e4e4e7 !important;">
        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Nom affiché</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="font-weight-semibold text-dark" style="font-size: 0.95rem;">{{ $role->title ?? 'Non défini' }}</span>
            </div>
        </div>

        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #fafafa;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Identifiant système</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="text-monospace font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $role->name }}</span>
            </div>
        </div>

        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Guard appliqué</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="px-2 py-1 rounded small font-weight-medium" style="background-color: #f4f4f5; color: #18181b; border-radius: 4px;">
                    {{ $role->guard_name }}
                </span>
            </div>
        </div>

        <div class="row mx-0 p-4 align-items-center" style="background-color: #fafafa;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Description</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="text-secondary" style="font-size: 0.95rem;">
                    {{ $role->description ?? 'Aucune description spécifiée pour ce rôle.' }}
                </span>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h3 class="h6 font-weight-bold text-uppercase mb-1" style="color: #09090b; letter-spacing: 0.05em; font-size: 0.8rem;">
            Suivi temporel
        </h3>
    </div>

    <div class="border rounded-lg overflow-hidden" style="border-color: #e4e4e7 !important;">
        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Créé le</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span style="color: #3f3f46; font-size: 0.95rem;">
                    {{ $role->created_at ? $role->created_at->format('d/m/Y à H:i') : 'Inconnue' }}
                </span>
            </div>
        </div>

        <div class="row mx-0 p-4 align-items-center" style="background-color: #fafafa;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Dernière modification</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span style="color: #3f3f46; font-size: 0.95rem;">
                    {{ $role->updated_at ? $role->updated_at->format('d/m/Y à H:i') : 'Inconnue' }}
                </span>
            </div>
        </div>
    </div>

</div>

