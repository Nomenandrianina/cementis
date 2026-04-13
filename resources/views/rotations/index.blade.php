@extends('layouts.app')
<style>
  .topbar {
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        padding: 0 24px;
        height: 52px;
        display: flex;
        align-items: center;
        gap: 16px;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .topbar-title {
        font-family: var(--head);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.04em;
        flex: 1;
    }

    .topbar-actions { display: flex; gap: 8px; align-items: center; }

    .page-content { padding: 24px; flex: 1; }

     /* ── Components ────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }
 
        .card-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
 
        .card-title {
            font-family: var(--head);
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            flex: 1;
        }
 
        .card-body { padding: 18px; }
         /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s;
            font-family: var(--body);
            white-space: nowrap;
        }
 
        .btn-primary  { background: var(--accent); color: #000; }
        .btn-primary:hover { background: #d97706; }
 
        .btn-blue     { background: var(--accent2); color: #fff; }
        .btn-blue:hover { background: #2563eb; }
 
        .btn-ghost    { background: var(--panel); color: var(--text); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--border); }
 
        .btn-danger   { background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
 
        .btn-sm { padding: 4px 10px; font-size: 11px; }
 
        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-family: var(--mono);
        }
 
        .badge-success  { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-danger   { background: rgba(239,68,68,0.15);  color: var(--danger); }
        .badge-warning  { background: rgba(245,158,11,0.15); color: var(--warning); }
        .badge-muted    { background: var(--panel); color: var(--muted); }
        .badge-blue     { background: rgba(59,130,246,0.15); color: var(--accent2); }
 
        /* Tables */
        .table-wrap { overflow-x: auto; }
 
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
 
        thead tr { background: var(--panel); }
 
        th {
            padding: 10px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
 
        td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
 
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody tr:last-child td { border-bottom: none; }
 
        /* Forms */
        .form-group { margin-bottom: 16px; }
 
        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }
 
        input[type=text], input[type=number], input[type=date],
        input[type=email], select, textarea {
            width: 100%;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text);
            padding: 8px 12px;
            font-size: 13px;
            font-family: var(--body);
            outline: none;
            transition: border-color 0.15s;
        }
 
        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
        }
 
        select option { background: var(--panel); }
 
        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
 
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--success); }
        .alert-error   { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.3);  color: var(--danger); }
 
        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
 
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }
 
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--accent);
        }
 
        .stat-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }
 
        .stat-value {
            font-family: var(--head);
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
            color: var(--accent);
        }
 
        .stat-sub {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px;
        }
 
        /* Progress bar */
        .progress {
            height: 6px;
            background: var(--panel);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }
 
        .progress-bar {
            height: 100%;
            background: var(--accent);
            border-radius: 3px;
            transition: width 0.5s ease;
        }
 
        .progress-bar.over { background: var(--danger); }
        .progress-bar.good { background: var(--success); }
 
        /* Grid helpers */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
 
        /* Timeline for rotation legs */
        .timeline { position: relative; padding-left: 28px; }
 
        .timeline::before {
            content: '';
            position: absolute;
            left: 9px; top: 8px; bottom: 8px;
            width: 2px;
            background: var(--border);
        }
 
        .timeline-item { position: relative; padding-bottom: 20px; }
        .timeline-item:last-child { padding-bottom: 0; }
 
        .timeline-dot {
            position: absolute;
            left: -22px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid var(--accent);
            background: var(--bg);
        }
 
        .timeline-dot.done { background: var(--accent); }
 
        .timeline-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
        }
 
        .timeline-time {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text);
            margin-top: 2px;
        }
 
        .timeline-duration {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }
 
        /* Mono text */
        .mono { font-family: var(--mono); }
 
        /* Utility */
        .text-success { color: var(--success); }
        .text-danger  { color: var(--danger); }
        .text-muted   { color: var(--muted); }
        .text-accent  { color: var(--accent); }
        .text-right   { text-align: right; }
        .mt-16  { margin-top: 16px; }
        .mt-24  { margin-top: 24px; }
        .mb-16  { margin-bottom: 16px; }
        .flex   { display: flex; }
        .items-center { align-items: center; }
        .gap-8  { gap: 8px; }
        .gap-16 { gap: 16px; }
        .flex-1 { flex: 1; }
 
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
 
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
</style>
@section('title', 'Rotations')
@section('page-title', 'Rotations')

