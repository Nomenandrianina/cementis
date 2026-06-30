@extends('layouts.app')

@section('title', 'Rapports')
{{-- @section('page-title', 'Rapports mensuels') --}}

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@600;700&display=swap');

    .ac-wrap {
        font-family: 'Barlow', sans-serif;
        padding: 2rem 0;
        max-width: 900px;
        margin: 0 auto;
    }

    .ac-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 1.75rem;
    }

    .ac-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #7c1d1d;
        margin-bottom: 4px;
        margin-top: 0;
    }

    .ac-heading {
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        line-height: 1.1;
        letter-spacing: 0.01em;
    }

    .ac-heading span {
        color: #7c1d1d;
    }

    .ac-badge {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: #FCEBEB;
        color: #A32D2D;
        border: 0.5px solid #F09595;
        border-radius: 4px;
        padding: 4px 10px;
        margin-top: 4px;
        display: inline-block;
        white-space: nowrap;
    }

    .ac-card {
        background: #ffffff;
        border: 0.5px solid #e0e0e0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    .ac-card-top {
        background: #7c1d1d;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ac-card-top svg {
        width: 20px;
        height: 20px;
        color: #fff;
        flex-shrink: 0;
    }

    .ac-card-top-text {
        font-size: 13px;
        font-weight: 500;
        color: #fff;
        letter-spacing: 0.02em;
    }

    .ac-card-body {
        padding: 1.5rem;
    }

    .ac-field {
        margin-bottom: 1.25rem;
    }

    .ac-label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #666;
        margin-bottom: 6px;
    }

    .ac-select,
    .ac-input {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        border: 0.5px solid #ccc;
        border-radius: 8px;
        background: #f8f8f8;
        color: #1a1a1a;
        font-size: 15px;
        font-family: 'Barlow', sans-serif;
        font-weight: 400;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
    }

    .ac-select-wrap {
        position: relative;
    }

    .ac-select-wrap::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #999;
        pointer-events: none;
    }

    .ac-select:focus,
    .ac-input:focus {
        border-color: #7c1d1d;
        box-shadow: 0 0 0 3px rgba(124, 29, 29, 0.12);
        background: #ffffff;
    }

    .ac-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .ac-divider {
        height: 0.5px;
        background: #e8e8e8;
        margin: 1.25rem 0;
    }

    .ac-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        height: 50px;
        background: #7c1d1d;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
        text-decoration: none;
    }

    .ac-btn:hover {
        background: #A32D2D;
    }

    .ac-btn:active {
        transform: scale(0.99);
    }

    .ac-info-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-top: 12px;
    }

    .ac-info-chip {
        font-size: 11px;
        font-weight: 500;
        color: #999;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .ac-info-chip::before {
        content: '';
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #bbb;
        display: inline-block;
    }
</style>

<div class="ac-wrap">

    <div class="ac-header">
        <div>
            <p class="ac-eyebrow">Alpha Ciment</p>
            <h2 class="ac-heading">Rapport <span>de rotation</span></h2>
        </div>
        <span class="ac-badge">Gestion &amp; Suivi</span>
    </div>

    <div class="ac-card">

        <div class="ac-card-top">
            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 5h12M4 10h12M4 15h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="ac-card-top-text">Paramètres du rapport</span>
        </div>

        <div class="ac-card-body">

            <form action="{{ route('reports.monthly') }}" method="GET">

                {{-- Circuit --}}
                <div class="ac-field">
                    <label class="ac-label" for="circuit_id">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M4 6h4M6 4v4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                        </svg>
                        Circuit
                    </label>
                    <div class="ac-select-wrap">
                        <select name="circuit_id" id="circuit_id" class="ac-select" required>
                            <option value="">— Sélectionner un circuit —</option>
                            @foreach($circuits as $c)
                                <option value="{{ $c->id }}" {{ old('circuit_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Année & Mois --}}
                <div class="ac-row">

                    <div class="ac-field">
                        <label class="ac-label" for="year">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <rect x="1.5" y="2.5" width="9" height="8" rx="1" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M1.5 5.5h9M4 1.5v2M8 1.5v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                            Année
                        </label>
                        <input
                            type="number"
                            name="year"
                            id="year"
                            class="ac-input"
                            value="{{ old('year', date('Y')) }}"
                            min="2020"
                            max="2099"
                            required
                        >
                    </div>

                    <div class="ac-field">
                        <label class="ac-label" for="month">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                <circle cx="6" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M6 4.5v2.5l1.5 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Mois
                        </label>
                        <div class="ac-select-wrap">
                            <select name="month" id="month" class="ac-select" required>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $m == old('month', date('n')) ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="ac-divider"></div>

                <button type="submit" class="ac-btn">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M3 13.5h12M9 3v8m0 0-3-3m3 3 3-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Générer le rapport
                </button>

            </form>

            <div class="ac-info-row">
                <span class="ac-info-chip">Excel</span>
                <span class="ac-info-chip">CSV</span>
            </div>

        </div>
    </div>
</div>

@endsection