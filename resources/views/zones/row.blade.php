<div class="zone-row {{ $depth > 0 ? 'zone-child children-of-' . $zone->parent_id : 'zone-root' }}"
     data-depth="{{ $depth }}"
     {{ $depth > 0 ? 'style=display:none' : '' }}>

    <div class="zone-row-inner" style="padding-left: {{ 20 + $depth * 28 }}px;">

        {{-- Bouton expand si a des enfants (uniquement racines niveau 0) --}}
        @if($zone->hasChildren() && $depth === 0)
            <button
                id="expand-btn-{{ $zone->id }}"
                data-open="0"
                onclick="toggleChildren({{ $zone->id }})"
                class="z-expand-btn"
                aria-label="Développer les sous-zones"
                title="Développer / réduire">
                <svg id="expand-icon-{{ $zone->id }}"
                     style="transition:transform 0.18s ease;"
                     width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
        @else
            {{-- Espace réservé pour l'alignement --}}
            <span style="width:22px; flex-shrink:0;"></span>
        @endif

        {{-- Connecteur arbre pour sous-zones --}}
        @if($depth > 0)
            <span class="zone-tree-connector">└</span>
        @endif


        {{-- Infos --}}
        <div class="zone-info">
            <div class="zone-name-row">
                <span class="zone-name">{{ $zone->name }}</span>

                {{-- Type / option --}}
                <span class="z-pill z-pill-option {{ $zone->isOptional() ? 'z-pill-optional' : 'z-pill-required' }}">
                    {{ $zone->getOptionLabel() }}
                </span>

                {{-- Sous-zones count --}}
                @if($zone->hasChildren())
                    <span class="z-pill z-pill-sub">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        {{ $zone->children->count() }} sous-zone{{ $zone->children->count() > 1 ? 's' : '' }}
                    </span>
                @endif

                {{-- Rôle --}}
                @if($zone->role === 'start')
                    <span class="z-pill z-pill-start">▶ Départ</span>
                @elseif($zone->role === 'end')
                    <span class="z-pill z-pill-end">⏹ Arrivée</span>
                @elseif($zone->role === 'waypoint')
                    <span class="z-pill z-pill-waypoint">⬡ Étape</span>
                @endif

                {{-- Inactive --}}
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
    <div id="edit-zone-{{ $zone->id }}" style="display:none; padding: 0 20px 12px {{ 20 + $depth * 28 }}px;">
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
                        <label>Type</label>
                        <select name="option">
                            @foreach(\App\Models\Zone::OPTIONS as $key => $label)
                                <option value="{{ $key }}" {{ $zone->option === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
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