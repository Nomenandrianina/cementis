
{{-- <div style="border-bottom:1px solid var(--cream-d);
            padding:11px 20px 11px {{ 20 + $depth * 24 }}px;
            {{ $depth > 0 ? 'background:rgba(245,240,232,0.5);' : '' }}
            position:relative;">

    <div style="display:flex;align-items:center;gap:10px;">

        
        @if($depth > 0)
            <span style="color:var(--cream-dd);font-size:13px;flex-shrink:0;
                         font-family:var(--mono);">└</span>
        @endif

        
        @if($zone->color)
            <div style="width:9px;height:9px;border-radius:50%;
                        background:{{ $zone->color }};flex-shrink:0;"></div>
        @endif

        
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
        </div>

        
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <button onclick="toggleEditZone({{ $zone->id }})" class="btn btn-ghost btn-sm">✎</button>
            <form action="{{ route('zones.destroy', $zone) }}" method="POST"
                  onsubmit="return confirm('Supprimer {{ addslashes($zone->name) }} ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">✕</button>
            </form>
        </div>
    </div>

    
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


@foreach($zone->children as $child)
    @include('zones.row', [
        'zone'        => $child,
        'depth'       => $depth + 1,
        'parentZones' => $parentZones,
    ])
@endforeach --}}

{{-- Partial récursif : affiche une zone et ses sous-zones --}}

<style>
    /* ===== ZONE ROW ===== */
    .zone-row {
        font-family: 'DM Sans', sans-serif;
        border-bottom: 1px solid #e8ecf2;
        transition: background 0.15s;
        position: relative;
        padding: 12px 20px 12px {{ 20 + $depth * 28 }}px;
        {{ $depth > 0 ? 'background: #f8fafc;' : '' }}
    }

    .zone-row:last-child { border-bottom: none; }

    .zone-row:hover { background: #f0f4ff !important; }

    .zone-row.is-editing { background: #f5f7ff !important; }

    /* Ligne verticale pour les sous-zones */
    @if($depth > 0)
    .zone-row[data-depth="{{ $depth }}"]::before {
        content: '';
        position: absolute;
        left: {{ 20 + ($depth - 1) * 28 + 10 }}px;
        top: 0; bottom: 0;
        width: 1.5px;
        background: linear-gradient(180deg, #d4dae4, #e8ecf2);
    }
    @endif

    .zone-row-inner {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Connecteur arbre */
    .zone-tree-connector {
        flex-shrink: 0;
        color: #b0bac8;
        font-size: 12px;
        font-family: 'DM Mono', monospace;
        line-height: 1;
        margin-right: 2px;
    }

    /* Dot couleur */
    .zone-color-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.9), 0 0 0 3px currentColor;
    }

    /* Infos */
    .zone-info { flex: 1; min-width: 0; }

    .zone-name-row {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .zone-name {
        font-size: 13.5px;
        font-weight: 600;
        color: #1a2035;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Badges */
    .z-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }

    .z-pill-sub {
        background: #eef1fd;
        color: #3b5bdb;
        border: 1px solid #c5cff9;
    }

    .z-pill-start {
        background: #e6fcf5;
        color: #0ca678;
        border: 1px solid #96f2d7;
    }

    .z-pill-end {
        background: #fff4e6;
        color: #e67700;
        border: 1px solid #ffd8a8;
    }

    .z-pill-waypoint {
        background: #f3f0ff;
        color: #7048e8;
        border: 1px solid #d0bfff;
    }

    .z-pill-inactive {
        background: #f1f3f5;
        color: #868e96;
        border: 1px solid #dee2e6;
    }

    /* Actions */
    .zone-actions {
        display: flex;
        gap: 5px;
        flex-shrink: 0;
        opacity: 0;
        transition: opacity 0.15s;
    }

    .zone-row:hover .zone-actions { opacity: 1; }

    .z-action-btn {
        width: 30px; height: 30px;
        border-radius: 8px;
        border: 1.5px solid #e8ecf2;
        background: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.15s;
        color: #6b7280;
    }

    .z-action-btn:hover {
        border-color: #3b5bdb;
        background: #eef1fd;
        color: #3b5bdb;
    }

    .z-action-btn.danger:hover {
        border-color: #fa5252;
        background: #fff5f5;
        color: #fa5252;
    }

    /* ===== EDIT FORM ===== */
    .zone-edit-panel {
        margin-top: 12px;
        background: #ffffff;
        border: 1.5px solid #c5cff9;
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 4px 20px rgba(59,91,219,0.08);
        animation: slideDown 0.18s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .zone-edit-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 100px;
        gap: 10px;
        align-items: end;
    }

    .ze-field label {
        display: block;
        font-size: 10.5px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .ze-field input,
    .ze-field select {
        width: 100%;
        padding: 8px 10px;
        border: 1.5px solid #e8ecf2;
        border-radius: 7px;
        font-family: 'DM Sans', sans-serif;
        font-size: 12.5px;
        color: #1a2035;
        background: #fafbfd;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .ze-field input:focus,
    .ze-field select:focus {
        border-color: #3b5bdb;
        box-shadow: 0 0 0 3px rgba(59,91,219,0.10);
        background: #fff;
    }

    .ze-actions {
        display: flex;
        gap: 6px;
        margin-top: 12px;
    }

    .ze-btn-save {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 8px 14px;
        background: #3b5bdb;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
    }

    .ze-btn-save:hover { background: #2f4cc7; transform: translateY(-1px); }

    .ze-btn-cancel {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 8px 12px;
        background: transparent;
        color: #6b7280;
        border: 1.5px solid #e8ecf2;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }

    .ze-btn-cancel:hover { border-color: #adb5bd; color: #1a2035; background: #f8f9fa; }
</style>

<div class="zone-row" data-depth="{{ $depth }}">
    <div class="zone-row-inner">

        {{-- Connecteur arbre --}}
        @if($depth > 0)
            <span class="zone-tree-connector">└</span>
        @endif

        {{-- Dot couleur --}}
        @if($zone->color)
            <div class="zone-color-dot" style="background:{{ $zone->color }};color:{{ $zone->color }};"></div>
        @else
            <div style="width:8px;height:8px;border-radius:50%;background:#e8ecf2;flex-shrink:0;"></div>
        @endif

        {{-- Infos --}}
        <div class="zone-info">
            <div class="zone-name-row">
                <span class="zone-name">{{ $zone->name }}</span>

                @if($zone->hasChildren())
                    <span class="z-pill z-pill-sub">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        {{ $zone->children->count() }} sous-zone{{ $zone->children->count() > 1 ? 's' : '' }}
                    </span>
                @endif

                @if($zone->role === 'start')
                    <span class="z-pill z-pill-start">▶ Départ</span>
                @elseif($zone->role === 'end')
                    <span class="z-pill z-pill-end">⏹ Arrivée</span>
                @elseif($zone->role === 'waypoint')
                    <span class="z-pill z-pill-waypoint">⬡ Étape</span>
                @endif

                @if(!$zone->active)
                    <span class="z-pill z-pill-inactive">⊘ Inactive</span>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="zone-actions">
            <button onclick="toggleEditZone({{ $zone->id }})" class="z-action-btn" title="Modifier">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <form action="{{ route('zones.destroy', $zone) }}" method="POST" style="display:contents;"
                  onsubmit="return confirm('Supprimer « {{ addslashes($zone->name) }} » ?')">
                @csrf @method('DELETE')
                <button type="submit" class="z-action-btn danger" title="Supprimer">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Formulaire édition inline --}}
    <div id="edit-zone-{{ $zone->id }}" style="display:none;">
        <div class="zone-edit-panel">
            <form action="{{ route('zones.update', $zone) }}" method="POST">
                @csrf @method('PUT')
                <div class="zone-edit-grid">
                    <div class="ze-field">
                        <label>Nom</label>
                        <input type="text" name="name" value="{{ $zone->name }}" required>
                    </div>
                    <div class="ze-field">
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
                    <div class="ze-field">
                        <label>Rôle</label>
                        <select name="role">
                            <option value=""         {{ !$zone->role ? 'selected' : '' }}>— Générique —</option>
                            <option value="start"    {{ $zone->role==='start'    ? 'selected' : '' }}>Départ</option>
                            <option value="end"      {{ $zone->role==='end'      ? 'selected' : '' }}>Arrivée</option>
                            <option value="waypoint" {{ $zone->role==='waypoint' ? 'selected' : '' }}>Étape</option>
                        </select>
                    </div>
                    <div class="ze-field">
                        <label>Actif</label>
                        <select name="active">
                            <option value="1" {{ $zone->active  ? 'selected' : '' }}>Oui</option>
                            <option value="0" {{ !$zone->active ? 'selected' : '' }}>Non</option>
                        </select>
                    </div>
                </div>
                <div class="ze-actions">
                    <button type="submit" class="ze-btn-save">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Enregistrer
                    </button>
                    <button type="button" onclick="toggleEditZone({{ $zone->id }})" class="ze-btn-cancel">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
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