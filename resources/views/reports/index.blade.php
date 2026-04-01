@extends('layouts.app')

@section('title', 'Rapports')
@section('page-title', 'Rapports mensuels')

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div style="max-width:700px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Générer un rapport</span></div>
        <div class="card-body">
            <form action="{{ route('reports.monthly') }}" method="GET">
                <div class="grid-2" style="gap:16px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Circuit</label>
                        <select name="circuit_id" required>
                            <option value="">— Sélectionner un circuit —</option>
                            @foreach($circuits as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Année</label>
                        <input type="number" name="year" value="{{ date('Y') }}" min="2020" max="2099" required>
                    </div>
                    <div class="form-group">
                        <label>Mois</label>
                        <select name="month" required>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" @selected($m == date('n'))>
                                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Générer le rapport</button>
            </form>
        </div>
    </div>
</div>
@endsection