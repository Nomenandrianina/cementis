<div class="table-container custom-scroll" id="table-container">
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