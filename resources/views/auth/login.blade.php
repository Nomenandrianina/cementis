@extends('layouts.full')
@push('page_body_class')
hold-transition login-page-modern
@endpush

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<div id="loader" class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
<div id="overlay"></div>

<div class="login-wrapper">
    <!-- Left Panel: Image / Branding -->
    <div class="left-panel">
        <div class="left-overlay"></div>
        <div class="left-content">
            <div class="brand-badge">Alpha Ciment</div>
            <h1 class="left-headline">Construisons<br>l'avenir<br>ensemble.</h1>
            <p class="left-sub">Portail de gestion interne — accès sécurisé réservé aux collaborateurs autorisés.</p>
            <div class="decorative-line"></div>
        </div>
    </div>

    <!-- Right Panel: Form -->
    <div class="right-panel">
        <div class="form-card">
            <div class="form-header">
                <img src="{{ url('images/alpha_ciment.jpg') }}" alt="Alpha Ciment" class="logo-img">
                <h2 class="form-title">Connexion</h2>
                <p class="form-subtitle">Veuillez vous connecter à votre compte</p>
            </div>

            <form method="post" action="{{ url('/login') }}" class="login-form">
                @csrf

                <div class="field-group">
                    <label class="field-label">Adresse e-mail</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="exemple@alphaciment.com" class="field-input @error('email') is-invalid @enderror" autocomplete="email">
                    </div>
                    @error('email')
                    <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">Mot de passe</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" name="password" id="passwordInput" placeholder="••••••••" class="field-input @error('password') is-invalid @enderror" autocomplete="current-password">
                        <button type="button" class="toggle-pw" onclick="togglePassword()" title="Afficher/masquer le mot de passe">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')
                    <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-login" onclick="submitForm()">
                    <span class="btn-text">Se connecter</span>
                    <span class="btn-arrow">→</span>
                </button>
            </form>

            <p class="form-footer">© {{ date('Y') }} Alpha Ciment. Tous droits réservés.</p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>

<script>
    $(document).ready(function () {
        $('#overlay').hide();
        $('#loader').hide();

        // Animate form elements on load
        setTimeout(() => {
            document.querySelectorAll('.animate-in').forEach((el, i) => {
                setTimeout(() => el.classList.add('visible'), i * 100);
            });
        }, 100);
    });

    function submitForm() {
        $('#overlay').show();
        $('#loader').show();
        return true;
    }

    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }
</script>

