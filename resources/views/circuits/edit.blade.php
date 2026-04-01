@extends('layouts.app')

@section('title', 'Circuit : ' . $circuit->name)
@section('page-title', $circuit->name)

@section('topbar-actions')
    <a href="{{ route('circuits.index') }}" class="btn btn-ghost btn-sm">← Circuits</a>
    <a href="{{ route('circuits.objectives.index', $circuit) }}" class="btn btn-blue btn-sm">Objectifs</a>
@endsection

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

        {{-- Camions affectés --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Camions affectés</span></div>
            <div class="card-body">
                <form action="{{ route('circuits.vehicles.assign', $circuit) }}" method="POST" style="margin-bottom:16px;">
                    @csrf
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label>Camion</label>
                            <select name="vehicle_id" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Depuis</label>
                            <input type="date" name="assigned_from" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Jusqu'au</label>
                            <input type="date" name="assigned_until">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">+</button>
                    </div>
                </form>

                @foreach($circuit->vehicles as $v)
                    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid var(--border);">
                        <div style="flex:1;">
                            <div style="font-weight:600;">{{ $v->name }}</div>
                            <div style="font-size:11px;color:var(--muted);" class="mono">
                                Depuis {{ $v->pivot->assigned_from }}
                                @if($v->pivot->assigned_until) → {{ $v->pivot->assigned_until }} @endif
                            </div>
                        </div>
                        <form action="{{ route('circuits.vehicles.remove', [$circuit, $v]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Retirer</button>
                        </form>
                    </div>
                @endforeach

                @if($circuit->vehicles->isEmpty())
                    <p style="color:var(--muted);font-size:12px;">Aucun camion affecté.</p>
                @endif
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
            <div id="legs-list" style="margin-bottom:20px;">
                @forelse($circuit->legs as $leg)
                    <div class="leg-item" data-id="{{ $leg->id }}"
                         style="background:var(--panel);border:1px solid var(--border);border-radius:4px;padding:12px;margin-bottom:8px;position:relative;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="cursor:grab;color:var(--muted);font-size:18px;">⠿</span>
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    {{-- <span style="font-family:var(--mono);font-size:11px;color:var(--accent);font-weight:700;">T{{ $leg->order }}</span> --}}
                                    <span style="font-weight:600;">{{ $leg->label }}</span>
                                </div>
                                <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                    {{ $leg->event_type }} ·
                                    {{ $leg->reference_type }} :
                                    @if($leg->reference_type === 'zone')
                                        {{ \App\Models\Zone::find($leg->reference_id)?->name ?? '?' }}
                                    @else
                                        {{ \App\Models\Checkpoint::find($leg->reference_id)?->name ?? '?' }}
                                    @endif
                                    @if($leg->direction !== 'any')
                                        · {{ $leg->direction }}
                                    @endif
                                </div>
                            </div>
                            <div style="display:flex;gap:6px;">
                                <button onclick="toggleEditLeg({{ $leg->id }})" class="btn btn-ghost btn-sm">✎</button>
                                <form action="{{ route('circuits.legs.destroy', [$circuit, $leg]) }}" method="POST"
                                      onsubmit="return confirm('Supprimer cette étape ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </div>
                        </div>

                        {{-- Formulaire d'édition inline --}}
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
                                            <option value="enter_zone" @selected($leg->event_type==='enter_zone')>Entrée zone</option>
                                            <option value="leave_zone" @selected($leg->event_type==='leave_zone')>Sortie zone</option>
                                            <option value="pass_checkpoint" @selected($leg->event_type==='pass_checkpoint')>Passage checkpoint</option>
                                            <option value="pass_depot" @selected($leg->event_type==='pass_depot')>Dépôt</option>
                                            <option value="pass_garage" @selected($leg->event_type==='pass_garage')>Garage</option>
                                            <option value="pass_parking" @selected($leg->event_type==='pass_parking')>Parking</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label>Type référence</label>
                                        <select name="reference_type" required>
                                            <option value="zone" @selected($leg->reference_type==='zone')>Zone</option>
                                            <option value="checkpoint" @selected($leg->reference_type==='checkpoint')>Checkpoint</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label>Référence</label>
                                        <select name="reference_id" required>
                                            <optgroup label="Zones">
                                                @foreach($zones as $z)
                                                    <option value="{{ $z->id }}" @selected($leg->reference_type==='zone' && $leg->reference_id==$z->id)>{{ $z->name }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Checkpoints">
                                                @foreach($checkpoints as $cp)
                                                    <option value="{{ $cp->id }}" @selected($leg->reference_type==='checkpoint' && $leg->reference_id==$cp->id)>{{ $cp->name }}</option>
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
                    <p style="color:var(--muted);font-size:12px;margin-bottom:16px;">Aucune étape définie.</p>
                @endforelse
            </div>

            {{-- Ajout d'une étape --}}
            <div style="border-top:1px solid var(--border);padding-top:16px;">
                {{-- <div class="card-title" style="font-size:12px;margin-bottom:12px;">+ Ajouter une étape</div> --}}
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

</div>
@endsection

@push('scripts')
<script>
function toggleEditLeg(id) {
    const el = document.getElementById('edit-leg-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush