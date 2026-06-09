<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DP-LMS') — Sto. Domingo NHS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Sidebar */
            --sb-bg:            #111827;
            --sb-bg-hover:      #1F2937;
            --sb-active:        #4F46E5;
            --sb-active-hover:  #4338CA;
            --sb-active-glow:   rgba(79,70,229,.20);
            --sb-border:        rgba(255,255,255,.07);
            --sb-text:          #6B7280;
            --sb-text-hover:    #D1D5DB;
            --sb-text-active:   #FFFFFF;
            --sb-icon-bg:       rgba(255,255,255,.05);
            --sb-icon-active:   rgba(255,255,255,.15);

            /* App chrome */
            --nav-bg:           #FFFFFF;
            --bg:               #F8F9FC;
            --surface:          #FFFFFF;
            --border:           #E5E7EB;
            --border-strong:    #D1D5DB;

            /* Accent */
            --accent:           #4F46E5;
            --accent-dark:      #4338CA;
            --accent-lt:        #EEF2FF;
            --accent-mid:       #818CF8;

            /* Text */
            --text-1:           #111827;
            --text-2:           #6B7280;
            --text-3:           #9CA3AF;

            /* Status */
            --danger:           #DC2626;
            --green:            #16A34A;
            --green-lt:         #DCFCE7;

            /* Sizes */
            --sidebar-w:            252px;
            --sidebar-w-collapsed:   64px;
            --nav-h:                 60px;

            /* Radii & shadows */
            --r-xs:  4px;
            --r-sm:  6px;
            --r-md:  10px;
            --r-lg:  14px;
            --shadow-xs: 0 1px 2px rgba(17,24,39,.06);
            --shadow-sm: 0 1px 3px rgba(17,24,39,.08), 0 1px 2px rgba(17,24,39,.05);
            --shadow-md: 0 4px 12px rgba(17,24,39,.08), 0 1px 3px rgba(17,24,39,.05);
            --shadow-lg: 0 16px 40px rgba(17,24,39,.14), 0 4px 10px rgba(17,24,39,.06);

            /* Motion */
            --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
            --ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text-1);
            min-height: 100vh;
            overflow-x: hidden;
            opacity: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Top Nav ─────────────────────────────────── */
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--nav-h);
            background: var(--nav-bg);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; padding: 0 20px 0 0;
            box-shadow: var(--shadow-xs);
        }

        .brand-zone {
            width: var(--sidebar-w); flex-shrink: 0;
            display: flex; align-items: center; flex-direction: row;
            padding: 0 14px; gap: 10px; height: 100%;
            background: var(--sb-bg);
            transition: width .28s var(--ease-out);
            overflow: hidden;
        }
        body.collapsed .brand-zone { width: var(--sidebar-w-collapsed); }

        /* Collapse button */
        .collapse-btn {
            width: 30px; height: 30px; border-radius: var(--r-sm);
            border: 1px solid rgba(255,255,255,.09); cursor: pointer;
            background: rgba(255,255,255,.05); color: #6B7280;
            flex-shrink: 0; display: flex; align-items: center; justify-content: center;
            transition: background .18s, color .18s, border-color .18s, transform .18s;
        }
        .collapse-btn:hover {
            background: var(--sb-active); border-color: var(--sb-active);
            color: #fff; transform: scale(1.05);
        }
        .collapse-btn svg { transition: transform .30s var(--ease-out); }
        body.collapsed .collapse-btn svg { transform: rotate(180deg); }

        /* Brand — logo + text always side by side */
        .brand {
            display: flex; align-items: center; flex-direction: row;
            gap: 9px; flex: 1; min-width: 0;
            overflow: hidden; white-space: nowrap;
            transition: opacity .20s, max-width .28s var(--ease-out);
            max-width: 196px;
        }
        body.collapsed .brand { opacity: 0; max-width: 0; pointer-events: none; }

        /* Logo mark */
        .logo-mark { flex-shrink: 0; width: 30px; height: 30px; display: block; }

        .brand-text {
            line-height: 1.25; flex-shrink: 0;
            display: flex; flex-direction: column;
        }
        .brand-name {
            font-size: 12px; font-weight: 700;
            color: #F9FAFB; letter-spacing: .015em;
            white-space: nowrap;
        }
        .brand-sub { font-size: 10px; font-weight: 400; color: #6B7280; letter-spacing: .01em; white-space: nowrap; }

        /* Nav right */
        .nav-right {
            flex: 1; display: flex; align-items: center;
            justify-content: flex-end; gap: 8px;
        }

        /* School name in nav */
        .nav-school {
            font-size: 13px; font-weight: 500;
            color: var(--text-2);
            padding-right: 12px;
            border-right: 1px solid var(--border);
            margin-right: 4px;
        }

        /* Role badge */
        .role-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px 3px 8px; border-radius: 20px;
            background: var(--accent-lt); color: var(--accent);
            font-size: 11.5px; font-weight: 600;
            border: 1px solid #C7D2FE;
        }
        .role-badge-dot {
            width: 5px; height: 5px; border-radius: 50%;
            background: var(--accent); flex-shrink: 0;
        }

        /* User chip */
        .user-chip {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 12px 4px 4px; border-radius: 30px;
            border: 1px solid var(--border); background: var(--surface);
            cursor: default; transition: border-color .18s, box-shadow .18s;
        }
        .user-chip:hover { border-color: #C7D2FE; box-shadow: 0 0 0 3px var(--accent-lt); }
        .user-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, #4F46E5 0%, #818CF8 100%);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text-1); line-height: 1.3; }
        .user-role { font-size: 11px; color: var(--accent); font-weight: 500; }

        /* ─── Layout ──────────────────────────────────── */
        .layout { display: flex; padding-top: var(--nav-h); min-height: 100vh; }

        /* ─── Sidebar ─────────────────────────────────── */
        .sidebar {
            position: fixed; top: var(--nav-h); left: 0; bottom: 0;
            width: var(--sidebar-w); background: var(--sb-bg);
            display: flex; flex-direction: column; overflow: hidden;
            
            transition: width .28s var(--ease-out);
            z-index: 90;
        }
        body.collapsed .sidebar { width: var(--sidebar-w-collapsed); overflow: visible; }
        body.collapsed .sidebar-nav { overflow: visible; }

        /* Section label */
        .nav-section {
            padding: 20px 16px 4px;
            font-size: 9.5px; font-weight: 700;
            letter-spacing: .1em; color: #374151;
            text-transform: uppercase; white-space: nowrap; overflow: hidden;
            transition: opacity .18s;
        }
        body.collapsed .nav-section { opacity: 0; }

        /* Nav scroll area */
        .sidebar-nav {
            padding: 4px 10px 10px; flex: 1;
            overflow-y: auto; overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.06) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 3px; }

        /* Nav links */
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 7px 8px 7px 6px; border-radius: var(--r-md);
            font-size: 13.5px; font-weight: 500; color: var(--sb-text);
            text-decoration: none; margin-bottom: 2px;
            white-space: nowrap; position: relative; overflow: visible;
            transition: background .18s, color .16s, transform .18s var(--ease-out);
        }
        .sidebar-nav a:hover {
            background: var(--sb-bg-hover); color: var(--sb-text-hover);
            transform: translateX(2px);
        }
        .sidebar-nav a.active {
            background: rgba(79,70,229,.16); color: #A5B4FC;
            font-weight: 600;
        }
        .sidebar-nav a.active .nav-icon {
            background: var(--sb-active);
            box-shadow: 0 3px 10px var(--sb-active-glow);
        }

        /* Active indicator bar */
        .sidebar-nav a.active::before {
            content: '';
            position: absolute; left: -10px; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 22px;
            background: var(--accent-mid);
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--r-sm); background: var(--sb-icon-bg);
            transition: background .18s, box-shadow .18s;
        }
        .sidebar-nav a:hover:not(.active) .nav-icon { background: rgba(255,255,255,.08); }
        .nav-icon svg { width: 16px; height: 16px; stroke-width: 2; flex-shrink: 0; }

        .nav-label {
            white-space: nowrap; overflow: hidden; flex: 1; min-width: 0;
            transition: opacity .18s, max-width .28s var(--ease-out);
            max-width: 160px;
        }
        body.collapsed .nav-label { opacity: 0; max-width: 0; }
        body.collapsed .sidebar-nav a { justify-content: center; padding: 8px; }

        /* Badge */
        .pending-count {
            background: #EF4444; color: #fff;
            font-size: 10px; font-weight: 700;
            min-width: 18px; height: 18px;
            padding: 0 5px; border-radius: 9px;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: opacity .18s;
        }
        body.collapsed .pending-count { opacity: 0; }

        /* Tooltip on collapsed */
        .nav-tip {
            position: absolute; left: calc(100% + 12px); top: 50%;
            transform: translateY(-50%) translateX(-4px);
            background: #1F2937; color: #F9FAFB;
            padding: 6px 11px; border-radius: var(--r-sm);
            font-size: 12px; font-weight: 600; white-space: nowrap;
            pointer-events: none; opacity: 0; visibility: hidden;
            transition: opacity .18s, visibility .18s, transform .18s;
            box-shadow: var(--shadow-md); border: 1px solid rgba(255,255,255,.06); z-index: 300;
        }
        .nav-tip::before {
            content: ''; position: absolute; right: 100%; top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent; border-right-color: #1F2937;
        }
        body.collapsed .sidebar-nav a:hover .nav-tip {
            opacity: 1; visibility: visible; transform: translateY(-50%) translateX(0);
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 8px 10px 16px;
            border-top: 1px solid var(--sb-border);
        }

        .logout-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 7px 8px 7px 6px; border-radius: var(--r-md);
            border: none; cursor: pointer; background: transparent;
            font-family: inherit; font-size: 13.5px; font-weight: 500;
            color: var(--sb-text); white-space: nowrap; position: relative;
            transition: background .2s, color .2s, transform .22s var(--ease-out);
        }
        .logout-btn:hover {
            background: rgba(220,38,38,.12); color: #F87171;
            transform: translateX(2px);
        }
        .logout-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--r-sm); background: rgba(220,38,38,.10);
            transition: background .2s;
        }
        .logout-btn:hover .logout-icon { background: rgba(220,38,38,.22); }
        .logout-icon svg { width: 15px; height: 15px; stroke: #EF4444; stroke-width: 2.2; }
        body.collapsed .logout-btn { justify-content: center; padding: 8px; }
        body.collapsed .logout-btn .nav-label { opacity: 0; max-width: 0; overflow: hidden; }

        /* ─── Main ────────────────────────────────────── */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-w);
            padding: 28px 32px;
            transition: margin-left .28s var(--ease-out);
            opacity: 0; min-width: 0;
        }
        body.collapsed .main-content { margin-left: var(--sidebar-w-collapsed); }

        /* ─── Toast ───────────────────────────────────── */
        #dp-toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            display: flex; align-items: center; gap: 9px;
            padding: 12px 16px; border-radius: var(--r-md);
            font-size: 13px; font-weight: 500;
            box-shadow: var(--shadow-lg);
            pointer-events: none; opacity: 0;
            transform: translateY(10px) scale(.97);
            transition: opacity .25s var(--ease-out), transform .25s var(--ease-out);
            max-width: 320px;
        }
        #dp-toast.show { opacity: 1; transform: translateY(0) scale(1); }
        #dp-toast.hide { opacity: 0; transform: translateY(6px) scale(.97); transition: opacity .3s ease, transform .3s ease; }
        #dp-toast.toast-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #15803D; }
        #dp-toast.toast-error   { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }

        /* ─── Logout modal ────────────────────────────── */
        .modal-bg {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(17,24,39,.50); backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity .22s;
        }
        .modal-bg.open { opacity: 1; pointer-events: all; }

        .modal-box {
            background: var(--surface); border-radius: 16px;
            padding: 32px 28px 26px; width: 310px; text-align: center;
            box-shadow: var(--shadow-lg); border: 1px solid var(--border);
            transform: translateY(20px) scale(.96);
            transition: transform .30s var(--ease-out);
        }
        .modal-bg.open .modal-box { transform: none; }

        .modal-icon {
            width: 50px; height: 50px; border-radius: var(--r-md);
            background: #FEF2F2; margin: 0 auto 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-title { font-size: 16px; font-weight: 700; color: var(--text-1); margin-bottom: 6px; }
        .modal-body  { font-size: 13px; color: var(--text-3); margin-bottom: 22px; line-height: 1.55; }

        .modal-actions { display: flex; gap: 8px; }
        .modal-cancel, .modal-confirm {
            flex: 1; padding: 10px; border-radius: var(--r-md);
            border: none; font-size: 13.5px; font-weight: 600;
            cursor: pointer; font-family: inherit;
            transition: opacity .15s, transform .15s;
        }
        .modal-cancel  { background: var(--bg); color: var(--text-2); border: 1px solid var(--border); }
        .modal-confirm { background: var(--danger); color: #fff; }
        .modal-cancel:hover  { background: #F3F4F6; transform: translateY(-1px); }
        .modal-confirm:hover { opacity: .88; transform: translateY(-1px); }

        /* ─── Accessibility: keyboard focus rings ─────── */
        .sidebar-nav a:focus-visible,
        .logout-btn:focus-visible,
        .collapse-btn:focus-visible,
        .modal-cancel:focus-visible,
        .modal-confirm:focus-visible {
            outline: 2px solid var(--accent-mid);
            outline-offset: 2px;
        }

        /* ─── Responsive ──────────────────────────────── */
        @media (max-width: 1100px) {
            .main-content { padding: 22px 20px; }
            .nav-school   { display: none; }
        }
        @media (max-width: 720px) {
            .main-content        { padding: 16px 14px; }
            .role-badge          { display: none; }
            .user-name, .user-role { display: none; }
            .user-chip           { padding: 4px; }
        }

        /* ─── Respect reduced motion ──────────────────── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

<!-- ─── Top Nav ──────────────────────────────────────── -->
<nav class="top-nav">
    <div class="brand-zone">
        <button class="collapse-btn" id="collapseBtn" aria-label="Toggle sidebar">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M15 18l-6-6 6-6"/>
            </svg>
        </button>
        <div class="brand">
            <!-- Improved logo: shield with open book -->
            <svg class="logo-mark" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Shield base -->
                <path d="M16 2L4 7v8c0 7.18 5.17 13.9 12 15.93C22.83 28.9 28 22.18 28 15V7L16 2z" fill="#312E81"/>
                <!-- Shield highlight -->
                <path d="M16 2L4 7v8c0 7.18 5.17 13.9 12 15.93C22.83 28.9 28 22.18 28 15V7L16 2z" fill="url(#shieldGrad)" opacity=".3"/>
                <!-- Open book -->
                <path d="M10 11.5c0-.28.22-.5.5-.5H15v9h-4.5a.5.5 0 01-.5-.5v-8z" fill="white" opacity=".9"/>
                <path d="M22 11.5c0-.28-.22-.5-.5-.5H17v9h4.5a.5.5 0 00.5-.5v-8z" fill="white" opacity=".9"/>
                <rect x="15" y="11" width="2" height="9" rx=".5" fill="white" opacity=".5"/>
                <!-- Book spine line -->
                <line x1="16" y1="11" x2="16" y2="20" stroke="#312E81" stroke-width=".8" opacity=".4"/>
                <!-- Page lines left -->
                <line x1="11.5" y1="13.5" x2="14" y2="13.5" stroke="#4338CA" stroke-width=".7" stroke-linecap="round"/>
                <line x1="11.5" y1="15" x2="14" y2="15" stroke="#4338CA" stroke-width=".7" stroke-linecap="round"/>
                <line x1="11.5" y1="16.5" x2="14" y2="16.5" stroke="#4338CA" stroke-width=".7" stroke-linecap="round"/>
                <!-- Page lines right -->
                <line x1="18" y1="13.5" x2="20.5" y2="13.5" stroke="#4338CA" stroke-width=".7" stroke-linecap="round"/>
                <line x1="18" y1="15" x2="20.5" y2="15" stroke="#4338CA" stroke-width=".7" stroke-linecap="round"/>
                <line x1="18" y1="16.5" x2="20.5" y2="16.5" stroke="#4338CA" stroke-width=".7" stroke-linecap="round"/>
                <defs>
                    <linearGradient id="shieldGrad" x1="4" y1="2" x2="28" y2="32" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#818CF8"/>
                        <stop offset="1" stop-color="#312E81"/>
                    </linearGradient>
                </defs>
            </svg>
            <div class="brand-text">
                <div class="brand-name">SDNHS</div>
                <div class="brand-sub">LMS</div>
            </div>
        </div>
    </div>

    <div class="nav-right">
        <span class="nav-school">Academic Year {{ now()->month >= 6 ? now()->year : now()->year - 1 }}–{{ now()->month >= 6 ? now()->year + 1 : now()->year }}</span>
        <div class="role-badge">
            <span class="role-badge-dot"></span>
            Faculty
        </div>
        <div class="user-chip">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">Faculty</div>
            </div>
        </div>
    </div>
</nav>

<!-- ─── Layout ───────────────────────────────────────── -->
<div class="layout">
    <aside class="sidebar">
        <div class="nav-section">Navigation</div>
        <nav class="sidebar-nav" id="sidebarNav"></nav>
        <div class="sidebar-footer">
            <button class="logout-btn" id="logoutTrigger">
                <span class="logout-icon">
                    <svg fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0
                              01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </span>
                <span class="nav-label">Log out</span>
                <span class="nav-tip">Log out</span>
            </button>
        </div>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>
</div>

<!-- ─── Logout modal ─────────────────────────────────── -->
<div class="modal-bg" id="logoutModal">
    <div class="modal-box">
        <div class="modal-icon">
            <svg width="22" height="22" fill="none" stroke="#DC2626" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>
        <div class="modal-title">Log out?</div>
        <div class="modal-body">You'll be signed out of your session.</div>
        <div class="modal-actions">
            <button class="modal-cancel" id="modalCancel">Cancel</button>
            <form method="POST" action="{{ route('logout') }}" style="flex:1;display:flex;">
                @csrf
                <button type="submit" class="modal-confirm" style="width:100%;">Log out</button>
            </form>
        </div>
    </div>
</div>

<div id="dp-toast" role="alert" aria-live="polite"></div>

<script>
(function () {
    const PENDING_COUNT = {{ $pendingCount ?? 0 }};

    const NAV_ITEMS = [
        {
            href : '/faculty/dashboard',
            label: 'Dashboard',
            icon : `<rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>`,
        },
        {
            href  : '/faculty/enrollments',
            label : 'Enrollments',
            badge : PENDING_COUNT > 0 ? PENDING_COUNT : null,
            icon  : `<path stroke-linecap="round" stroke-linejoin="round"
                       d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                          a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>`,
        },
        {
            href  : '/faculty/teachers',
            label : 'Teachers',
            icon  : `<path stroke-linecap="round" stroke-linejoin="round"
                       d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0
                          013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-8a4
                          4 0 11-8 0 4 4 0 018 0z"/>`,
        },
    ];

    const nav     = document.getElementById('sidebarNav');
    const curPath = window.location.pathname.replace(/\/$/, '');

    NAV_ITEMS.forEach(item => {
        const cleanHref = item.href.replace(/\/$/, '');
        const isActive  = curPath === cleanHref || curPath.startsWith(cleanHref + '/');

        const a    = document.createElement('a');
        a.href     = item.href;
        if (isActive) a.classList.add('active');

        const badgeHtml = item.badge
            ? `<span class="pending-count">${item.badge}</span>`
            : '';

        a.innerHTML = `
            <span class="nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     width="16" height="16" stroke-width="2">
                    ${item.icon}
                </svg>
            </span>
            <span class="nav-label">${item.label}${badgeHtml ? ' ' + badgeHtml : ''}</span>
            <span class="nav-tip">${item.label}</span>
        `;

        a.addEventListener('click', function () {
            // Reflect active state immediately; the browser performs a normal,
            // reliable full navigation (so each page's own scripts/animations run).
            document.querySelectorAll('#sidebarNav a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });

        nav.appendChild(a);
    });

    /* Collapse */
    const body = document.body;
    const CKEY = 'dp-lms-faculty-collapsed';
    if (localStorage.getItem(CKEY) === '1') body.classList.add('collapsed');
    document.getElementById('collapseBtn').addEventListener('click', () => {
        body.classList.toggle('collapsed');
        localStorage.setItem(CKEY, body.classList.contains('collapsed') ? '1' : '0');
    });

    /* Logout modal */
    const modal = document.getElementById('logoutModal');
    document.getElementById('logoutTrigger').addEventListener('click', () => modal.classList.add('open'));
    document.getElementById('modalCancel').addEventListener('click',   () => modal.classList.remove('open'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.classList.remove('open'); });

    /* Entrance animation */
    window.addEventListener('load', () => {
        if (typeof gsap === 'undefined') {
            body.style.opacity = '1';
            document.querySelector('.main-content').style.opacity = '1';
            return;
        }
        gsap.timeline({ defaults: { ease: 'power3.out' } })
            .to('body',            { opacity: 1,                        duration: .22 })
            .from('.top-nav',      { y: -16, opacity: 0,                duration: .30 }, '-=.10')
            .from('.sidebar',      { x: -14, opacity: 0,                duration: .30 }, '-=.26')
            .to('.main-content',   { opacity: 1,                        duration: .36 }, '-=.22')
            .from('#sidebarNav a', { x: -6, opacity: 0, stagger: .06,  duration: .22 }, '-=.28');
    });
})();
</script>

@php
    $toastMsg  = session('success') ?? session('error') ?? null;
    $toastType = session('success') ? 'success' : (session('error') ? 'error' : null);
@endphp
@if($toastMsg)
<script>
    window.__dpToast = { msg: @json($toastMsg), type: '{{ $toastType }}' };
</script>
@endif

<script>
function dpShowToast(msg, type) {
    const toast = document.getElementById('dp-toast');
    if (!toast || !msg) return;

    const iconOk  = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
    const iconErr = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;

    toast.className = '';
    toast.classList.add(type === 'success' ? 'toast-success' : 'toast-error');
    toast.innerHTML = (type === 'success' ? iconOk : iconErr) + `<span>${msg}</span>`;

    requestAnimationFrame(() => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.add('hide');
            toast.classList.remove('show');
            setTimeout(() => { toast.className = ''; toast.innerHTML = ''; }, 320);
        }, 2400);
    });
}

if (window.__dpToast) {
    window.addEventListener('load', () => dpShowToast(window.__dpToast.msg, window.__dpToast.type));
}
window.dpShowToast = dpShowToast;
</script>

</body>
</html>