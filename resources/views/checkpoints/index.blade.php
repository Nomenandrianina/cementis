<link rel="stylesheet" href="{{ asset('css/checkpoint.css') }}">
@extends('layouts.app')
@section('title', 'Checkpoints')
@section('page-title', 'Points de contrôle')

@section('topbar-actions')
    <form action="{{ route('checkpoints.sync') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn-sync">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Sync GPS
        </button>
    </form>
@endsection

@section('content')



<script>
    function openEditModal(checkpoint) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        
        let url = "{{ route('checkpoints.update', ':id') }}";
        // On remplace le placeholder par l'ID réel
        url = url.replace(':id', checkpoint.id);
        
        form.action = url;
        
        // On remplit les champs
        document.getElementById('edit_name').value = checkpoint.name;
        document.getElementById('edit_type').value = checkpoint.type;
        document.getElementById('edit_description').value = checkpoint.description || '';
        document.getElementById('edit_lat').value = checkpoint.lat;
        document.getElementById('edit_lng').value = checkpoint.lng;
        document.getElementById('edit_radius').value = checkpoint.radius;
        
        modal.style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Fermer si on clique en dehors du blanc
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) closeEditModal();
    }
</script>

{{-- ── Stats ─────────────────────────────────────────────────────────────── --}}
<div class="stats-row">
    <div class="stat stat-success">
        <div class="stat-lbl">Checkpoints</div>
        <div class="stat-num">{{ $checkpoints->count() }}</div>
        <div class="stat-sub">définis</div>
    </div>
    <div class="stat stat-info">
        <div class="stat-lbl">Synchronisés</div>
        <div class="stat-num stat-num--green">{{ $checkpoints->whereNotNull('gps_marker_id')->count() }}</div>
        <div class="stat-sub">avec GPS</div>
    </div>
    <div class="stat stat-danger">
        <div class="stat-lbl">En attente</div>
        <div class="stat-num stat-num--amber">{{ $checkpoints->whereNull('gps_marker_id')->count() }}</div>
        <div class="stat-sub">sans GPS ID</div>
    </div>
</div>

{{-- ── Layout 2 colonnes ────────────────────────────────────────────────── --}}
<div class="cp-layout">

    {{-- Formulaire création --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title-row">
                <div class="card-dot"></div>
                <span class="card-title">Ajouter un checkpoint</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('checkpoints.store') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select  shadow-none" style="border-radius: 8px 8px 8px 8px; height: 45px;" required>
                        <option value="" selected>— Sélectionner —</option>
                        @foreach(\App\Models\Checkpoint::TYPES as $key => $label)
                            <option value="{{ $key }}">
                                 {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size:11px;color:var(--muted);margin-top:4px;">
                        <strong>Obligatoire</strong> = obligatoire (rotation invalide si manqué) ·
                        <strong>Optionnel</strong> = optionnel (juste enregistré si passé)
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input class="form-input @error('name') error @enderror"
                           type="text" name="name"
                           value="{{ old('name') }}"
                           placeholder="Ex : Checkpoint Ambodimita" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" name="description"
                              rows="2" placeholder="Description optionnelle…">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Coordonnées & rayon</label>
                    <div class="coords-grid">
                        <div>
                            <input class="form-input @error('lat') error @enderror"
                                   type="number" name="lat" step="0.0000001"
                                   value="{{ old('lat') }}" placeholder="Latitude" required>
                            <div class="form-hint">ex : -18.865963</div>
                            @error('lat')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <input class="form-input @error('lng') error @enderror"
                                   type="number" name="lng" step="0.0000001"
                                   value="{{ old('lng') }}" placeholder="Longitude" required>
                            <div class="form-hint">ex : 47.486343</div>
                            @error('lng')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <input class="form-input @error('radius') error @enderror"
                                   type="number" name="radius" step="0.001"
                                   value="{{ old('radius', '0.1') }}" placeholder="Rayon (km)" required>
                            <div class="form-hint">défaut : 0.1 km</div>
                            @error('radius')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;">
                        <path d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer le checkpoint
                </button>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title-row">
                <div class="card-dot"></div>
                <span class="card-title">Checkpoints définis</span>
            </div>
             <span class="count-pill">{{ $checkpoints->count() }}</span> 
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Coordonnées</th>
                        <th>Rayon</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checkpoints as $cp)
                        <tr>
                            <td>
                                <div class="cp-name">{{ $cp->name }}</div>
                                @if($cp->description)
                                    <div class="cp-desc">{{ $cp->description }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="mono-sm">{{ $cp->lat }}</div>
                                <div class="mono-sm">{{ $cp->lng }}</div>
                            </td>
                            <td>
                                <span class="radius-badge">{{ $cp->radius }} km</span>
                            </td>
                            <td>
                                @if($cp->type === 'obligatoire')
                                    <span class="gps-badge" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                                        Obligatoire
                                    </span>
                                @else
                                    <span class="gps-badge" style="background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db;">
                                        Optionnel
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" 
                                            class="btn-edit" 
                                            title="Modifier"
                                            onclick="openEditModal({{ $cp->toJson() }})">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <form action="{{ route('checkpoints.destroy', $cp) }}" method="POST"
                                        onsubmit="return confirm('Supprimer ce checkpoint ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del" title="Supprimer">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-td">
                                <div class="empty-icon">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                         stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                                         style="width:20px;height:20px;">
                                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="empty-title">Aucun checkpoint</div>
                                <div class="empty-sub">Créez-en un ou synchronisez depuis l'API GPS.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal-content card">
        <div class="card-header">
            <div class="card-title-row">
                <div class="card-dot" style="background: var(--info);"></div>
                <span class="card-title">Modifier le Checkpoint</span>
            </div>
            <button class="btn-close" onclick="closeEditModal()">&times;</button>
        </div>
        
        <div class="card-body">
            <form id="editForm" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" id="edit_type" class="form-select shadow-none" style="border-radius: 8px; height: 45px;" required>
                        <option value="" selected>— Sélectionner —</option>
                        @foreach(\App\Models\Checkpoint::TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input class="form-input" type="text" name="name" id="edit_name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" name="description" id="edit_description" rows="2"></textarea>
                </div>

                <div class="coords-grid">
                    <div>
                        <label class="form-label">Lat</label>
                        <input class="form-input" type="number" name="lat" id="edit_lat" step="0.0000001" required>
                    </div>
                    <div>
                        <label class="form-label">Lng</label>
                        <input class="form-input" type="number" name="lng" id="edit_lng" step="0.0000001" required>
                    </div>
                    <div>
                        <label class="form-label">Rayon</label>
                        <input class="form-input" type="number" name="radius" id="edit_radius" step="0.001" required>
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                    <button type="button" onclick="closeEditModal()" class="btn-cancel" >Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection