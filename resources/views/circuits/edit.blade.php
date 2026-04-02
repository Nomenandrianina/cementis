@extends('layouts.app')

@section('title', 'Circuit : ' . $circuit->name)
@section('page-title', $circuit->name)

@section('topbar-actions')
    <a href="{{ route('circuits.index') }}" class="btn btn-ghost btn-sm">← Circuits</a>
    <a href="{{ route('circuits.objectives.index', $circuit) }}" class="btn btn-blue btn-sm">Objectifs</a>
@endsection
<script>
    function toggleEditLeg(id) {
        const el = document.getElementById('edit-leg-' + id);
        
        if (!el) {
            console.error("Élément introuvable : edit-leg-" + id);
            return;
        }

        // On vérifie le style calculé pour être sûr de l'état actuel
        if (window.getComputedStyle(el).display === 'none') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }
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
                            <label>Label (ex: T1 – Arrivée Tamatave)</label>
                            <input type="text" name="label" required placeholder="T1 – Arrivée Tamatave">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Type d'événement</label>
                            <select name="event_type" required>
                                <option value="enter_zone">Entrée zone</option>
                                <option value="leave_zone">Sortie zone</option>
                                <option value="pass_checkpoint">Passage checkpoint</option>
                                <option value="pass_depot">Dépôt</option>
                                <option value="pass_garage">Garage</option>
                                <option value="pass_parking">Parking</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Type référence</label>
                            <select name="reference_type" required>
                                <option value="zone">Zone</option>
                                <option value="checkpoint">Checkpoint</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Référence GPS</label>
                            <select name="reference_id" required>
                                <optgroup label="Zones">
                                    @foreach($zones as $z)
                                        <option value="{{ $z->id }}">{{ $z->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Checkpoints">
                                    @foreach($checkpoints as $cp)
                                        <option value="{{ $cp->id }}">{{ $cp->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
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
            {{-- Étapes existantes --}}
            <div id="legs-list" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                @forelse($circuit->legs as $leg)
                    <div class="leg-item" data-id="{{ $leg->id }}" 
                        style="background: var(--panel); border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; min-width: 180px; flex: 0 1 auto; position: relative; transition: all 0.2s ease;">
                        
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            {{-- Badge de type pour identification rapide --}}
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 2px 5px; border-radius: 3px; 
                                    background: {{ str_contains($leg->event_type, 'pass') ? 'var(--accent-muted)' : 'var(--border)' }}; color: var(--text);">
                                    {{ str_replace('pass_', '', $leg->event_type) }}
                                </span>
                                <div style="display: flex; gap: 5px;">
                                    <button onclick="toggleEditLeg({{ $leg->id }})" 
                                            style="background: none; border: none; cursor: pointer; color: var(--muted); font-size: 14px; padding: 0 4px;" title="Modifier">✎</button>
                                    
                                    <form action="{{ route('circuits.legs.destroy', [$circuit, $leg]) }}" method="POST" 
                                        onsubmit="return confirm('Supprimer cette étape ?')" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background: none; border: none; cursor: pointer; color: #ff5252; font-size: 14px; padding: 0 4px;" title="Supprimer">✕</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Label principal plus compact --}}
                            <div style="font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="{{ $leg->label }}">
                                {{ $leg->label }}
                            </div>

                            {{-- Détails discrets --}}
                            <div style="font-size: 10px; color: var(--muted); display: flex; align-items: center; gap: 4px;">
                                @if($leg->reference_type === 'zone')
                                    {{ \App\Models\Zone::find($leg->reference_id)?->name ?? '?' }}
                                @else
                                    {{ \App\Models\Checkpoint::find($leg->reference_id)?->name ?? '?' }}
                                @endif
                            </div>
                        </div>

                        {{-- Flèche de liaison (optionnel, pour l'aspect visuel du circuit) --}}
                        <div style="position: absolute; right: -12px; top: 50%; transform: translateY(-50%); color: var(--border); font-size: 14px; z-index: 1;">
                            →
                        </div>

                        {{-- Le formulaire d'édition doit passer en "overlay" ou "absolu" pour ne pas casser la grille --}}
                        <div id="edit-leg-{{ $leg->id }}" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
                            <form action="{{ route('circuits.legs.update', [$circuit, $leg]) }}" method="POST">
                                @csrf @method('PUT')
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                    <div class="form-group" style="margin:0;">
                                        <label>Label</label>
                                        <input type="text" name="label" value="{{ $leg->label }}" required>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label>Type d'événement</label>
                                        <select name="event_type" required>
                                            <option value="enter_zone" {{ $leg->event_type === 'enter_zone' ? 'selected' : '' }}>Entrée zone</option>
                                            <option value="leave_zone" {{ $leg->event_type === 'leave_zone' ? 'selected' : '' }}>Sortie zone</option>
                                            <option value="pass_checkpoint" {{ $leg->event_type === 'pass_checkpoint' ? 'selected' : '' }}>Passage checkpoint</option>
                                            <option value="pass_depot" {{ $leg->event_type === 'pass_depot' ? 'selected' : '' }}>Dépôt</option>
                                            <option value="pass_garage" {{ $leg->event_type === 'pass_garage' ? 'selected' : '' }}>Garage</option>
                                            <option value="pass_parking" {{ $leg->event_type === 'pass_parking' ? 'selected' : '' }}>Parking</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label>Type référence</label>
                                        <select name="reference_type" required>
                                            <option value="zone" {{ $leg->reference_type==='zone' ? 'selected' : '' }}>Zone</option>
                                            <option value="checkpoint" {{ $leg->reference_type==='checkpoint' ? 'selected' : '' }}>Checkpoint</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label>Référence</label>
                                        <select name="reference_id" required>
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
                                </div>
                                <div style="margin-top:10px;display:flex;gap:8px;">
                                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                                    <button type="button" onclick="toggleEditLeg({{ $leg->id }})" class="btn btn-ghost btn-sm">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <p style="color:var(--muted);font-size:12px;">Aucune étape définie.</p>
                @endforelse
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')

@endpush