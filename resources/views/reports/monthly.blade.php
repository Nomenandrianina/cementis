@extends('layouts.app')

@section('title', 'Rapport ' . $report['month_label'])
@section('page-title', 'Rapport – ' . $report['month_label'])

@section('topbar-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">← Rapports</a>
    
    <a href="{{ route('reports.export_csv', [
        'circuit_id' => $circuit->id,
        'year'       => $report['year'],
        'month'      => $report['month'],
    ]) }}" class="btn btn-ghost btn-sm">↓ CSV</a>

    <a href="{{ route('reports.export_excel', [
        'circuit_id' => $circuit->id,
        'year'       => $report['year'],
        'month'      => $report['month'],
    ]) }}" class="btn btn-primary btn-sm">↓ Excel</a>
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
    @if($vr['rotation_count'] > 0)
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
                    ['Durée moy. rotation', $vr['avg_duration']],
                    ['Objectif durée', $vr['target_duration']],
                    ['Durée totale', $vr['total_duration']],
                ] as [$label, $val])
                <div style="flex:1;padding:12px 18px;border-right:1px solid var(--border);">
                    <div style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px;">{{ $label }}</div>
                    <div style="font-family:var(--head);font-size:20px;font-weight:700;">{{ $val }}</div>
                </div>
                @endforeach
            </div>
            
            {{-- Tableau des rotations --}}
            @if($vr['rotations']->count())
            @php
                $fmt = fn(?int $s) => $s === null
                    ? ''
                    : intdiv($s, 3600) . 'h '
                        . str_pad(intdiv($s % 3600, 60), 2, '0', STR_PAD_LEFT) . 'm '
                        . str_pad($s % 60, 2, '0', STR_PAD_LEFT) . 's';
            @endphp
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
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
                                <td class="mono">{{ $rot['started_at'] }}</td>
                                <td class="mono">{{ $rot['completed_at'] }}</td>
                                <td class="mono" style="font-weight:600;">{{ $rot['duration_label'] }}</td>
                                <td class="mono" style="color:var(--muted);">{{ $rot['target_label'] }}</td>
                                <td>
                                    @if($rot['vs_target'] !== null)
                                        <span class="{{ $rot['vs_target'] > 0 ? 'text-danger' : 'text-success' }}" style="font-family:var(--mono);font-size:12px;">
                                            {{ $rot['vs_target'] > 0 ? '+' : '' }}{{$fmt($rot['vs_target'])}}
                                        </span>
                                    @else
                                        <span style="color:var(--muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    <button onclick="toggleDetail('rot-{{ $rot['id'] }}')" class="btn btn-ghost btn-sm">▾ Étapes</button>
                                </td>
                            </tr>
                            {{-- Détail hiérarchique --}}
                            <tr id="rot-{{ $rot['id'] }}" style="display:none;">
                                <td colspan="7" style="padding:0;">
                                    <div style="background:var(--cream);padding:14px 20px;border-top:2px solid var(--bordeaux);">
                                        @foreach($rot['blocks'] as $block)
                                        
                                            @if($block['type'] === 'checkpoint')
                                                {{-- Checkpoint --}}
                                                <div style="display:flex;align-items:center;gap:10px;
                                                            padding:5px 8px;margin:2px 0;border-radius:4px;">
                                                    <div style="width:6px;height:6px;border-radius:50%;flex-shrink:0;
                                                                background:{{ $block['is_done'] ? 'var(--success)' : 'var(--cream-dd)' }};"></div>
                                                    <span style="font-size:12px;font-weight:600;
                                                                color:{{ $block['is_done'] ? 'var(--ink)' : 'var(--muted)' }};">
                                                        {{ $block['label'] }}
                                                    </span>
                                                    <span style="margin-left:auto;font-family:var(--mono);font-size:11px;color:var(--muted);">
                                                        {{ $block['occurred_at'] ?? '—' }}
                                                    </span>
                                                </div>

                                            @elseif($block['type'] === 'zone')
                                            {{-- Zone --}}
                                            @php
                                                $zE = $block['ecart'];
                                                $zD = $block['is_done'];
                                                $zC = !$zD ? 'var(--cream-dd)'
                                                    : ($zE === null ? 'var(--bordeaux)'
                                                    : ($zE > 0 ? 'var(--danger)' : 'var(--success)'));
                                                $zB = !$zD ? '#fff'
                                                    : ($zE === null ? 'rgba(139,26,26,0.03)'
                                                    : ($zE > 0 ? 'rgba(192,39,45,0.04)' : 'rgba(45,122,74,0.04)'));
                                            @endphp

                                            <div style="border:1.5px solid {{ $zC }};border-radius:8px;
                                                        background:{{ $zB }};margin:7px 0;overflow:hidden;">

                                                {{-- En-tête --}}
                                                <div style="display:flex;align-items:center;gap:10px;padding:9px 14px;">
                                                    <div style="width:9px;height:9px;border-radius:50%;
                                                                background:{{ $zC }};flex-shrink:0;"></div>
                                                    <div style="flex:1;min-width:0;">
                                                        <div style="font-size:12px;font-weight:700;color:var(--ink);">
                                                            {{ $block['label'] }}
                                                        </div>
                                                        <div style="display:flex;gap:10px;margin-top:2px;">
                                                            @if($block['enter_at'])
                                                                <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">
                                                                    ↓ {{ $block['enter_at'] }}
                                                                </span>
                                                            @endif
                                                            @if($block['leave_at'])
                                                                <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">
                                                                    ↑ {{ $block['leave_at'] }}
                                                                </span>
                                                            @endif
                                                            @if(!$zD)
                                                                <span style="font-size:10px;color:var(--muted);font-style:italic;">
                                                                    Non atteint
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div style="text-align:right;flex-shrink:0;min-width:75px;">
                                                        <div style="font-family:var(--mono);font-size:14px;font-weight:800;
                                                                    color:{{ $zC }};line-height:1;">
                                                            {{ $fmt($block['actual_sec']) }}
                                                        </div>
                                                        @if($block['target_sec'] !== null)
                                                            <div style="font-size:10px;font-weight:600;margin-top:2px;
                                                                        color:{{ $zE > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                                {{ $zE > 0 ? '+' : '' }}{{ $fmt($zE) }}
                                                            </div>
                                                            <div style="font-size:9px;color:var(--muted);">
                                                                obj: {{ $fmt($block['target_sec']) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Sous-zones --}}
                                                @if(!empty($block['children']))
                                                    <div style="border-top:1px solid {{ $zC }}33;
                                                                padding:8px 14px 10px;background:rgba(255,255,255,0.5);">
                                                        <div style="font-size:9px;font-weight:700;letter-spacing:0.1em;
                                                                    text-transform:uppercase;color:var(--muted);margin-bottom:5px;">
                                                            Sous-zones
                                                        </div>
                                                        @foreach($block['children'] as $child)
                                                            @php
                                                                $cE = $child['ecart'];
                                                                $cD = $child['is_done'];
                                                                $cC = !$cD ? 'var(--cream-dd)'
                                                                    : ($cE === null ? 'var(--bordeaux)'
                                                                    : ($cE > 0 ? 'var(--danger)' : 'var(--success)'));
                                                            @endphp
                                                            <div style="display:flex;align-items:center;gap:10px;
                                                                        border:1px solid {{ $cC }};border-radius:6px;
                                                                        padding:7px 12px;margin-bottom:4px;
                                                                        background:{{ !$cD ? '#fff' : ($cE > 0 ? 'rgba(192,39,45,0.04)' : 'rgba(45,122,74,0.04)') }};">
                                                                <div style="display:flex;flex-direction:column;
                                                                            align-items:center;gap:1px;flex-shrink:0;">
                                                                    <div style="width:1px;height:5px;background:var(--cream-dd);"></div>
                                                                    <div style="width:6px;height:6px;border-radius:50%;
                                                                                background:{{ $cC }};"></div>
                                                                    <div style="width:1px;height:5px;background:var(--cream-dd);"></div>
                                                                </div>
                                                                <div style="flex:1;min-width:0;">
                                                                    <div style="font-size:11px;font-weight:700;
                                                                                color:{{ $cD ? 'var(--ink)' : 'var(--muted)' }};">
                                                                        {{ $child['label'] }}
                                                                    </div>
                                                                    <div style="display:flex;gap:8px;margin-top:1px;">
                                                                        @if($child['enter_at'])
                                                                            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">
                                                                                ↓ {{ $child['enter_at'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if($child['leave_at'])
                                                                            <span style="font-family:var(--mono);font-size:10px;color:var(--muted);">
                                                                                ↑ {{ $child['leave_at'] }}
                                                                            </span>
                                                                        @endif
                                                                        @if(!$cD)
                                                                            <span style="font-size:10px;color:var(--muted);font-style:italic;">
                                                                                Non atteint
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div style="text-align:right;flex-shrink:0;min-width:65px;">
                                                                    <div style="font-family:var(--mono);font-size:13px;font-weight:700;
                                                                                color:{{ $cC }};line-height:1;">
                                                                        {{ $fmt($child['actual_sec']) }}
                                                                    </div>
                                                                    @if($child['target_sec'] !== null)
                                                                        <div style="font-size:10px;font-weight:600;margin-top:1px;
                                                                                    color:{{ $cE > 0 ? 'var(--danger)' : 'var(--success)' }};">
                                                                            {{ $cE > 0 ? '+' : '' }}{{ $fmt($cE) }}
                                                                        </div>
                                                                        <div style="font-size:9px;color:var(--muted);">
                                                                            obj: {{ $fmt($child['target_sec']) }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @endif

                                        @endforeach
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
    @endif
@endforeach

@endsection

@push('scripts')

@endpush