<style>
    /* ===== RESET & BASE ===== */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body.login-page-modern {
        font-family: 'DM Sans', sans-serif;
        background: #0f0f0f;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    /* ===== WRAPPER ===== */
    .login-wrapper {
        display: flex;
        width: 100vw;
        height: 100vh;
    }

    /* ===== LEFT PANEL ===== */
    .left-panel {
        flex: 1.1;
        position: relative;
        background: linear-gradient(145deg, #8b1a1a 0%, #6b2737 30%, #4a4a55 65%, #3a3f4a 100%);
        display: flex;
        align-items: flex-end;
        padding: 56px 52px;
        overflow: hidden;
    }

    .left-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: 
            radial-gradient(ellipse at 15% 85%, rgba(180,40,40,0.30) 0%, transparent 50%),
            radial-gradient(ellipse at 85% 15%, rgba(80,85,100,0.35) 0%, transparent 50%),
            radial-gradient(ellipse at 50% 50%, rgba(100,50,60,0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Decorative geometric elements */
    .left-panel::after {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 320px;
        height: 320px;
        border: 1.5px solid rgba(255,255,255,0.07);
        border-radius: 50%;
        pointer-events: none;
    }

    .left-overlay {
        position: absolute;
        top: 48px;
        right: 48px;
        width: 180px;
        height: 180px;
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .left-content {
        position: relative;
        z-index: 2;
    }

    .brand-badge {
        display: inline-block;
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.5);
        border: 1px solid rgba(255,255,255,0.15);
        padding: 6px 14px;
        border-radius: 100px;
        margin-bottom: 28px;
    }

    .left-headline {
        font-family: 'Playfair Display', serif;
        font-size: clamp(38px, 4.5vw, 58px);
        font-weight: 700;
        line-height: 1.12;
        color: #ffffff;
        margin-bottom: 20px;
        letter-spacing: -0.01em;
    }

    .left-sub {
        font-size: 14px;
        font-weight: 300;
        color: rgba(255,255,255,0.45);
        line-height: 1.7;
        max-width: 320px;
        margin-bottom: 36px;
    }

    .decorative-line {
        width: 48px;
        height: 2px;
        background: linear-gradient(90deg, rgba(180,80,80,0.7), rgba(100,105,120,0.5), transparent);
        border-radius: 2px;
    }

    /* ===== RIGHT PANEL ===== */
    .right-panel {
        flex: 0 0 480px;
        background: #fafaf8;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 40px;
    }

    .form-card {
        width: 100%;
        max-width: 380px;
    }

    .form-header {
        margin-bottom: 40px;
    }

    .logo-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .form-title {
        font-family: 'Playfair Display', serif;
        font-size: 30px;
        font-weight: 700;
        color: #111;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }

    .form-subtitle {
        font-size: 14px;
        color: #888;
        font-weight: 400;
    }

    /* ===== FORM FIELDS ===== */
    .login-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .field-label {
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #555;
    }

    .field-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .field-icon {
        position: absolute;
        left: 14px;
        color: #aaa;
        display: flex;
        align-items: center;
        pointer-events: none;
        transition: color 0.2s;
    }

    .field-input {
        width: 100%;
        padding: 13px 42px 13px 42px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 400;
        color: #222;
        background: #fff;
        border: 1.5px solid #e4e4e0;
        border-radius: 10px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        -webkit-appearance: none;
    }

    .field-input::placeholder {
        color: #bbb;
    }

    .field-input:focus {
        border-color: #6b2737;
        box-shadow: 0 0 0 3px rgba(107,39,55,0.12);
    }

    .field-input:focus ~ .field-icon,
    .field-wrap:focus-within .field-icon {
        color: #6b2737;
    }

    .field-input.is-invalid {
        border-color: #e53e3e;
    }

    .field-error {
        font-size: 12px;
        color: #e53e3e;
        font-weight: 400;
    }

    .toggle-pw {
        position: absolute;
        right: 14px;
        background: none;
        border: none;
        cursor: pointer;
        color: #aaa;
        display: flex;
        align-items: center;
        padding: 0;
        transition: color 0.2s;
    }

    .toggle-pw:hover {
        color: #6b2737;
    }

    /* ===== BUTTON ===== */
    .btn-login {
        margin-top: 8px;
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #8b1a1a 0%, #6b2737 50%, #4b5563 100%);
        color: #fff;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 500;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        letter-spacing: 0.02em;
    }

    .btn-login:hover {
        background: linear-gradient(135deg, #6e1414 0%, #572030 50%, #374151 100%);
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(107,39,55,0.35);
    }

    .btn-login:active {
        transform: translateY(0);
        box-shadow: none;
    }

    .btn-arrow {
        font-size: 16px;
        transition: transform 0.2s;
    }

    .btn-login:hover .btn-arrow {
        transform: translateX(3px);
    }

    /* ===== FOOTER ===== */
    .form-footer {
        margin-top: 36px;
        font-size: 12px;
        color: #bbb;
        text-align: center;
    }

    /* ===== LOADER ===== */
    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        backdrop-filter: blur(4px);
    }

    .lds-roller {
        display: none;
        position: fixed;
        width: 80px;
        height: 80px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        color: #ffffff;
    }

    .lds-roller div {
        animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        transform-origin: 40px 40px;
    }

    .lds-roller div:after {
        content: " ";
        display: block;
        position: absolute;
        width: 7.2px;
        height: 7.2px;
        border-radius: 50%;
        background: currentColor;
        margin: -3.6px 0 0 -3.6px;
    }

    .lds-roller div:nth-child(1) { animation-delay: -0.036s; }
    .lds-roller div:nth-child(1):after { top: 62.62742px; left: 62.62742px; }
    .lds-roller div:nth-child(2) { animation-delay: -0.072s; }
    .lds-roller div:nth-child(2):after { top: 67.71281px; left: 56px; }
    .lds-roller div:nth-child(3) { animation-delay: -0.108s; }
    .lds-roller div:nth-child(3):after { top: 70.90963px; left: 48.28221px; }
    .lds-roller div:nth-child(4) { animation-delay: -0.144s; }
    .lds-roller div:nth-child(4):after { top: 72px; left: 40px; }
    .lds-roller div:nth-child(5) { animation-delay: -0.18s; }
    .lds-roller div:nth-child(5):after { top: 70.90963px; left: 31.71779px; }
    .lds-roller div:nth-child(6) { animation-delay: -0.216s; }
    .lds-roller div:nth-child(6):after { top: 67.71281px; left: 24px; }
    .lds-roller div:nth-child(7) { animation-delay: -0.252s; }
    .lds-roller div:nth-child(7):after { top: 62.62742px; left: 17.37258px; }
    .lds-roller div:nth-child(8) { animation-delay: -0.288s; }
    .lds-roller div:nth-child(8):after { top: 56px; left: 12.28719px; }

    @keyframes lds-roller {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
        .login-wrapper { flex-direction: column; height: auto; min-height: 100vh; }
        .left-panel { flex: none; height: 260px; align-items: flex-start; padding: 40px 32px; }
        .left-headline { font-size: 30px; }
        .right-panel { flex: none; width: 100%; padding: 40px 24px 56px; }
    }

    @media (max-width: 480px) {
        .right-panel { padding: 32px 20px 48px; }
    }
</style>
@endsection