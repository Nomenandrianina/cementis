<aside class="main-sidebar elevation-4" id="alpha-sidebar">

    <!-- Fond gradient derrière le glass -->
    <div class="alpha-sidebar-bg"></div>

    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="brand-link alpha-brand-link">
        <div class="alpha-logo-wrap">
            <img src="{{url('images/alpha_ciment.jpg')}}"
                 alt="{{ config('app.name') }} Logo"
                 class="alpha-logo-img">
        </div>
        <div class="alpha-brand-text">
            <span class="alpha-brand-name">{{ config('app.name') }}</span>
            <span class="alpha-brand-tagline">Portail de gestion</span>
        </div>
    </a>


    <div class="alpha-divider"></div>

    <!-- Sidebar nav -->
    <div class="sidebar alpha-sidebar-body">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column alpha-nav"
                data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>

    <!-- Footer -->
    <div class="alpha-sidebar-footer">
        <div class="alpha-footer-dot"></div>
        <span class="alpha-footer-text">Système opérationnel</span>
    </div>
</aside>

<style>
    @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600&family=Sora:wght@300;400;500;600&display=swap');

    :root {
        /* iOS 26 Liquid Glass tokens */
        --ios-glass-bg:       rgba(255, 255, 255, 0.10);
        --ios-glass-bg-h:     rgba(255, 255, 255, 0.16);
        --ios-glass-active:   rgba(255, 255, 255, 0.20);
        --ios-border:         rgba(255, 255, 255, 0.18);
        --ios-border-h:       rgba(255, 255, 255, 0.32);
        --ios-specular:       rgba(255, 255, 255, 0.55);
        --ios-ink:            rgba(255, 255, 255, 0.92);
        --ios-ink-60:         rgba(255, 255, 255, 0.55);
        --ios-ink-30:         rgba(255, 255, 255, 0.30);
        --ios-ink-15:         rgba(255, 255, 255, 0.12);
        --ios-accent:         rgba(255, 255, 255, 0.85);
        --ios-separator:      rgba(255, 255, 255, 0.10);
    }

    /* ===== SIDEBAR BASE — iOS 26 Liquid Glass ===== */
    #alpha-sidebar {
        font-family: 'Sora', -apple-system, BlinkMacSystemFont, sans-serif;

        /* Liquid glass: translucide blanc sur fond coloré */
        background: var(--ios-glass-bg) !important;
        backdrop-filter: blur(40px) saturate(180%) brightness(1.05) !important;
        -webkit-backdrop-filter: blur(40px) saturate(180%) brightness(1.05) !important;

        /* Bordure spéculaire iOS */
        border-right: 1px solid var(--ios-border) !important;
        border-top: none !important;

        /* Reflet en haut à gauche */
        box-shadow:
            inset 1px 1px 0 var(--ios-specular),
            inset -1px 0 0 rgba(255,255,255,0.04),
            4px 0 48px rgba(0,0,0,0.35) !important;

        display: flex;
        flex-direction: column;

        width: 260px ;
        overflow: hidden;
        transition: width 0.3s ease-in-out;
    }

    .sidebar-collapse #alpha-sidebar {
        width: 4.2rem !important; 
    }

    .sidebar-collapse #alpha-sidebar:hover {
        width: 260px !important;
    }

    .sidebar-collapse .alpha-brand-text,
    .sidebar-collapse .alpha-user-info,
    .sidebar-collapse .alpha-sidebar-footer .alpha-footer-text,
    .sidebar-collapse .alpha-nav .nav-link span:not(.nav-icon) {
        display: none !important;
    }

    .sidebar-collapse #alpha-sidebar:hover .alpha-brand-text,
    .sidebar-collapse #alpha-sidebar:hover .alpha-user-info,
    .sidebar-collapse #alpha-sidebar:hover .alpha-sidebar-footer .alpha-footer-text,
    .sidebar-collapse #alpha-sidebar:hover .alpha-nav .nav-link span:not(.nav-icon),
    .sidebar-collapse #alpha-sidebar:hover .alpha-nav .nav-link .right {
        display: flex !important;
    }

    

    /* Gradient riche derrière le glass — positionné DANS la sidebar uniquement */
    #alpha-sidebar .alpha-sidebar-bg {
        position: fixed;
        top: 0; left: 0;
        /* width: 260px; */
        width: 100%;
        height: 100%;
        background: radial-gradient(ellipse 200px 250px at 15% 10%, rgb(51 71 101 / 75%) 0%, #0000003b 65%), 
                    radial-gradient(ellipse 180px 220px at 88% 85%, rgb(80 86 95 / 90%) 0%, #00000000 65%), 
                    radial-gradient(ellipse 130px 150px at 50% 50%, rgb(0 0 0 / 24%) 0%, transparent 70%), #454e5f;
        z-index: -1;
        pointer-events: none;
    }

    /* Highlight spéculaire supérieur — effet verre courbé iOS */
    #alpha-sidebar::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 120px;
        background: linear-gradient(180deg,
            rgba(255,255,255,0.12) 0%,
            rgba(255,255,255,0.04) 40%,
            transparent 100%
        );
        pointer-events: none;
        z-index: 0;
        border-radius: 0 0 60% 60% / 0 0 30px 30px;
    }

    /* Grain subtil — texture verre dépoli Apple */
    #alpha-sidebar::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.025'/%3E%3C/svg%3E");
        background-size: 256px;
        pointer-events: none;
        z-index: 0;
    }

    /* Tous les enfants au-dessus des pseudo-éléments */
    #alpha-sidebar > * { position: relative; z-index: 1; }

    /* ===== BRAND LINK ===== */
    .alpha-brand-link {
        display: flex !important;
        align-items: center;
        gap: 12px;
        padding: 22px 18px 16px !important;
        border-bottom: none !important;
        text-decoration: none;
    }

    .alpha-brand-link:hover { text-decoration: none; opacity: 0.85; }

    .alpha-logo-wrap { flex-shrink: 0; }

    .alpha-logo-img {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        object-fit: cover;
        display: block;
        border: 1px solid rgba(255,255,255,0.30);
        box-shadow:
            0 2px 12px rgba(0,0,0,0.30),
            inset 0 1px 0 rgba(255,255,255,0.40);
    }

    .alpha-brand-text { display: flex; flex-direction: column; gap: 3px; }

    .alpha-brand-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--ios-ink);
        letter-spacing: -0.01em;
        line-height: 1;
    }

    .alpha-brand-tagline {
        font-size: 9px;
        font-weight: 400;
        color: var(--ios-ink-30);
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    /* ===== USER CARD ===== */
    .alpha-user-card {
        display: flex;
        align-items: center;
        gap: 11px;
        margin: 0 10px 10px;
        padding: 10px 12px;
        background: var(--ios-glass-bg);
        border: 1px solid var(--ios-border);
        border-radius: 14px;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.20);
    }

    .alpha-user-card:hover {
        background: var(--ios-glass-bg-h);
        border-color: var(--ios-border-h);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.30), 0 4px 16px rgba(0,0,0,0.15);
    }

    .alpha-user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(255,255,255,0.30), rgba(255,255,255,0.10));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(255,255,255,0.35);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.50);
    }

    .alpha-user-avatar span {
        font-size: 13px;
        font-weight: 600;
        color: var(--ios-ink);
    }

    .alpha-user-info { flex: 1; min-width: 0; }

    .alpha-user-name {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--ios-ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .alpha-user-role {
        display: block;
        font-size: 10px;
        color: var(--ios-ink-30);
        letter-spacing: 0.03em;
    }

    .alpha-user-chevron {
        font-size: 9px;
        color: var(--ios-ink-30);
        flex-shrink: 0;
    }

    /* ===== DIVIDER ===== */
    .alpha-divider {
        height: 1px;
        margin: 0 10px 6px;
        background: linear-gradient(90deg, transparent, var(--ios-separator) 30%, var(--ios-separator) 70%, transparent);
    }

    /* ===== SIDEBAR BODY ===== */
    .alpha-sidebar-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* ===== NAV HEADERS ===== */
    .alpha-nav .nav-header {
        font-family: 'Sora', sans-serif !important;
        font-size: 9px !important;
        font-weight: 600 !important;
        letter-spacing: 0.18em !important;
        color: var(--ios-ink-30) !important;
        padding: 16px 20px 6px !important;
        text-transform: uppercase !important;
    }

    /* ===== NAV ITEMS ===== */
    .alpha-nav .nav-item > .nav-link {
        font-family: 'Sora', sans-serif !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        color: var(--ios-ink-60) !important;
        border-radius: 12px !important;
        margin: 2px 8px !important;
        padding: 10px 14px !important;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s !important;
        position: relative;
        display: flex !important;
        align-items: center !important;
        border: 1px solid transparent !important;
    }

    .alpha-nav .nav-item > .nav-link:hover {
        background: var(--ios-glass-bg-h) !important;
        color: var(--ios-ink) !important;
        border-color: var(--ios-border) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18) !important;
        text-decoration: none !important;
    }

    /* Actif — pill glass blanc iOS */
    .alpha-nav .nav-item > .nav-link.active,
    .alpha-nav .nav-item.menu-open > .nav-link {
        background: var(--ios-glass-active) !important;
        color: var(--ios-ink) !important;
        font-weight: 500 !important;
        border-color: rgba(255,255,255,0.28) !important;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.40),
            0 2px 12px rgba(0,0,0,0.12) !important;
    }

    /* Barre active — fine ligne blanche */
    .alpha-nav .nav-item > .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0; top: 22%;
        height: 56%; width: 2.5px;
        background: rgba(255,255,255,0.80);
        border-radius: 0 3px 3px 0;
        box-shadow: 0 0 6px rgba(255,255,255,0.50);
    }

    /* Icons */
    .alpha-nav .nav-link .nav-icon {
        color: var(--ios-ink-30) !important;
        font-size: 14px !important;
        margin-right: 11px !important;
        width: 18px;
        text-align: center;
        transition: color 0.18s;
        flex-shrink: 0;
    }

    .alpha-nav .nav-link:hover .nav-icon,
    .alpha-nav .nav-link.active .nav-icon {
        color: var(--ios-ink) !important;
    }

    /* Sous-menus */
    .alpha-nav .nav-treeview { padding-left: 4px; }

    .alpha-nav .nav-treeview .nav-link {
        font-size: 12px !important;
        padding: 8px 14px 8px 30px !important;
        color: var(--ios-ink-30) !important;
        border: none !important;
    }

    .alpha-nav .nav-treeview .nav-link:hover {
        color: var(--ios-ink-60) !important;
        background: var(--ios-ink-15) !important;
    }

    .alpha-nav .nav-treeview .nav-link.active {
        color: var(--ios-ink) !important;
        background: transparent !important;
        border: none !important;
        font-weight: 500 !important;
    }

    .alpha-nav .nav-treeview .nav-link.active::before { display: none; }

    /* Flèche treeview */
    .alpha-nav .nav-link .right {
        color: var(--ios-ink-30) !important;
        font-size: 10px !important;
        margin-left: auto !important;
        transition: transform 0.25s cubic-bezier(.4,0,.2,1), color 0.2s !important;
    }
    .alpha-nav .nav-item.menu-open > .nav-link .right {
        color: var(--ios-ink-60) !important;
        transform: rotate(-90deg);
    }

    /* ===== SIDEBAR FOOTER ===== */
    .alpha-sidebar-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 20px;
        border-top: 1px solid var(--ios-separator);
    }

    .alpha-footer-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 7px rgba(52,211,153,0.75);
        flex-shrink: 0;
        animation: pulse-dot 2.8s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.35; }
    }

    .alpha-footer-text {
        font-size: 10px;
        /* font-family: 'Sora', sans-serif; */
        color: var(--ios-ink-30);
        letter-spacing: 0.06em;
    }

    /* ===== SCROLLBAR ===== */
    /* .alpha-sidebar-body::-webkit-scrollbar { width: 3px; }
    .alpha-sidebar-body::-webkit-scrollbar-track { background: transparent; }
    .alpha-sidebar-body::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
    } */
   /* Masque la barre de défilement sur Chrome, Safari et Opera */
    .sidebar::-webkit-scrollbar {
        display: none;
    }

    /* Masque la barre de défilement sur Firefox et IE/Edge */
    .sidebar {
        -ms-overflow-style: none;  /* IE et Edge */
        scrollbar-width: none;  /* Firefox */
    }

    /* ===== LAYOUT ADMINLTE ===== */
    .content-wrapper,
    .main-footer {
        margin-left: 260px !important;
    }

    .sidebar-collapse .content-wrapper,
    .sidebar-collapse .main-footer {
        margin-left: 0 !important;
    }


</style>

