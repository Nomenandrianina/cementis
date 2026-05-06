<link rel="stylesheet" href="{{ asset('css/checkpoint.css') }}">
@extends('layouts.app')
@section('title', 'Checkpoints')
@section('page-title', 'Points de contrôle')

@section('topbar-actions')
    <form action="{{ route('checkpoints.sync') }}" method="POST" style="display:inline;margin-top: 17px;">
        @csrf
        <button type="submit" class="btn-sync">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Sync GPS
        </button>
    </form>
    <button type="button" class="btn-create" onclick="toggleCheckpointModal(true)">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;margin-right:5px;">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau Checkpoint
    </button>
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

    function toggleCheckpointModal(show) {
        const modal = document.getElementById('checkpointModal');
        modal.style.display = show ? 'flex' : 'none';
    }

    // Fermer si on clique sur l'overlay noir (à l'extérieur de la carte)
    window.onclick = function(event) {
        const modalCreate = document.getElementById('checkpointModal');
        if (event.target == modalCreate) {
            toggleCheckpointModal(false);
        }
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

{{-- ── Liste ─────────────────────────────────────────────────────────────── --}}
<div class="card-modern">
    <div class="card-header-pro">
        <div class="header-main">
            <div class="title-section">
                <span class="icon-bg"><i class="fas fa-map-marked-alt"></i></span>
                <div>
                    <h3>Checkpoints</h3>
                    <p>{{ $checkpoints->total() }} points enregistrés</p>
                </div>
            </div>
            <div class="header-actions">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Filtrer par nom..." id="searchInput">
                </div>
            </div>
        </div>
    </div>

    <div class="table-container custom-scroll">
        <table class="table-clean">
            <thead>
                <tr>
                    <th>Checkpoint</th>
                    <th>Localisation</th>
                    <th>Rayon</th>
                    <th>Type</th>
                    <th class="text-right">Gestion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($checkpoints as $cp)
                <tr>
                    <td>
                        <div class="cp-info-box">
                            <div class="cp-avatar">{{ substr($cp->name, 0, 1) }}</div>
                            <span class="cp-name">{{ $cp->name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="coord-pill">
                            <i class="fas fa-location-arrow"></i>
                            {{ number_format($cp->lat, 4) }}, {{ number_format($cp->lng, 4) }}
                        </div>
                    </td>
                    <td><span class="radius-text">{{ $cp->radius }} km</span></td>
                    <td>
                        <span class="status-badge {{ $cp->type === 'obligatoire' ? 'type-req' : 'type-opt' }}">
                            {{ $cp->type }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="action-flex">
                            <button class="btn-table edit" onclick='openEditModal(@json($cp))'>
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <form action="{{ route('checkpoints.destroy', $cp) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-table delete" onclick="return confirm('Supprimer ?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="card-footer-pro">
        {{ $checkpoints->links() }}
    </div>
</div>

<!-- Modal de modification (initialement cachée) -->
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

<!-- Modal de création -->
<div id="checkpointModal" class="modal-overlay" style="display: none;">
    <div class="modal-content card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title-row">
                <div class="card-dot"></div>
                <span class="card-title">Ajouter un checkpoint</span>
            </div>
            <button type="button" class="btn-close" onclick="toggleCheckpointModal(false)" >&times;</button>
        </div>
        
        <div class="card-body">
            <form action="{{ route('checkpoints.store') }}" method="POST">
                @csrf
                {{-- Votre formulaire actuel reste ici --}}
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select shadow-none" required>
                        <option value="" selected>— Sélectionner —</option>
                        @foreach(\App\Models\Checkpoint::TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input class="form-input" type="text" name="name" placeholder="Ex : Checkpoint Ambodimita" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" name="description" rows="2" placeholder="Description..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Coordonnées & rayon</label>
                    <div class="coords-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <input class="form-input" type="number" name="lat" step="0.0000001" placeholder="Lat" required>
                        <input class="form-input" type="number" name="lng" step="0.0000001" placeholder="Lng" required>
                        <input class="form-input" type="number" name="radius" step="0.001" value="0.1" placeholder="Rayon" required>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-submit">Créer le checkpoint</button>
                    <button type="button" class="btn-cancel" onclick="toggleCheckpointModal(false)">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection