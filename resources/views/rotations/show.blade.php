@extends('layouts.app')

@section('title', 'Rotation #' . $rotation->id)
@section('page-title', 'Rotation #' . $rotation->id)

@section('topbar-actions')
    <a href="{{ route('rotations.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="grid-2" style="gap:24px;">

    {{-- Informations générales --}}
    <div>
        <div class="card mb-16">
            <div class="card-header">
                <span class="card-title">Informations</span>
                @if($rotation->is_valid)
                    <span class="badge badge-success">✓ Valide</span>
                @else
                    <span class="badge badge-danger">✗ Invalide</span>
                @endif
            </div>
            <div class="card-body">
                <table style="width:100%;font-size:13px;">
                    <tbody>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;width:40%;">Camion</td>
                            <td style="font-weight:600;">{{ $rotation->rvehicule->name }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Immatriculation</td>
                            <td class="mono">{{ $rotation->rvehicule->plate_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Circuit</td>
                            <td>{{ $rotation->circuit->name }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Mois compté</td>
                            <td class="mono">{{ $rotation->counted_month ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Statut</td>
                            <td>
                                @switch($rotation->status)
                                    @case('completed') <span class="badge badge-success">Complète</span> @break
                                    @case('in_progress') <span class="badge badge-blue">En cours</span> @break
                                    @case('cancelled') <span class="badge badge-danger">Annulée</span> @break
                                @endswitch
                            </td>
                        </tr>
                        @if($rotation->invalidation_reason)
                        <tr>
                            <td style="color:var(--muted);padding:6px 0;">Raison</td>
                            <td style="color:var(--danger);font-size:12px;">{{ $rotation->invalidation_reason }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Durées vs objectifs --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Durée vs Objectif</span>
            </div>
            <div class="card-body">
                @php
                    $targetDur = $objective?->target_duration_minutes;
                    $actualDur = $rotation->duration_minutes;
                @endphp

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;margin-bottom:16px;">
                    <div>
                        <div class="stat-label">Début (T1)</div>
                        <div class="mono" style="font-size:14px;color:var(--text);">
                            {{ $rotation->started_at?->format('d/m/Y') }}<br>
                            <span style="color:var(--accent);font-size:18px;font-family:var(--head);font-weight:700;">
                                {{ $rotation->started_at?->format('H:i') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">Fin (T5)</div>
                        <div class="mono" style="font-size:14px;color:var(--text);">
                            {{ $rotation->completed_at?->format('d/m/Y') ?? '—' }}<br>
                            <span style="color:var(--accent);font-size:18px;font-family:var(--head);font-weight:700;">
                                {{ $rotation->completed_at?->format('H:i') ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="stat-label">Durée totale</div>
                        <span style="font-family:var(--head);font-size:24px;font-weight:900;color:
                            @if($actualDur && $targetDur)
                                {{ $actualDur <= $targetDur ? 'var(--success)' : 'var(--danger)' }}
                            @else
                                var(--accent)
                            @endif
                        ">
                            @if($actualDur)
                                {{ intdiv($actualDur, 60) }}h{{ $actualDur % 60 }}m
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </div>

                @if($targetDur && $actualDur)
                    @php $pct = min(round($actualDur / $targetDur * 100), 150); @endphp
                    <div style="margin-top:8px;">
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:4px;">
                            <span>Objectif : {{ intdiv($targetDur, 60) }}h{{ $targetDur % 60 }}m</span>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $pct > 100 ? 'over' : 'good' }}" style="width:{{ min($pct, 100) }}%;"></div>
                        </div>
                        @php $ecart = $actualDur - $targetDur; @endphp
                        <div style="text-align:right;font-size:12px;margin-top:6px;" class="{{ $ecart > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $ecart > 0 ? '+' : '' }}{{ intdiv($ecart, 60) }}h{{ abs($ecart % 60) }}m vs objectif
                        </div>
                    </div>
                @elseif(!$targetDur)
                    <p style="font-size:12px;color:var(--muted);">Aucun objectif défini pour ce circuit.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Timeline des étapes --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Étapes de la rotation</span>
            <span class="badge badge-muted">{{ $rotation->rotationLegs->count() }} / {{ $rotation->circuit->legs->count() }}</span>
        </div>
        <div class="card-body">
            @php
                $legObjectives = $objective?->leg_objectives ?? [];
                $allLegs = $rotation->circuit->legs;
                $completedLegs = $rotation->rotationLegs->keyBy('circuit_leg_id');
            @endphp

            <div class="timeline">
                @foreach($allLegs as $leg)
                    @php
                        $rl = $completedLegs->get($leg->id);
                        $isDone = $rl !== null;
                        $targetMin = $legObjectives[$leg->id] ?? null;
                        $actualMin = $rl?->duration_since_previous_minutes;
                    @endphp
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $isDone ? 'done' : '' }}"></div>
                        <div class="timeline-label">{{ $leg->label }}</div>
                        @if($isDone)
                            <div class="timeline-time">{{ $rl->occurred_at->format('d/m/Y H:i:s') }}</div>
                            @if($actualMin !== null)
                                <div class="timeline-duration">
                                    +{{ intdiv($actualMin, 60) }}h{{ $actualMin % 60 }}m depuis étape précédente
                                    @if($targetMin)
                                        @php $e = $actualMin - $targetMin; @endphp
                                        <span class="{{ $e > 0 ? 'text-danger' : 'text-success' }}">
                                            (objectif : {{ intdiv($targetMin,60) }}h{{ $targetMin%60 }}m,
                                            {{ $e > 0 ? '+' : '' }}{{ intdiv($e,60) }}h{{ abs($e%60) }}m)
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="timeline-duration text-muted">Non atteint</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection