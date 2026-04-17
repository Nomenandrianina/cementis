<aside class="main-sidebar elevation-4" id="alpha-sidebar">
    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="brand-link alpha-brand-link">
        <div class="alpha-logo-wrap">
            <img src="{{url('images/alpha_ciment.jpg')}}"
                 alt="{{ config('app.name') }} Logo"
                 class="alpha-logo-img">
            <div class="alpha-logo-glow"></div>
        </div>
        <div class="alpha-brand-text">
            <span class="alpha-brand-name">{{ config('app.name') }}</span>
            <span class="alpha-brand-tagline">Portail de gestion</span>
        </div>
    </a>

    <!-- Divider -->
    <div class="alpha-divider"></div>

    <!-- Sidebar nav -->
    <div class="sidebar alpha-sidebar-body">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column alpha-nav" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>

        <!-- Footer inside scrollable area, at bottom of nav -->
    </div>
</aside>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');

    :root {
        --ac-red:       #6b7280;
        --ac-red-line:  rgba(160,160,165,0.30);
        --ac-red-glow:  rgba(180,180,185,0.30);
        --ac-white:     #ffffff;
        --ac-white-60:  rgba(255,255,255,0.65);
        --ac-white-30:  rgba(255,255,255,0.35);
        --ac-white-10:  rgba(255,255,255,0.09);
    }

    /* ===== SIDEBAR BASE ===== */
    #alpha-sidebar {
        font-family: 'DM Sans', sans-serif;
        background:
            radial-gradient(ellipse at 0% 0%,    rgba(180,180,185,0.20) 0%, transparent 55%),
            radial-gradient(ellipse at 100% 100%, rgba(30,30,35,0.50)   0%, transparent 50%),
            linear-gradient(175deg, #6b7280 0%, #4b5563 40%, #2d3340 100%);
        border-right: none !important;
        box-shadow: 4px 0 32px rgba(0,0,0,0.30) !important;
    }

    /* Texture noise */
    #alpha-sidebar .sidebar::before {
        content: '';
        position: fixed;
        top: 0; left: 0;
        width: 250px;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
        background-size: 200px 200px;
        pointer-events: none;
        z-index: 0;
    }

    /* ===== BRAND LINK ===== */
    .alpha-brand-link {
        display: flex !important;
        align-items: center;
        gap: 13px;
        padding: 18px 18px 14px !important;
        border-bottom: none !important;
        text-decoration: none;
        transition: opacity 0.2s;
    }

    .alpha-brand-link:hover { opacity: 0.85; text-decoration: none; }

    .alpha-logo-wrap { position: relative; flex-shrink: 0; }

    .alpha-logo-img {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        object-fit: cover;
        display: block;
        border: 1.5px solid rgba(255,255,255,0.2);
        box-shadow: 0 2px 12px rgba(0,0,0,0.3);
    }

    .alpha-logo-glow {
        position: absolute;
        inset: -4px;
        border-radius: 15px;
        background: var(--ac-red-glow);
        filter: blur(8px);
        opacity: 0;
        transition: opacity 0.3s;
        z-index: -1;
    }

    .alpha-brand-link:hover .alpha-logo-glow { opacity: 1; }

    .alpha-brand-text { display: flex; flex-direction: column; gap: 2px; }

    .alpha-brand-name {
        font-size: 14.5px;
        font-weight: 600;
        color: var(--ac-white);
        letter-spacing: 0.01em;
        line-height: 1;
    }

    .alpha-brand-tagline {
        font-size: 9.5px;
        font-weight: 400;
        color: var(--ac-white-60);
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    /* ===== DIVIDER ===== */
    .alpha-divider {
        height: 1px;
        margin: 0 14px 10px;
        background: linear-gradient(90deg, transparent, var(--ac-red-line), transparent);
    }

    /* ===== SIDEBAR BODY ===== */
    .alpha-sidebar-body { position: relative; z-index: 1; }

    /* ===== NAV ITEMS ===== */
    .alpha-nav .nav-item > .nav-link {
        font-family: 'DM Sans', sans-serif !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        color: var(--ac-white-60) !important;
        border-radius: 9px !important;
        margin: 2px 8px !important;
        padding: 9px 13px !important;
        transition: background 0.18s, color 0.18s, padding-left 0.2s !important;
        position: relative;
    }

    .alpha-nav .nav-item > .nav-link:hover {
        background: var(--ac-white-10) !important;
        color: var(--ac-white) !important;
        padding-left: 17px !important;
    }

    .alpha-nav .nav-item > .nav-link.active,
    .alpha-nav .nav-item.menu-open > .nav-link {
        background: rgba(255,255,255,0.14) !important;
        color: var(--ac-white) !important;
        font-weight: 500 !important;
    }

    /* Barre verticale active */
    .alpha-nav .nav-item > .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0; top: 20%;
        height: 60%; width: 3px;
        background: rgba(255,255,255,0.8);
        border-radius: 0 3px 3px 0;
    }

    /* Icons */
    .alpha-nav .nav-link .nav-icon {
        color: var(--ac-white-60) !important;
        font-size: 14px !important;
        margin-right: 9px !important;
        width: 18px;
        text-align: center;
        transition: color 0.18s;
    }

    .alpha-nav .nav-link:hover .nav-icon,
    .alpha-nav .nav-link.active .nav-icon {
        color: var(--ac-white) !important;
    }

    /* Nav headers */
    .alpha-nav .nav-header {
        font-family: 'DM Sans', sans-serif !important;
        font-size: 9px !important;
        font-weight: 600 !important;
        letter-spacing: 0.16em !important;
        color: var(--ac-white-30) !important;
        padding: 14px 22px 5px !important;
        text-transform: uppercase !important;
    }

    /* Sous-menus */
    .alpha-nav .nav-treeview { padding-left: 6px; }
    .alpha-nav .nav-treeview .nav-link {
        font-size: 12px !important;
        padding: 7px 13px 7px 26px !important;
    }

    /* Flèche */
    .alpha-nav .nav-link .right {
        color: var(--ac-white-30) !important;
        font-size: 10px !important;
        transition: color 0.2s;
    }
    .alpha-nav .nav-item.menu-open > .nav-link .right {
        color: var(--ac-white-60) !important;
    }

    /* ===== SIDEBAR FOOTER ===== */
    .alpha-sidebar-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 18px;
        margin-top: 16px;
        border-top: 1px solid rgba(255,255,255,0.07);
    }

    .alpha-footer-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 6px rgba(74,222,128,0.6);
        flex-shrink: 0;
        animation: pulse-dot 2.5s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.35; }
    }

    .alpha-footer-text {
        font-size: 10px;
        color: var(--ac-white-30);
        letter-spacing: 0.04em;
    }

    /* ===== SCROLLBAR ===== */
    #alpha-sidebar .sidebar::-webkit-scrollbar { width: 3px; }
    #alpha-sidebar .sidebar::-webkit-scrollbar-track { background: transparent; }
    #alpha-sidebar .sidebar::-webkit-scrollbar-thumb {
        background: var(--ac-red-line);
        border-radius: 10px;
    }

    /* ===== CORRECTION LAYOUT ADMINLTE ===== */
    /* S'assure que content-wrapper et footer restent bien alignés */
    .content-wrapper,
    .main-footer {
        margin-left: 250px !important;
    }

    /* Quand sidebar est réduit (sidebar-collapse) */
    .sidebar-collapse .content-wrapper,
    .sidebar-collapse .main-footer {
        margin-left: 0 !important;
    }

    /* Quand sidebar-mini est actif et survolé */
    .sidebar-mini.sidebar-collapse #alpha-sidebar:hover ~ .content-wrapper {
        margin-left: 250px !important;
    }
</style>