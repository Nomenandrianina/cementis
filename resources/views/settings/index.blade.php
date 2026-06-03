@extends('layouts.app')
@section('title', 'Paramètres')
@section('page-title', 'Paramètres')

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">

<div>

  <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
    <div style="width:36px;height:36px;border-radius:6px;background:var(--cream);display:flex;align-items:center;justify-content:center;">
      <i class="nav-icon fas fa-cog" style="font-size:18px;color:var(--muted);"></i>
    </div>
    <div>
      <div style="font-size:16px;font-weight:500;color:var(--ink);">Paramètres généraux</div>
      <div style="font-size:12px;color:var(--muted);">Configuration globale de l'application</div>
    </div>
  </div>

  @if(session('success'))
    <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(45,122,74,0.06);
                border:1px solid rgba(45,122,74,0.2);border-radius:7px;margin-bottom:16px;font-size:13px;color:var(--success);">
      <i class="ti ti-circle-check" style="font-size:16px;"></i>
      {{ session('success') }}
    </div>
  @endif

  <div class="card">
    <div class="card-header" style="display:flex;align-items:center;gap:8px;">
      <i class="ti ti-route" style="font-size:16px;color:var(--muted);"></i>
      <span class="card-title">Calcul des rotations</span>
    </div>
    <div class="card-body">

      <form action="{{ route('settings.update') }}" method="POST">
        @csrf

        <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;
                    color:var(--muted);margin-bottom:1rem;">Zones et circuits</div>

        <div style="display:flex;align-items:flex-start;gap:1.5rem;padding:1rem 0;
                    border-bottom:0.5px solid var(--cream-d);">
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:500;color:var(--ink);margin-bottom:4px;">
              Délai zone parent → sous-zone
            </div>
            <div style="font-size:12px;color:var(--muted);line-height:1.5;">
              Délai maximum accepté entre l'entrée dans la zone parent et l'entrée dans la sous-zone
              pour considérer la fin de rotation comme valide.
            </div>
            <div style="margin-top:8px;">
              <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:500;
                           padding:2px 8px;border-radius:5px;background:var(--cream);color:var(--muted);
                           border:0.5px solid var(--cream-dd);">
                <i class="ti ti-info-circle" style="font-size:12px;"></i>
                Applicable à tous les circuits
              </span>
            </div>
          </div>
          <div style="flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:8px;">
              <input type="number" name="parent_zone_delay_hours"
                     value="{{ $settings['parent_zone_delay_hours']->value ?? 24 }}"
                     min="1" max="720"
                     id="delay-input"
                     style="width:80px;text-align:center;font-size:15px;font-weight:500;">
              <span style="font-size:13px;color:var(--muted);">heures</span>
              <div style="margin-top:6px;text-align:right;font-size:11px;color:var(--muted);" id="delay-preview">
                = {{ number_format(($settings['parent_zone_delay_hours']->value ?? 24) * 60, 0, ',', ' ') }} min
              </div>
            </div>
            
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;margin-top:1.25rem;">
          <a href="{{ route('settings.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="ti ti-device-floppy" style="font-size:14px;"></i>
            Enregistrer
          </button>
        </div>

      </form>
    </div>
  </div>

</div>

<script>
  const input = document.getElementById('delay-input');
  const preview = document.getElementById('delay-preview');
  input.addEventListener('input', function() {
    const h = parseInt(this.value) || 0;
    preview.textContent = '= ' + (h * 60).toLocaleString('fr-FR') + ' min';
  });
</script>
@endsection