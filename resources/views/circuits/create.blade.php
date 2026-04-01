@extends('layouts.app')

@section('title', 'Nouveau circuit')
@section('page-title', 'Nouveau circuit')

@section('topbar-actions')
    <a href="{{ route('circuits.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('css/rotation.css') }}">
<div style="max-width:600px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Créer un circuit</span></div>
        <div class="card-body">
            <form action="{{ route('circuits.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nom du circuit</label>
                    <input type="text" name="name" required placeholder="Ex: Tamatave – Tanà – Tamatave">
                </div>
                <div class="form-group">
                    <label>Code unique</label>
                    <input type="text" name="code" required placeholder="Ex: TAM-TAN-TAM">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Description du circuit…"></textarea>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">Créer le circuit</button>
                    <a href="{{ route('circuits.index') }}" class="btn btn-ghost">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection