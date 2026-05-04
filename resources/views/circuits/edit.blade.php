@ -1,487 +0,0 @@
@extends('layouts.app')

@section('title', 'Circuit : ' . $circuit->name)
@section('page-title', $circuit->name)

@section('topbar-actions')
    <a href="{{ route('circuits.index') }}" class="btn btn-ghost btn-sm">← Circuits</a>
    <a href="{{ route('circuits.objectives.index', $circuit) }}" class="btn btn-blue btn-sm">Objectifs</a>
@endsection

<script>
    
    // ── Drag & Drop des étapes ─────────────────────────────────────────────────
    var REORDER_URL = '{{ route('circuits.legs.reorder', $circuit) }}';

    var dragSrcEl   = null;   // élément en cours de drag
    var dragSrcIdx  = null;   // index de départ

    function toggleEditLeg(id) {
        const el = document.getElementById('edit-leg-' + id);
        if (!el) return;

        if (el.style.display === 'none') {
            // Fermer les autres modals ouverts
            document.querySelectorAll('.leg-edit-modal-container').forEach(m => m.style.display = 'none');
            
            // UTILISER FLEX ICI pour activer le centrage du CSS
            el.style.display = 'flex'; 
            
            // Empêcher le scroll du reste de la page quand le modal est ouvert
            document.body.style.overflow = 'hidden';
        } else {
            el.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function getLegsContainer() {
        return document.getElementById('legs-list');
    }

    function getLegItems() {
        return [...getLegsContainer().querySelectorAll('.leg-item[data-id]')];
    }

    function handleDragStart(e) {
        dragSrcEl  = e.currentTarget;
        dragSrcIdx = getLegItems().indexOf(dragSrcEl);

        // Données transmises (ID de l'élément)
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragSrcEl.dataset.id);

        // Feedback visuel après un tick (sinon le snapshot du drag est affecté)
        setTimeout(() => {
            dragSrcEl.style.opacity   = '0.4';
            dragSrcEl.style.transform = 'scale(0.97)';
        }, 0);
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        if (!dragSrcEl) return;

        const target = e.currentTarget;
        if (target === dragSrcEl) return;

        target.style.borderColor  = 'var(--bordeaux)';
        target.style.background   = 'rgba(139,26,26,0.04)';
        target.style.boxShadow    = '0 0 0 2px rgba(139,26,26,0.2)';
    }

    function handleDragLeave(e) {
        const target = e.currentTarget;
        target.style.borderColor = '';
        target.style.background  = '';
        target.style.boxShadow   = '';
    }

    function handleDropOnItem(e) {
        e.preventDefault();
        e.stopPropagation();

        const target = e.currentTarget;
        handleDragLeave(e);

        if (!dragSrcEl || dragSrcEl === target) return;

        const container = getLegsContainer();
        const items     = getLegItems();
        const srcIdx    = items.indexOf(dragSrcEl);
        const tgtIdx    = items.indexOf(target);

        // Insertion avant ou après selon la position relative
        if (srcIdx < tgtIdx) {
            target.after(dragSrcEl);
        } else {
            target.before(dragSrcEl);
        }

        // Masquer la dernière flèche après réordonnancement
        updateArrows();

        // Sauvegarder en BDD
        saveOrder();
    }

    function handleDrop(e) {
        // Géré par handleDropOnItem, rien à faire ici
        e.preventDefault();
    }

    function handleDragEnd(e) {
        // Remettre le style de l'élément draggué
        if (dragSrcEl) {
            dragSrcEl.style.opacity   = '';
            dragSrcEl.style.transform = '';
            dragSrcEl.style.cursor    = 'grab';
        }

        // Nettoyer les styles de tous les items
        getLegItems().forEach(el => {
            el.style.borderColor = '';
            el.style.background  = '';
            el.style.boxShadow   = '';
        });

        dragSrcEl  = null;
        dragSrcIdx = null;
    }

    /** Cache la flèche du dernier élément */
    function updateArrows() {
        const items = getLegItems();
        items.forEach((el, idx) => {
            const arrow = el.querySelector('.leg-arrow');
            if (arrow) arrow.style.display = idx === items.length - 1 ? 'none' : '';
        });

        // Renuméroter les badges T1, T2…
        items.forEach((el, idx) => {
            const badge = el.querySelector('[data-order-badge]');
            if (badge) badge.textContent = `T${idx + 1}`;
        });
    }

    /** Envoie le nouvel ordre en BDD via POST JSON */
    async function saveOrder() {
        const ids = getLegItems().map(el => el.dataset.id);

        const tokenElement = document.querySelector('meta[name="csrf-token"]');
        const token = tokenElement ? tokenElement.getAttribute('content') : '';

        const status = document.getElementById('legs-order-status');
        if (status) {
            status.textContent = '↺ Sauvegarde…';
            status.style.color = 'var(--muted)';
            status.style.display = 'block';
        }

        try {
            console.log("token:", token);
            const res = await fetch(REORDER_URL, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ order: ids }),
            });

            const data = await res.json();

            if (data.success) {
                // Mettre à jour les badges T1, T2… avec les vrais ordres retournés
                if (data.legs) {
                    getLegItems().forEach(el => {
                        const leg = data.legs.find(l => String(l.id) === el.dataset.id);
                        if (leg) {
                            const badge = el.querySelector('[data-order-badge]') 
                                    ?? el.querySelector('div[style*="font-size:9px"]');
                            if (badge) badge.textContent = `T${leg.order}`;
                        }
                    });
                }
                if (status) {
                    status.textContent   = '✓ Ordre sauvegardé';
                    status.style.color   = 'var(--success)';
                    setTimeout(() => status.style.display = 'none', 2000);
                }
            } else {
                throw new Error(data.message ?? 'Erreur inconnue');
            }
        } catch (err) {
            console.error('Reorder error:', err);
            if (status) {
                status.textContent = '✗ Erreur de sauvegarde';
                status.style.color = 'var(--danger)';
            }
        }
    }

    // Init au chargement
    document.addEventListener('DOMContentLoaded', () => {
        updateArrows();
    });

    // Fermer les formulaires d'édition en cliquant ailleurs
    document.addEventListener('click', e => {
        if (!e.target.closest('[id^="edit-leg-"]') && !e.target.closest('button[onclick^="toggleEditLeg"]')) {
            document.querySelectorAll('[id^="edit-leg-"]').forEach(el => el.style.display = 'none');
        }
    });

    function toggleEditLeg(id) {
        const el = document.getElementById('edit-leg-' + id);
        if (!el) return;
        const isOpen = el.style.display !== 'none';
        // Fermer tous les autres
        document.querySelectorAll('[id^="edit-leg-"]').forEach(e => e.style.display = 'none');
        if (!isOpen) el.style.display = 'block';
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'label-select') {
            const selected = event.target.options[event.target.selectedIndex];
            if (!selected.value) return;

            const refType = selected.dataset.refType;
            const refId   = selected.dataset.refId;

            // Mettre à jour Type référence
            document.getElementById('reference-type-select').value = refType;

            // Mettre à jour Référence GPS — querySelector au lieu de la boucle
            const refIdSelect = document.getElementById('reference-id-select');
            const target = refIdSelect.querySelector(`option[value="${refId}"][data-type="${refType}"]`);
            
            console.log('target trouvé:', target);
            
            if (target) {
                refIdSelect.value = target.value;
                // Si ça ne suffit pas, forcer :
                target.selected = true;
            }
        }
    });
</script>

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="grid-2" style="gap:24px;">

    {{-- Infos du circuit --}}
    <div>
        <div class="card mb-16">
            <div class="card-header"><span class="card-title">Informations</span></div>
            <div class="card-body">
                <form action="{{ route('circuits.update', $circuit) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Nom du circuit</label>
                        <input type="text" name="name" value="{{ $circuit->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" name="code" value="{{ $circuit->code }}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3">{{ $circuit->description }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                </form>
            </div>
        </div>

        {{-- Ajout d'un étape du circuit --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Ajout d'une étape du circuit</span></div>
            <div class="card-body">
                <form action="{{ route('circuits.legs.store', $circuit) }}" method="POST">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div class="form-group" style="margin:0;">
                            <label>Label (référence GPS)</label>
                            <select name="label" id="label-select" required>
                                <optgroup label="Zones">
                                    @foreach($zones as $z)
                                        <option value="{{ $z->name }}"
                                            data-ref-type="zone"
                                            data-ref-id="{{ $z->id }}">
                                            {{ $z->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Checkpoints">
                                    @foreach($checkpoints as $cp)
                                        <option value="{{ $cp->name }}"
                                            data-ref-type="checkpoint"
                                            data-ref-id="{{ $cp->id }}">
                                            {{ $cp->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group" style="margin:0;">
                            <label>Type d'événement</label>
                            <select name="event_type" required>
                                <option value="enter_zone">Entrée zone</option>
                                <option value="leave_zone">Sortie zone</option>
                                <option value="pass_checkpoint">Passage checkpoint</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Type référence</label>
                            <select name="reference_type" id="reference-type-select" required>
                                <option value="zone">Zone</option>
                                <option value="checkpoint">Checkpoint</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Référence GPS</label>
                            <select name="reference_id" id="reference-id-select" required>
                                <optgroup label="Zones">
                                    @foreach($zones as $z)
                                        <option value="{{ $z->id }}" data-type="zone">{{ $z->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Checkpoints">
                                    @foreach($checkpoints as $cp)
                                        <option value="{{ $cp->id }}" data-type="checkpoint">{{ $cp->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Obligatoire ?</label>
                            <select name="optional">
                                <option value="0">✓ Obligatoire</option>
                                <option value="1">○ Optionnel</option>
                            </select>
                            <div style="font-size:10px;color:var(--muted);margin-top:3px;">
                                Optionnel = passé si présent, n'invalide pas la rotation
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <button type="submit" class="btn btn-primary">Ajouter l'étape</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Étapes du circuit --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Étapes du circuit</span>
            <span class="badge badge-muted">{{ $circuit->legs->count() }} étape(s)</span>
        </div>
        <div class="card-body">
            {{-- Liste des étapes avec drag & drop horizontal --}}
            <div id="legs-list" 
                style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-start;min-height:60px;padding:4px;border-radius:6px;transition:background 0.2s;"
                ondragover="event.preventDefault()"
                ondrop="handleDrop(event)">

                @forelse($circuit->legs as $leg)
                <div class="leg-item"
                    draggable="true"
                    data-id="{{ $leg->id }}"
                    ondragstart="handleDragStart(event)"
                    ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)"
                    ondrop="handleDropOnItem(event)"
                    ondragend="handleDragEnd(event)"
                    style="background:var(--panel);border:1px solid var(--border);border-radius:6px;
                            padding:8px 12px;min-width:180px;flex:0 1 auto;position:relative;
                            cursor:grab;transition:all 0.2s ease;user-select:none;">

                    {{-- Indicateur de drag --}}
                    <div style="position:absolute;top:4px;left:6px;color:var(--muted);font-size:11px;opacity:0.5;cursor:grab;">⠿</div>

                    <div style="display:flex;flex-direction:column;gap:4px;margin-left:12px;">

                        {{-- Badge type + actions --}}
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:9px;font-weight:800;text-transform:uppercase;
                                        padding:2px 5px;border-radius:3px;
                                        background:{{ str_contains($leg->event_type,'pass') ? 'rgba(139,26,26,0.12)' : 'var(--cream-d)' }};
                                        color:{{ str_contains($leg->event_type,'pass') ? 'var(--bordeaux)' : 'var(--ink-light)' }};">
                                {{ str_replace(['pass_','_zone'], ['',''], $leg->event_type) }}
                            </span>
                            <div style="display:flex;gap:5px;">
                                <button onclick="toggleEditLeg({{ $leg->id }})"
                                        style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;padding:0 3px;"
                                        title="Modifier">✎</button>
                                <form action="{{ route('circuits.legs.destroy', [$circuit, $leg]) }}" method="POST"
                                    onsubmit="return confirm('Supprimer cette étape ?')" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            style="background:none;border:none;cursor:pointer;color:var(--danger);font-size:13px;padding:0 3px;"
                                            title="Supprimer">✕</button>
                                </form>
                            </div>
                        </div>

                        {{-- Label --}}
                        <div style="font-weight:600;font-size:12px;white-space:nowrap;overflow:hidden;
                                    text-overflow:ellipsis;max-width:150px;color:var(--ink);"
                            title="{{ $leg->label }}">
                            {{ $leg->label }}
                        </div>

                        {{-- Référence --}}
                        <div style="font-size:10px;color:var(--muted);">
                            @if($leg->reference_type === 'zone')
                                {{ \App\Models\Zone::find($leg->reference_id)?->name ?? '?' }}
                            @else
                                {{ \App\Models\Checkpoint::find($leg->reference_id)?->name ?? '?' }}
                            @endif
                        </div>

                        {{-- Ordre --}}
                        <div style="font-size:9px;color:var(--bordeaux);font-family:var(--mono);font-weight:600;opacity:0.7;">
                            @if ($leg->optional == 0)
                                Obligatoire
                            @else
                                Optionnel
                            @endif
                        </div>
                    </div>

                    {{-- Flèche --}}
                    <div class="leg-arrow"
                        style="position:absolute;right:-14px;top:50%;transform:translateY(-50%);
                                color:var(--bordeaux);font-size:14px;z-index:2;opacity:0.5;pointer-events:none;">→</div>

                    {{-- Formulaire édition inline --}}
                    
                    <div id="edit-leg-{{ $leg->id }}" class="leg-edit-modal-container" style="display:none;">
                        <div class="modal-backdrop" onclick="toggleEditLeg({{ $leg->id }})"></div>
                        
                        <div class="modal-content">
                            <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="margin:0; font-size: 16px; color: var(--ink);">Modifier le trajet</h3>
                                <button type="button" onclick="toggleEditLeg({{ $leg->id }})" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--muted);">&times;</button>
                            </div>

                            <form action="{{ route('circuits.legs.update', [$circuit, $leg]) }}" method="POST">
                                @csrf @method('PUT')
                                
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                                    <div class="form-group" style="margin:0; grid-column: 1/-1;">
                                        <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:600;">Label</label>
                                        <input type="text" name="label" value="{{ $leg->label }}" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                                    </div>

                                    <div class="form-group" style="margin:0;">
                                        <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:600;">Type d'événement</label>
                                        <select name="event_type" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                                            <option value="enter_zone" {{ $leg->event_type==='enter_zone' ? 'selected' : '' }}>Entrée zone</option>
                                            <option value="leave_zone" {{ $leg->event_type==='leave_zone' ? 'selected' : '' }}>Sortie zone</option>
                                            <option value="pass_checkpoint" {{ $leg->event_type==='pass_checkpoint' ? 'selected' : '' }}>Passage checkpoint</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="margin:0;">
                                        <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:600;">Type référence</label>
                                        <select name="reference_type" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                                            <option value="zone" {{ $leg->reference_type==='zone' ? 'selected' : '' }}>Zone</option>
                                            <option value="checkpoint" {{ $leg->reference_type==='checkpoint' ? 'selected' : '' }}>Checkpoint</option>
                                        </select>
                                    </div>

                                    <div class="form-group" style="margin:0; grid-column: 1/-1;">
                                        <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:600;">Référence</label>
                                        <select name="reference_id" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                                            <optgroup label="Zones">
                                                @foreach($zones as $z)
                                                    <option value="{{ $z->id }}" {{ $leg->reference_type==='zone' && $leg->reference_id==$z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Checkpoints">
                                                @foreach($checkpoints as $cp)
                                                    <option value="{{ $cp->id }}" {{ $leg->reference_type==='checkpoint' && $leg->reference_id==$cp->id ? 'selected' : '' }}>{{ $cp->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>

                                    <div class="form-group" style="margin:0; grid-column: 1/-1;">
                                        <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:600;">Optionnel</label>
                                        <select name="optional">
                                            <option value="0" {{ $leg->optional == 0 ? 'selected' : '' }}>Obligatoire</option>
                                            <option value="1" {{ $leg->optional == 1 ? 'selected' : '' }}>Optionnel</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="margin-top:20px; display:flex; gap:10px; justify-content: flex-end;">
                                    <button type="button" onclick="toggleEditLeg({{ $leg->id }})" class="btn btn-ghost btn-sm" style="padding: 8px 16px;">Annuler</button>
                                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 16px; background: var(--bordeaux); color: white; border: none; border-radius: 6px;">Enregistrer les modifications</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                    <div style="color:var(--muted);font-size:12px;padding:20px;border:2px dashed var(--cream-dd);
                                border-radius:6px;width:100%;text-align:center;">
                        Aucune étape définie. Ajoutez-en une ci-dessous.
                    </div>
                @endforelse
            </div>

            {{-- Badge compteur mis à jour dynamiquement --}}
            <div id="legs-order-status"
                style="font-size:11px;color:var(--success);margin-top:6px;display:none;font-family:var(--mono);">
                ✓ Ordre sauvegardé
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')

@endpush