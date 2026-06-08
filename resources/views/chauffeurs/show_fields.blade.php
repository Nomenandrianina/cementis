{{-- <!-- Détails du Véhicule -->
<!-- Détails du Chauffeur Actuel -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4>{{ __('Détails du Chauffeur') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>{{ __('Nom') }} :</strong>
                <p>{{ $chauffeur_actuel->nom }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>{{ __('RFID') }} :</strong>
                <p>{{ $chauffeur_actuel->rfid }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>{{ __('RFID PHYSIQUE') }} :</strong>
                <p>{{ $chauffeur_actuel->rfid_physique }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>{{ __('Numéro badge') }} :</strong>
                <p>{{ $chauffeur_actuel->numero_badge }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>{{ __('Transporteur') }} :</strong>
                <p>{{ $chauffeur_actuel->related_transporteur->nom }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <strong>{{ __('Contact') }} :</strong>
                <p>{{ $chauffeur_actuel->contact }}</p>
            </div>
        </div>
    </div>
</div>


<!-- Historique des Mises à Jour du Chauffeur -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h4>{{ __('Historique des Mises à Jour du Chauffeur') }}</h4>
    </div>
    <div class="card-body">
        @if($chauffeur_updates->isEmpty())
            <p class="text-center text-muted">Aucune mise à jour trouvée pour ce chauffeur.</p>
        @else
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('RFID') }}</th>
                        <th>{{ __('RFID Physique') }}</th>
                        <th>{{ __('Numéro Badge') }}</th>
                        <th>{{ __('Transporteur') }}</th>
                        <th>{{ __('Date d\'installation') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chauffeur_updates as $index => $update)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $update->nom }}</td>
                            <td>{{ $update->rfid ?? '' }}</td>
                            <td>{{ $update->rfid_physique ?? '' }}</td>
                            <td>{{ $update->numero_badge ?? '-' }}</td>
                            <td>{{ $update->transporteur->nom }}</td>
                            <td>{{ $update->date_installation }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
 --}}

<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px !important; overflow: hidden;">
    <div class="card-header bg-white border-bottom py-3">
        <h4 class="h5 font-weight-bold mb-0 text-dark">
            <i class="fas fa-user text-muted mr-2 small"></i> Informations Personnelles
        </h4>
    </div>
    
    <div class="card-body p-4 bg-white">
        <div class="row">
            <div class="col-md-4 col-sm-6 mb-4">
                <span class="text-muted font-weight-bold small text-uppercase" style="letter-spacing: 0.5px;">{{ __('Nom') }}</span>
                <p class="text-dark font-weight-medium mb-0 mt-1 h6">{{ $chauffeur_actuel->nom }}</p>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <span class="text-muted font-weight-bold small text-uppercase" style="letter-spacing: 0.5px;">{{ __('Contact') }}</span>
                <p class="text-dark font-weight-medium mb-0 mt-1 h6">{{ $chauffeur_actuel->contact }}</p>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <span class="text-muted font-weight-bold small text-uppercase" style="letter-spacing: 0.5px;">{{ __('Transporteur') }}</span>
                <p class="mb-0 mt-1"><span class="badge badge-light px-3 py-2 text-secondary border font-weight-medium" style="border-radius: 6px;">{{ $chauffeur_actuel->related_transporteur->nom }}</span></p>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <span class="text-muted font-weight-bold small text-uppercase" style="letter-spacing: 0.5px;">{{ __('Numéro badge') }}</span>
                <p class="text-dark font-weight-medium mb-0 mt-1 h6">{{ $chauffeur_actuel->numero_badge }}</p>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <span class="text-muted font-weight-bold small text-uppercase" style="letter-spacing: 0.5px;">{{ __('RFID') }}</span>
                <p class="text-mono mb-0 mt-1 text-secondary small font-weight-bold">{{ $chauffeur_actuel->rfid ?? '-' }}</p>
            </div>
            <div class="col-md-4 col-sm-6 mb-4">
                <span class="text-muted font-weight-bold small text-uppercase" style="letter-spacing: 0.5px;">{{ __('RFID PHYSIQUE') }}</span>
                <p class="text-mono mb-0 mt-1 text-secondary small font-weight-bold">{{ $chauffeur_actuel->rfid_physique ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-5" style="border-radius: 12px !important; overflow: hidden;">
    <div class="card-header bg-white border-bottom py-3">
        <h4 class="h5 font-weight-bold mb-0 text-dark">
            <i class="fas fa-history text-muted mr-2 small"></i> Historique des Mises à Jour
        </h4>
    </div>
    
    <div class="card-body p-0 bg-white">
        @if($chauffeur_updates->isEmpty())
            <div class="p-5 text-center">
                <i class="fas fa-folder-open text-muted fa-2x mb-3"></i>
                <p class="text-muted mb-0 font-weight-medium">Aucune mise à jour trouvée pour ce chauffeur.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table alpha-modern-table mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">#</th>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">{{ __('Nom') }}</th>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">{{ __('RFID') }}</th>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">{{ __('RFID Physique') }}</th>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">{{ __('Numéro Badge') }}</th>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">{{ __('Transporteur') }}</th>
                            <th class="border-top-0 text-secondary" style="font-weight: 600;">{{ __('Date d\'installation') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chauffeur_updates as $index => $update)
                            <tr>
                                <td class="text-muted font-weight-bold">{{ $index + 1 }}</td>
                                <td class="font-weight-medium text-dark">{{ $update->nom }}</td>
                                <td class="text-muted small">{{ $update->rfid ?? '-' }}</td>
                                <td class="text-muted small">{{ $update->rfid_physique ?? '-' }}</td>
                                <td><span class="badge badge-secondary px-2 py-1 font-weight-normal" style="border-radius: 4px;">{{ $update->numero_badge ?? '-' }}</span></td>
                                <td>{{ $update->transporteur->nom ?? '-' }}</td>
                                <td class="text-muted">{{ $update->date_installation }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>