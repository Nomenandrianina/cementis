{{-- Partial récursif : affiche une zone et ses sous-zones --}}
<div style="border-bottom:1px solid var(--cream-d);
            padding:11px 20px 11px {{ 20 + $depth * 24 }}px;
            {{ $depth > 0 ? 'background:rgba(245,240,232,0.5);' : '' }}
            position:relative;">

    <div style="display:flex;align-items:center;gap:10px;">

        {{-- Indicateur sous-zone --}}
        @if($depth > 0)
            <span style="color:var(--cream-dd);font-size:13px;flex-shrink:0;
                         font-family:var(--mono);">└</span>
        @endif

        {{-- Dot couleur --}}
        @if($zone->color)
            <div style="width:9px;height:9px;border-radius:50%;
                        background:{{ $zone->color }};flex-shrink:0;"></div>
        @endif

        {{-- Infos --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-weight:600;font-size:13px;color:var(--ink);">
                    {{ $zone->name }}
                </span>

                @if($zone->hasChildren())
                    <span style="font-size:10px;color:var(--muted);background:var(--cream-d);
                                 padding:2px 7px;border-radius:10px;">
                        {{ $zone->children->count() }} sous-zone(s)
                    </span>
                @endif

                @if($zone->role)
                    <span class="badge badge-blue" style="font-size:9px;">{{ $zone->role }}</span>
                @endif

                @if(!$zone->active)
                    <span class="badge badge-muted" style="font-size:9px;">Inactive</span>
                @endif
            </div>

            {{-- @if($zone->gps_zone_id)
                <div style="font-size:10px;color:var(--muted);font-family:var(--mono);margin-top:1px;">
                    GPS: {{ $zone->gps_zone_id }}
                </div>
            @endif --}}
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <button onclick="toggleEditZone({{ $zone->id }})" class="btn btn-ghost btn-sm">✎</button>
            <form action="{{ route('zones.destroy', $zone) }}" method="POST"
                  onsubmit="return confirm('Supprimer {{ addslashes($zone->name) }} ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">✕</button>
            </form>
        </div>
    </div>

    {{-- Formulaire édition inline --}}
    <div id="edit-zone-{{ $zone->id }}" style="display:none;margin-top:12px;
         background:var(--cream);border-radius:7px;padding:14px;">
        <form action="{{ route('zones.update', $zone) }}" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;">
                <div class="form-group" style="margin:0;">
                    <label>Nom</label>
                    <input type="text" name="name" value="{{ $zone->name }}" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Zone parente</label>
                    <select name="parent_id">
                        <option value="">— Aucune —</option>
                        @foreach($parentZones as $pz)
                            @if($pz->id !== $zone->id)
                                <option value="{{ $pz->id }}" {{ $zone->parent_id === $pz->id ? 'selected' : '' }}>
                                    {{ $pz->parent_id ? '└ ' : '' }}{{ $pz->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Rôle</label>
                    <select name="role">
                        <option value=""      {{ !$zone->role ? 'selected' : '' }}>— Générique —</option>
                        <option value="start" {{ $zone->role==='start' ? 'selected' : '' }}>Départ</option>
                        <option value="end"   {{ $zone->role==='end' ? 'selected' : '' }}>Arrivée</option>
                        <option value="waypoint" {{ $zone->role==='waypoint' ? 'selected' : '' }}>Étape</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Actif</label>
                    <select name="active">
                        <option value="1" {{ $zone->active ? 'selected' : '' }}>Oui</option>
                        <option value="0" {{ !$zone->active ? 'selected' : '' }}>Non</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:10px;display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                <button type="button" onclick="toggleEditZone({{ $zone->id }})"
                        class="btn btn-ghost btn-sm">Annuler</button>
            </div>
        </form>
    </div>
</div>

{{-- Sous-zones récursives --}}
@foreach($zone->children as $child)
    @include('zones.row', [
        'zone'        => $child,
        'depth'       => $depth + 1,
        'parentZones' => $parentZones,
    ])
@endforeach