@extends('layouts.app')

@section('title', 'Rapport ' . $report['month_label'])
@section('page-title', 'Rapport – ' . $report['month_label'])

@section('topbar-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Rapports</a>
    <a href="{{ route('reports.export_csv', ['circuit_id' => $circuit->id, 'year' => $report['year'], 'month' => $report['month']]) }}"
       class="btn btn-blue btn-sm">
        ↓ Export CSV
    </a>
@endsection
<script>
function toggleDetail(id) {
    const row = document.getElementById(id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
{{-- Résumé global --}}
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">Circuit</div>
        <div style="font-family:var(--head);font-size:18px;font-weight:700;color:var(--text);">{{ $circuit->name }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rotations réalisées</div>
        <div class="stat-value">{{ $report['total_rotations'] }}</div>
        <div class="stat-sub">
            @if($report['target_rotations'])
                Objectif : {{ $report['target_rotations'] }}
            @endif
        </div>
    </div>
    @if($report['achievement_rate'] !== null)
    <div class="stat-card" style="--stat-color:{{ $report['achievement_rate'] >= 100 ? 'var(--success)' : 'var(--danger)' }}">
        <div class="stat-label">Taux de réalisation</div>
        <div class="stat-value" style="color:{{ $report['achievement_rate'] >= 100 ? 'var(--success)' : 'var(--danger)' }};">
            {{ $report['achievement_rate'] }}%
        </div>
        <div class="progress" style="margin-top:12px;">
            <div class="progress-bar {{ $report['achievement_rate'] >= 100 ? 'good' : '' }}"
                 style="width:{{ min($report['achievement_rate'], 100) }}%;"></div>
        </div>
    </div>
    @endif
    <div class="stat-card">
        <div class="stat-label">Camions</div>
        <div class="stat-value">{{ $report['vehicle_reports']->count() }}</div>
    </div>
</div>

{{-- Rapport par véhicule --}}
@foreach($report['vehicle_reports'] as $vr)
<div class="card mb-16">
    <div class="card-header">
        <div style="flex:1;">
            <div style="font-family:var(--head);font-size:17px;font-weight:700;">{{ $vr['vehicle']->name }}</div>
            @if($vr['vehicle']->plate_number)
                <div class="mono" style="font-size:11px;color:var(--muted);">{{ $vr['vehicle']->plate_number }}</div>
            @endif
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            @if($vr['target_rotations'])
                @php $rate = round($vr['rotation_count'] / $vr['target_rotations'] * 100); @endphp
                <span class="badge {{ $vr['rotation_count'] >= $vr['target_rotations'] ? 'badge-success' : 'badge-danger' }}">
                    {{ $vr['rotation_count'] }} / {{ $vr['target_rotations'] }} rotations
                </span>
            @else
                <span class="badge badge-muted">{{ $vr['rotation_count'] }} rotation(s)</span>
            @endif
            @if($vr['cancelled_count'] > 0)
                <span class="badge badge-warning">{{ $vr['cancelled_count'] }} annulée(s)</span>
            @endif
        </div>
    </div>

    {{-- Résumé durées --}}
    <div style="display:flex;gap:0;border-bottom:1px solid var(--border);">
        @foreach([
            ['Durée moy. rotation', $vr['avg_duration'] ? intdiv($vr['avg_duration'],60).'h'.($vr['avg_duration']%60).'m' : '—'],
            ['Objectif durée', $vr['target_duration'] ? intdiv($vr['target_duration'],60).'h'.($vr['target_duration']%60).'m' : '—'],
            ['Durée totale', $vr['total_duration'] ? intdiv($vr['total_duration'],60).'h'.($vr['total_duration']%60).'m' : '—'],
        ] as [$label, $val])
        <div style="flex:1;padding:12px 18px;border-right:1px solid var(--border);">
            <div style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px;">{{ $label }}</div>
            <div style="font-family:var(--head);font-size:20px;font-weight:700;">{{ $val }}</div>
        </div>
        @endforeach
    </div>

    {{-- Tableau des rotations --}}
    @if($vr['rotations']->count())
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Début (T1)</th>
                    <th>Fin (T5)</th>
                    <th>Durée réelle</th>
                    <th>Objectif</th>
                    <th>Écart</th>
                    <th>Détail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vr['rotations'] as $idx => $rot)
                    <tr>
                        <td class="mono" style="color:var(--muted);">{{ $idx + 1 }}</td>
                        <td class="mono">{{ $rot['started_at'] }}</td>
                        <td class="mono">{{ $rot['completed_at'] }}</td>
                        <td class="mono" style="font-weight:600;">{{ $rot['duration_label'] }}</td>
                        <td class="mono" style="color:var(--muted);">{{ $rot['target_label'] }}</td>
                        <td>
                            @if($rot['vs_target'] !== null)
                                <span class="{{ $rot['vs_target'] > 0 ? 'text-danger' : 'text-success' }}" style="font-family:var(--mono);font-size:12px;">
                                    {{ $rot['vs_target'] > 0 ? '+' : '' }}{{ intdiv($rot['vs_target'], 60) }}h{{ abs($rot['vs_target'] % 60) }}m
                                </span>
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <button onclick="toggleDetail('rot-{{ $rot['id'] }}')" class="btn btn-ghost btn-sm">▾ Étapes</button>
                        </td>
                    </tr>
                    {{-- Détail des étapes --}}
                    <tr id="rot-{{ $rot['id'] }}" style="display:none;">
                        <td colspan="7" style="padding:0;">
                            <div style="background:var(--panel);padding:16px 20px;">
                                <table style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th style="background:transparent;">Étape</th>
                                            <th style="background:transparent;">Date/Heure</th>
                                            <th style="background:transparent;">Durée depuis préc.</th>
                                            <th style="background:transparent;">Objectif étape</th>
                                            <th style="background:transparent;">Écart</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rot['legs'] as $leg)
                                            <tr>
                                                <td style="font-size:12px;">{{ $leg['label'] }}</td>
                                                <td class="mono" style="font-size:12px;">{{ $leg['occurred_at'] }}</td>
                                                <td class="mono" style="font-size:12px;">
                                                    @if($leg['duration_since_prev'] !== null)
                                                        {{ intdiv($leg['duration_since_prev'],60) }}h{{ $leg['duration_since_prev']%60 }}m
                                                    @else —
                                                    @endif
                                                </td>
                                                <td class="mono" style="font-size:12px;color:var(--muted);">
                                                    @if($leg['target_duration'])
                                                        {{ intdiv($leg['target_duration'],60) }}h{{ $leg['target_duration']%60 }}m
                                                    @else —
                                                    @endif
                                                </td>
                                                <td style="font-size:12px;">
                                                    @if($leg['vs_target'] !== null)
                                                        <span class="{{ $leg['vs_target'] > 0 ? 'text-danger' : 'text-success' }}" class="mono">
                                                            {{ $leg['vs_target'] > 0 ? '+' : '' }}{{ $leg['vs_target'] }}min
                                                        </span>
                                                    @else —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div style="padding:24px;text-align:center;color:var(--muted);font-size:13px;">
            Aucune rotation valide pour ce véhicule sur ce mois.
        </div>
    @endif
</div>
@endforeach

@endsection

@push('scripts')

@endpush