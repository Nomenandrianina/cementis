@extends('layouts.app')

@section('title', 'Circuits')
@section('page-title', 'Circuits')

@section('topbar-actions')
    <a href="{{ route('circuits.create') }}" class="btn btn-primary btn-sm">+ Nouveau circuit</a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Étapes</th>
                    <th>Camions</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($circuits as $circuit)
                    <tr>
                        <td style="font-weight:600;">{{ $circuit->name }}</td>
                        <td class="mono">{{ $circuit->code }}</td>
                        <td>{{ $circuit->legs_count }}</td>
                        <td>{{ $circuit->vehicles->count() }}</td>
                        <td>
                            @if($circuit->active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-muted">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('circuits.edit', $circuit) }}" class="btn btn-ghost btn-sm">Configurer</a>
                                <a href="{{ route('circuits.objectives.index', $circuit) }}" class="btn btn-blue btn-sm">Objectifs</a>
                                <form action="{{ route('circuits.destroy', $circuit) }}" method="POST" onsubmit="return confirm('Supprimer ce circuit ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">✕</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);padding:32px;">
                            Aucun circuit. <a href="{{ route('circuits.create') }}" style="color:var(--accent);">Créez-en un</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection