<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/circuit.css') }}"> 
<div class="wrap" id="main-view">

    <div class="topbar">
        <div class="topbar-left">
            <div class="page-icon">
                <svg fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0
                           011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1
                           1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <div>
                <div class="page-title-text" style="font-size: 1.5rem; font-weight: 600;">Circuits</div>
                <div class="page-sub">{{ $circuits->count() }} circuit(s) configuré(s)</div>
            </div>
        </div>
        <button class="btn-new"  data-toggle="modal" data-target="#modalCreateCircuit">
            <svg fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau circuit
        </button>
    </div>
 
    <div class="stats-row">
        <div class="stat">
            <div class="stat-lbl">Total circuits</div>
            <div class="stat-num">{{ $circuits->count() }}</div>
            <div class="stat-sub">configurés</div>
        </div>
        <div class="stat stat-success">
            <div class="stat-lbl">Actifs</div>
            <div class="stat-num" style="color:#3B6D11;">{{ $circuits->where('active', true)->count() }}</div>
            <div class="stat-sub">en service</div>
        </div>
        <div class="stat stat-info">
            <div class="stat-lbl">Étapes totales</div>
            <div class="stat-num">{{ $circuits->sum('legs_count') }}</div>
            <div class="stat-sub">sur tous circuits</div>
        </div>
        <div class="stat stat-danger">
            <div class="stat-lbl">Camions assignés</div>
            <div class="stat-num">{{ $circuits->sum(fn($c) => $c->vehicles->count()) }}</div>
            <div class="stat-sub">véhicules</div>
        </div>
    </div>
 
    {{-- Table --}}
    <div class="card">
        <div class="rot-card-header">
            <span class="rot-card-title">Liste des circuits</span>
            <span class="rot-badge rot-badge-muted">{{ $circuits->count() }} entrée(s)</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Circuit</th>
                        <th>Code</th>
                        <th>Étapes</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($circuits as $circuit)
                    <tr>
                        <td>
                            <div class="circuit-name">{{ $circuit->name }}</div>
                            @if($circuit->description)
                                <div class="circuit-desc">{{ $circuit->description }}</div>
                            @endif
                        </td>
                        <td><span class="code-pill">{{ $circuit->code }}</span></td>
                        <td><span class="num-badge num-steps">{{ $circuit->legs_count }}</span></td>
                        <td>
                            @if($circuit->active)
                                <span class="badge badge-ok"><span class="badge-dot"></span>Actif</span>
                            @else
                                <span class="badge badge-off"><span class="badge-dot"></span>Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('circuits.edit', $circuit) }}" class="btn-sm btn-ghost">Configurer</a>
                                <div class="divider"></div>
                                <a href="{{ route('circuits.objectives.index', $circuit) }}" class="btn-sm btn-blue">Objectifs</a>
                                <div class="divider"></div>
                                <form action="{{ route('circuits.destroy', $circuit) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce circuit ?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0
                                               011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553
                                               2.276A1 1 0 0021 18.382V7.618a1 1 0
                                               00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                </div>
                                <div class="empty-title">Aucun circuit</div>
                                <div class="empty-sub">
                                    <a href="#" onclick="openModal(); return false;"
                                       style="color:var(--bx);text-decoration:none;font-weight:600;">
                                        Créez votre premier circuit
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
 
</div>
 
{{-- ═══════════════════════════════════════════════════════
     MODAL — placé HORS du div.wrap, juste avant @endsection
     Cela évite tout problème d'overflow/transform parent
     ═══════════════════════════════════════════════════════ --}}

<div class="modal fade" id="modalCreateCircuit" tabindex="-1" role="dialog" aria-labelledby="mmodalCreateCircuitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document"> <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border: none; padding: 1.5rem;">
                <h5 class="modal-title d-flex align-items-center" id="mmodalCreateCircuitLabel" style="font-weight: 600; letter-spacing: 0.5px;">
                    <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <svg class="text-primary" style="width:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    Définissez un nouveau circuit de livraison
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('circuits.store') }}" method="POST">
              @csrf
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    
                    <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px; background-color: #e3f2fd; color: #0d47a1;">
                        <small><i class="fas fa-info-circle mr-1"></i> Sélectionnez les paramètres pour créer un circuit.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Nom du circuit</label>
                            <input type="text" name="name" class="form-control shadow-none" 
                                style="border-radius: 8px; height: 45px;"
                                value="{{ old('name') }}"
                                placeholder="Ex: Tamatave – Tanà – Tamatave"
                                required>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Code unique</label>
                            <input type="text" name="code" class="form-control shadow-none" 
                                style="border-radius: 8px; height: 45px;"
                                value="{{ old('code') }}"
                                placeholder="Ex: TAM-TAN-TAM"
                                required>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="text-muted small font-weight-bold text-uppercase mb-2 d-block">Description</label>
                            <textarea class="form-control" name="description" rows="6"
                              placeholder="Description optionnelle du circuit…"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal" style="text-decoration: none;">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px; height: 48px; font-weight: 600; background: #1a73e8;">
                        <i class="fas fa-play-circle mr-2"></i> Créer le circuit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

 
@endsection