@section('topbar-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/>
        </svg>
        Rapports
    </a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
{{-- Calcul de rotations --}}
<div class="card mb-16">
    <div class="card-header">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span class="card-title">Calculer les rotations</span>
    </div>
    <div class="card-body">
        <form action="{{ route('rotations.calculate') }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns: 2fr 2fr 1fr 1fr auto; gap:12px; align-items:end;">
                <div class="form-group" style="margin:0">
                    <label>Circuit</label>
                    <select name="circuit_id" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($circuits as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Camion (optionnel)</label>
                    <select name="vehicle_id">
                        <option value="">— Tous les camions —</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Année</label>
                    <input type="number" name="year" value="{{ date('Y') }}" min="2020" max="2099" required>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Mois</label>
                    <select name="month" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" @selected($m == date('n'))>
                                {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Calculer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-16">
    <div class="card-body" style="padding:14px 18px;">
        <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin:0; min-width:180px;">
                <label>Circuit</label>
                <select name="circuit_id">
                    <option value="">Tous</option>
                    @foreach($circuits as $c)
                        <option value="{{ $c->id }}" @selected(request('circuit_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0; min-width:160px;">
                <label>Camion</label>
                <select name="vehicle_id">
                    <option value="">Tous</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(request('vehicle_id') == $v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0; min-width:120px;">
                <label>Mois (YYYY-MM)</label>
                <input type="text" name="month" value="{{ request('month') }}" placeholder="{{ date('Y-m') }}">
            </div>
            <div class="form-group" style="margin:0; min-width:130px;">
                <label>Statut</label>
                <select name="status">
                    <option value="">Sélectionner un statut</option>
                    <option value="completed" @selected(request('status') === 'completed')>Complète</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Annulée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-ghost btn-sm">Filtrer</button>
            <a href="{{ route('rotations.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
        </form>
    </div>
</div>

{{-- Table des rotations --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Liste des rotations</span>
        <span class="badge badge-muted">{{ $rotations->total() }} entrée(s)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Camion</th>
                    <th>Circuit</th>
                    <th>Mois compté</th>
                    {{-- <th>Début </th>
                    <th>Fin</th> --}}
                    <th>Objectif</th>
                    <th>Effectif</th>
                    <th>Statut</th>
                    <th>Validité</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rotations as $rotation)
                    <tr>
                        <td class="mono" style="color:var(--muted)">{{ $rotation->id }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $rotation->rvehicule->name }}</div>
                            @if($rotation->rvehicule->plate_number)
                                <div style="font-size:11px;color:var(--muted);" class="mono">{{ $rotation->rvehicule->plate_number }}</div>
                            @endif
                        </td>
                        <td>{{ $rotation->circuit->name }}</td>
                        <td class="mono">{{ $rotation->counted_month ?? '—' }}</td>
                        {{-- <td class="mono">{{ $rotation->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="mono">{{ $rotation->completed_at?->format('d/m/Y H:i') ?? '—' }}</td> --}}
                        <td class="mono">
                            @if($rotation->circuit->currentObjective()->target_duration_minutes)
                                {{ intdiv($rotation->circuit->currentObjective()->target_duration_minutes, 60) }}h{{ $rotation->circuit->currentObjective()->target_duration_minutes % 60 }}m
                            @else
                                —
                            @endif
                        </td>
                        <td class="mono">
                            @if($rotation->duration_minutes)
                                {{ intdiv($rotation->duration_minutes, 60) }}h{{ $rotation->duration_minutes % 60 }}m
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @switch($rotation->status)
                                @case('completed')
                                    <span class="badge badge-success">Complète</span>
                                    @break
                                @case('in_progress')
                                    <span class="badge badge-blue">En cours</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge badge-danger">Annulée</span>
                                    @break
                                @default
                                    <span class="badge badge-muted">{{ $rotation->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($rotation->is_valid)
                                <span class="badge badge-success">✓ Valide</span>
                            @else
                                <span class="badge badge-danger">✗ Invalide</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('rotations.show', $rotation) }}" class="btn btn-ghost btn-sm">Détail</a>
                                <form action="{{ route('rotations.destroy', $rotation) }}" method="POST" onsubmit="return confirm('Supprimer cette rotation ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;color:var(--muted);padding:32px;">
                            Aucune rotation trouvée. Lancez un calcul ci-dessus.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rotations->hasPages())
        <div style="padding:14px 18px;border-top:1px solid var(--border);">
            {{ $rotations->links() }}
        </div>
    @endif
</div>
@endsection