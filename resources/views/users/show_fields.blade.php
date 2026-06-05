<div class="p-4 p-md-5 border-bottom d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="border-color: #f1f5f9 !important;">
    <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" 
             style="width: 64px; height: 64px; font-size: 1.25rem; font-weight: 600; background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        
        <div class="ml-4">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h2 class="h4 font-weight-bold mb-0" style="color: #0f172a; letter-spacing: -0.02em;">{{ $user->name }}</h2>
                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: #ecfdf5; color: #059669; border-radius: 9999px; font-size: 0.75rem; padding: 2px 10px;">
                    Actif
                </span>
            </div>
            <p class="text-muted mb-0 mt-1" style="font-size: 0.9rem;">{{$user->role_title}}</p>
        </div>
    </div>

    <div class="mt-3 mt-md-0">
        <span class="px-3 py-1.5 rounded text-monospace small" style="background: #f1f5f9; color: #475569; border-radius: 6px;">
            id: {{ $user->id }}
        </span>
    </div>
</div>

<div class="p-4 p-md-5" style="background: #ffffff;">
    <div class="mb-4">
        <h3 class="h6 font-weight-bold text-uppercase mb-1" style="color: #0f172a; letter-spacing: 0.05em; font-size: 0.8rem;">
            Informations Personnelles
        </h3>
        <p class="text-muted small">Consultez et vérifiez les données d'identité de l'utilisateur.</p>
    </div>

    <div class="border rounded-lg overflow-hidden" style="border-color: #e2e8f0 !important;">
        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #f1f5f9 !important; background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium" style="color: #475569; font-size: 0.9rem;">Full name</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span style="color: #0f172a; font-weight: 500; font-size: 0.95rem;">{{ $user->name }}</span>
            </div>
        </div>

        <div class="row mx-0 p-4 border-bottom align-items-center" style="border-color: #f1f5f9 !important; background-color: #f8fafc;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium" style="color: #475569; font-size: 0.9rem;">Email address</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <a href="mailto:{{ $user->email }}" class="text-decoration-none font-weight-medium" style="color: #4f46e5; font-size: 0.95rem;">
                    {{ $user->email }}
                </a>
            </div>
        </div>

        @if(isset($user->created_at))
        <div class="row mx-0 p-4 align-items-center" style="background-color: #ffffff;">
            <div class="col-sm-4 px-0">
                <span class="font-weight-medium" style="color: #475569; font-size: 0.9rem;">Created at</span>
            </div>
            <div class="col-sm-8 px-0 mt-1 mt-sm-0">
                <span style="color: #334155; font-size: 0.95rem;">
                    {{ $user->created_at->format('d/m/Y à H:i') }}
                </span>
            </div>
        </div>
        @endif
    </div>
</div>