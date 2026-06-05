<div class="p-4 p-md-5 border-bottom d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="border-color: #e4e4e7 !important;">
    <div class="d-flex align-items-center">
        <div class="rounded-lg d-flex align-items-center justify-content-center text-white shadow-sm" 
             style="width: 56px; height: 56px; font-size: 1.25rem; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border-radius: 8px;">
            <i class="fas fa-key"></i>
        </div>
        
        <div class="ml-4">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h2 class="h4 font-weight-bold mb-0" style="color: #09090b; letter-spacing: -0.02em;">
                    {{ $permission->title ?? $permission->name }}
                </h2>
                @if(isset($permission->module))
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: #f4f4f5; color: #18181b; border-radius: 9999px; font-size: 0.75rem; padding: 2px 10px;">
                        Module: {{ $permission->module }}
                    </span>
                @endif
            </div>
            <p class="text-muted mb-0 mt-1" style="font-size: 0.9rem;">Identifiant technique (slug) : <code class="text-monospace bg-light px-1 py-0.5 rounded text-danger" style="font-size: 0.85rem;">{{ $permission->name }}</code></p>
        </div>
    </div>
</div>

<div class="p-4 p-md-5" style="background: #ffffff;">
    
    <div class="mb-4">
        <h3 class="h6 font-weight-bold text-uppercase mb-1" style="color: #09090b; letter-spacing: 0.05em; font-size: 0.8rem;">
            Configuration de sécurité
        </h3>
        <p class="text-muted small">Propriétés système et portée d'application de la permission.</p>
    </div>

    <div class="border rounded-lg overflow-hidden mb-5" style="border-color: #e4e4e7 !important;">
        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Nom affiché</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="font-weight-semibold text-dark" style="font-size: 0.95rem;">{{ $permission->title ?? 'Non défini' }}</span>
            </div>
        </div>

        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #fafafa;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Code système</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="text-monospace font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $permission->name }}</span>
            </div>
        </div>

        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Guard API / Web</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="px-2 py-1 rounded small font-weight-medium" style="background-color: #eff6ff; color: #1e40af; border-radius: 4px;">
                    {{ $permission->guard_name }}
                </span>
            </div>
        </div>

        <div class="row mx-0 p-4 align-items-center" style="background-color: #fafafa;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Description</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span class="text-secondary" style="font-size: 0.95rem;">
                    {{ $permission->description ?? 'Aucune description fournie pour cette permission.' }}
                </span>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h3 class="h6 font-weight-bold text-uppercase mb-1" style="color: #09090b; letter-spacing: 0.05em; font-size: 0.8rem;">
            Dates de traçabilité
        </h3>
    </div>

    <div class="border rounded-lg overflow-hidden" style="border-color: #e4e4e7 !important;">
        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #e4e4e7 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Date de création</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span style="color: #3f3f46; font-size: 0.95rem;">
                    {{ $permission->created_at ? $permission->created_at->format('d/m/Y à H:i') : $permission->created_at }}
                </span>
            </div>
        </div>

        <div class="row mx-0 p-4 align-items-center" style="background-color: #fafafa;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium text-muted" style="font-size: 0.9rem;">Dernière mise à jour</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span style="color: #3f3f46; font-size: 0.95rem;">
                    {{ $permission->updated_at ? $permission->updated_at->format('d/m/Y à H:i') : $permission->updated_at }}
                </span>
            </div>
        </div>
    </div>

</div>

