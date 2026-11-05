<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DP-LMS') — Sto. Domingo NHS</title>
    <meta name="theme-color" content="#FFFFFF">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════
           DESIGN TOKENS
        ═══════════════════════════════════════════ */
        :root {
            /* Sidebar — white, high-contrast text */
            --sb-bg:            #FFFFFF;
            --sb-border:        #E6EAF1;
            --sb-text:          #334155;   /* 10.3:1 on white */
            --sb-text-hover:    #0F172A;
            --sb-icon:          #64748B;
            --sb-hover-bg:      #F3F5F9;
            --sb-active-bg:     #EEF3FF;
            --sb-active-text:   #1D4ED8;   /* 6.9:1 on the active tint */

            /* Page & nav */
            --nav-bg:           #FFFFFF;
            --bg:               #F5F7FB;
            --surface:          #FFFFFF;
            --border:           #E6EAF1;

            /* Accent */
            --accent:           #2563EB;
            --accent-dark:      #1D4ED8;
            --accent-lt:        #EFF6FF;
            --accent-glow:      rgba(37,99,235,.18);

            /* Text */
            --text-1:           #0F172A;
            --text-2:           #475569;
            --text-3:           #64748B;   /* #94A3B8 was 2.6:1 on white — too faint for small text */

            /* Status */
            --danger:           #EF4444;
            --green:            #16A34A;
            --green-lt:         #DCFCE7;
            --green-dark:       #14532D;
            --green-mid:        #22C55E;
            --green-glow:       rgba(22,163,74,.22);

            /* Layout */
            --sidebar-w:            264px;
            --sidebar-w-collapsed:   72px;
            --menu-time: .32s;
            --menu-ease: cubic-bezier(.22,.61,.36,1);
            --nav-h:                 64px;

            /* Radii & shadows */
            --r-sm:  8px;
            --r-md:  12px;
            --r-lg:  16px;
            --shadow-sm:  0 1px 2px rgba(15,23,42,.05);
            --shadow-md:  0 4px 16px rgba(15,23,42,.08), 0 1px 3px rgba(15,23,42,.05);
            --shadow-lg:  0 16px 48px rgba(15,23,42,.14), 0 4px 12px rgba(15,23,42,.06);
        }

        /* ═══════════════════════════════════════════
           RESET
        ═══════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text-1);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Instant, JS-free page entrance — chrome static, content quick-fade */
        @keyframes contentIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: none; }
        }

        /* ═══════════════════════════════════════════
           TOP NAV
        ═══════════════════════════════════════════ */
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--nav-h);
            background: var(--nav-bg);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; padding: 0 24px 0 0;
        }

        /* Brand zone — same width as the sidebar, so the logo sits right above the menu */
        .nav-brand-zone {
            width: var(--sidebar-w); flex-shrink: 0;
            display: flex; align-items: center; padding: 0 16px;
            height: 100%;
            border-right: 1px solid var(--sb-border);
            transition: width var(--menu-time) var(--menu-ease), padding var(--menu-time) var(--menu-ease);
        }
        body.collapsed .nav-brand-zone { width: var(--sidebar-w-collapsed); padding: 0 17px; }

        /* Sidebar toggle — first control of the top bar */
        .collapse-btn {
            width: 40px; height: 40px; border-radius: var(--r-sm); margin-left: 16px;
            border: 1px solid var(--border); cursor: pointer;
            background: var(--surface); color: var(--text-2); flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .1s ease;
        }
        .collapse-btn:hover { background: var(--sb-hover-bg); color: var(--text-1); border-color: #D5DBE5; }

        /* Logo + school name */
        .nav-brand {
            display: flex; align-items: center; gap: 12px;
            min-width: 0; text-decoration: none;
        }
        .logo-mark { flex-shrink: 0; width: 38px; height: 38px; display: block; border-radius: 50%; object-fit: cover; }
        .brand-text {
            min-width: 0; white-space: nowrap; overflow: hidden;
            transition: opacity .2s ease .08s, max-width var(--menu-time) var(--menu-ease);
            max-width: 196px;
        }
        .brand-title {
            display: block; font-size: 15px; font-weight: 800; color: var(--text-1);
            letter-spacing: -.01em; line-height: 1.2;
        }
        .brand-sub { display: block; font-size: 12px; font-weight: 500; color: var(--text-3); line-height: 1.3; margin-top: 1px; }
        body.collapsed .brand-text { opacity: 0; max-width: 0; transition: opacity .12s ease, max-width var(--menu-time) var(--menu-ease); }

        /* Nav right */
        .nav-right { flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 10px; }

        .nav-icon-btn {
            position: relative; width: 40px; height: 40px; border-radius: var(--r-sm);
            border: 1px solid var(--border); background: var(--surface); cursor: pointer;
            display: flex; align-items: center; justify-content: center; color: var(--text-2);
            transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .1s ease;
        }
        .nav-icon-btn:hover { background: var(--sb-hover-bg); color: var(--text-1); border-color: #D5DBE5; }
        .notif-dot {
            position: absolute; top: 8px; right: 8px; width: 7px; height: 7px;
            border-radius: 50%; background: var(--danger); border: 2px solid var(--surface);
        }

        .user-chip {
            display: flex; align-items: center; gap: 10px;
            padding: 4px 14px 4px 4px; border-radius: 40px;
            border: 1px solid var(--border); background: var(--surface); cursor: default;
        }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--accent); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; letter-spacing: .02em; flex-shrink: 0;
        }
        .user-name { font-size: 13.5px; font-weight: 700; color: var(--text-1); line-height: 1.25; }
        .user-role { font-size: 12px; color: var(--text-3); font-weight: 500; }

        /* ═══════════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════════ */
        .layout { display: flex; padding-top: var(--nav-h); min-height: 100vh; }

        /* ═══════════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════════ */
        .sidebar {
            position: fixed; top: var(--nav-h); left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--sb-bg);
            border-right: 1px solid var(--sb-border);
            display: flex; flex-direction: column; overflow: hidden;
            transition: width var(--menu-time) var(--menu-ease);
            z-index: 90;
        }
        body.collapsed .sidebar { width: var(--sidebar-w-collapsed); overflow: visible; }   /* tooltips escape */
        body.collapsed .sidebar-nav { overflow: visible; }

        .sidebar-nav {
            padding: 16px 12px; flex: 1;
            display: flex; flex-direction: column; gap: 2px;
            overflow-y: auto; overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #D5DBE5 transparent;
        }

        /* ── Nav link ── */
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            min-height: 42px; padding: 0 14px; border-radius: var(--r-sm);   /* the icon then sits at the centre of the narrow menu */
            font-size: 14px; font-weight: 600; color: var(--sb-text);
            text-decoration: none; white-space: nowrap; position: relative;
            transition: background-color .15s ease, color .15s ease;
        }
        .sidebar-nav a:hover { background: var(--sb-hover-bg); color: var(--sb-text-hover); }
        .sidebar-nav a.active { background: var(--sb-active-bg); color: var(--sb-active-text); font-weight: 700; }
        /* Active marker: a quiet bar on the sidebar's edge */
        .sidebar-nav a.active::before {
            content: ''; position: absolute; left: -12px; top: 10px; bottom: 10px;
            width: 3px; border-radius: 0 3px 3px 0; background: var(--sb-active-text);
        }

        .nav-icon { width: 20px; height: 20px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--sb-icon); }
        .nav-icon svg { width: 20px; height: 20px; stroke-width: 1.8; }
        .sidebar-nav a:hover .nav-icon { color: var(--sb-text); }
        .sidebar-nav a.active .nav-icon { color: var(--sb-active-text); }

        .nav-label {
            white-space: nowrap; overflow: hidden;
            flex: 1; min-width: 0;
            transition: opacity .2s ease .08s;
        }
        body.collapsed .nav-label { opacity: 0; transition: opacity .12s ease; }

        /* Tooltip (collapsed sidebar only) */
        .sb-tooltip {
            position: absolute;
            left: calc(100% + 12px); top: 50%;
            transform: translateY(-50%);
            background: #0F172A; color: #F8FAFC;
            padding: 7px 11px; border-radius: 6px;
            font-size: 12.5px; font-weight: 600; white-space: nowrap;
            pointer-events: none; opacity: 0; visibility: hidden;
            transition: opacity .12s ease, visibility .12s ease;
            z-index: 300;
        }
        body.collapsed .sidebar-nav a:hover .sb-tooltip,
        body.collapsed .sidebar-nav a:focus-visible .sb-tooltip { opacity: 1; visibility: visible; }

        /* Log out — same row style as the menu, red only on hover */
        .sidebar-footer { padding: 12px; border-top: 1px solid var(--sb-border); }
        .logout-btn {
            display: flex; align-items: center; gap: 12px;
            width: 100%; min-height: 42px; padding: 0 14px; border-radius: var(--r-sm);
            border: none; cursor: pointer; background: transparent; text-align: left;
            font-family: inherit; font-size: 14px; font-weight: 600;
            color: var(--sb-text); white-space: nowrap; position: relative;
            transition: background-color .15s ease, color .15s ease;
        }
        .logout-btn:hover { background: #FEF2F2; color: #B91C1C; }
        .logout-icon { width: 20px; height: 20px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .logout-icon svg { width: 20px; height: 20px; stroke: var(--sb-icon); stroke-width: 1.8; transition: stroke .15s ease; }
        .logout-btn:hover .logout-icon svg { stroke: #B91C1C; }

        /* ═══════════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════════ */
        .main-content {
            flex: 1; margin-left: var(--sidebar-w);
            padding: 32px 40px 48px;
            transition: margin-left var(--menu-time) var(--menu-ease);
            min-width: 0;
            animation: contentIn .22s ease-out both;
        }
        body.collapsed .main-content { margin-left: var(--sidebar-w-collapsed); }

        /* Flash */
        .flash {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: var(--r-md);
            font-size: 13.5px; font-weight: 600; margin-bottom: 20px;
        }
        .flash svg { flex-shrink: 0; }
        .flash-success { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }
        .flash-error   { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
        .flash-info    { background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; }

        /* ═══════════════════════════════════════════
           MODAL
        ═══════════════════════════════════════════ */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(15,23,42,.55); backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity .25s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: all; }
        .modal-box {
            background: var(--surface); border-radius: 22px;
            padding: 36px 32px 30px; width: 340px; text-align: center;
            box-shadow: var(--shadow-lg); border: 1px solid var(--border);
            transform: translateY(24px) scale(.95);
            transition: transform .35s cubic-bezier(.34,1.56,.64,1);
        }
        .modal-overlay.open .modal-box { transform: none; }
        .modal-icon {
            width: 60px; height: 60px; border-radius: var(--r-md);
            background: #FEF2F2; margin: 0 auto 18px;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-title { font-size: 18px; font-weight: 800; color: var(--text-1); margin-bottom: 6px; }
        .modal-desc  { font-size: 13px; color: var(--text-3); margin-bottom: 26px; line-height: 1.65; }
        .modal-actions { display: flex; gap: 10px; }
        .modal-cancel, .modal-confirm {
            flex: 1; padding: 12px; border-radius: var(--r-md);
            border: none; font-size: 14px; font-weight: 700;
            cursor: pointer; font-family: inherit;
            transition: opacity .15s, transform .15s, box-shadow .2s;
        }
        .modal-cancel  { background: var(--surface); color: var(--text-1); border: 1px solid var(--border); }
        .modal-confirm { background: #DC2626; color: #fff; }
        .modal-cancel:hover  { background: var(--sb-hover-bg); border-color: #D5DBE5; }
        .modal-confirm:hover { background: #B91C1C; }

        /* ═══════════ MOBILE RESPONSIVE ═══════════ */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .26s cubic-bezier(.4,0,.2,1);
                width: 272px !important;
            }
            body.mobile-open .sidebar { transform: translateX(0); box-shadow: 8px 0 24px rgba(15,23,42,.10); }
            body.collapsed .sidebar { width: 272px !important; overflow: hidden; }
            body.collapsed .nav-label { opacity: 1 !important; }
            body.collapsed .sb-tooltip { display: none; }

            .main-content,
            body.collapsed .main-content {
                margin-left: 0 !important;
                padding: 18px 16px;
            }
            /* The notification list is placed against the screen, so it fits every phone */
            #notifDropdown { position: fixed !important; top: calc(var(--nav-h) + 8px) !important; left: 12px !important; right: 12px !important; width: auto !important; }
            /* Phone top bar: menu button, then the school, then notifications + avatar */
            .top-nav { padding-right: 12px; }
            .collapse-btn { order: -1; margin: 0 0 0 12px; }
            .nav-brand-zone,
            body.collapsed .nav-brand-zone { width: auto !important; padding: 0 0 0 12px; border-right: 0; min-width: 0; flex-shrink: 1; }
            body.collapsed .brand-text { opacity: 1; max-width: 180px; }
            .logo-mark { width: 34px; height: 34px; }

            .sidebar-backdrop {
                position: fixed; inset: var(--nav-h) 0 0 0; z-index: 85;
                background: rgba(15,23,42,.45); opacity: 0; pointer-events: none;
                transition: opacity .3s;
            }
            body.mobile-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }
        }

        /* ══════════════════════════════════════════════════════
           GLOBAL CONTENT RESPONSIVENESS — every student/parent page
        ══════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            /* Tables scroll sideways on phones instead of breaking the layout */
            .main-content table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .main-content table th,
            .main-content table td { white-space: nowrap; }

            /* Inline 3/4-column grids (stat cards, form rows) -> 1 column */
            .main-content [style*="repeat(3,1fr)"],
            .main-content [style*="repeat(3, 1fr)"],
            .main-content [style*="repeat(4,1fr)"],
            .main-content [style*="repeat(4, 1fr)"] {
                grid-template-columns: 1fr !important;
            }
            /* "sidebar + content" grids -> patong-patong */
            .main-content [style*="320px 1fr"],
            .main-content [style*="280px 1fr"] {
                grid-template-columns: 1fr !important;
            }

            /* Headers / action rows wrap */
            .main-content .panel-header,
            .main-content .page-header {
                flex-wrap: wrap;
                gap: 10px;
            }

            .main-content { overflow-x: hidden; }
            .main-content img, .main-content video { max-width: 100%; height: auto; }
        }

        @media (max-width: 480px) {
            .main-content { padding: 14px 12px !important; }
            /* 16px inputs = no iOS auto-zoom; touch-friendly */
            .main-content input,
            .main-content select,
            .main-content textarea { font-size: 16px !important; min-height: 44px; }
            .main-content button,
            .main-content .btn { min-height: 44px; }
        }

        @media (min-width: 769px) {
            .sidebar-backdrop { display: none; }
        }

        /* Log out: <button> centers its text by default — align with the links above */
        .logout-btn { text-align: left; }

        /* Phones: avatar only in the top bar (the name overflowed past the screen edge) */
        @media (max-width: 600px) {
            .top-nav { padding-right: 12px; }
            .user-chip { padding: 3px; }
            .user-chip > div:last-child { display: none; }
        }

        /* Keyboard focus */
        :focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        :root { color-scheme: light; }
        a, button, label, select { touch-action: manipulation; }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
            /* Exception: the menu moves only when the person presses the menu button */
            .sidebar, .nav-brand-zone, .main-content { transition-duration: var(--menu-time) !important; }
            .brand-text { transition-duration: .2s, var(--menu-time) !important; }
            body.collapsed .brand-text { transition-duration: .12s, var(--menu-time) !important; }
            .nav-label { transition-duration: .2s !important; }
            body.collapsed .nav-label { transition-duration: .12s !important; }
        }
    </style>
</head>
<body>
<script>
    try { if (localStorage.getItem('dp-lms-collapsed') === '1' && window.innerWidth > 768) document.body.classList.add('collapsed'); } catch (e) {}
</script>

{{-- ════════════════════════ TOP NAV ════════════════════════ --}}
<nav class="top-nav">
    <div class="nav-brand-zone">
        {{-- Logo mark + school name --}}
        <a class="nav-brand" href="{{ auth()->user()->role === 'parent' ? route('parent.dashboard') : route('student.dashboard') }}" aria-label="Sto. Domingo NHS, go to the dashboard">
            <img class="logo-mark" src="{{ asset('images/logo.png') }}" alt="" width="38" height="38">
            <span class="brand-text">
                <span class="brand-title">Sto. Domingo NHS</span>
                <span class="brand-sub">Learning Management System</span>
            </span>
        </a>
    </div>

    <button class="collapse-btn" id="collapseBtn" type="button" title="Show or hide the menu" aria-label="Show or hide the menu">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2.5"/>
            <path stroke-linecap="round" d="M9 4v16"/>
        </svg>
    </button>

    <div class="nav-right">
     <div style="position:relative;" id="notifWrapper">
    <button type="button" class="nav-icon-btn" id="notifBtn" title="Notifications" aria-label="Notifications" aria-haspopup="true" aria-expanded="false" aria-controls="notifDropdown">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24">
            <path stroke-linecap="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span id="notifBadge" style="
            display:none; position:absolute; top:-5px; right:-5px;
            min-width:18px; height:18px; border-radius:9px;
            background:var(--danger); color:#fff;
            font-size:10px; font-weight:800; line-height:18px;
            text-align:center; padding:0 4px;
            border:2px solid var(--nav-bg);
        "></span>
    </button>

    {{-- Dropdown --}}
    <div id="notifDropdown" style="
        display:none; position:absolute; top:calc(100% + 10px); right:0;
        width:min(320px, calc(100vw - 24px)); background:var(--surface); border-radius:var(--r-lg);
        border:1px solid var(--border); box-shadow:var(--shadow-lg);
        z-index:500; overflow:hidden;
    ">
        <div style="padding:14px 16px 10px; border-bottom:1px solid var(--border);
                    display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:13px; font-weight:800; color:var(--text-1);">Notifications</span>
            <button type="button" id="markAllRead" style="font-size:11px; color:var(--accent);
                font-weight:600; background:none; border:none; cursor:pointer;">
                Mark all read
            </button>
        </div>
        <div id="notifList" style="max-height:320px; overflow-y:auto;"></div>
        <div id="notifEmpty" style="display:none; padding:32px 16px; text-align:center;">
            <svg width="28" height="28" fill="none" stroke="#cbd5e1" stroke-width="1.8"
                viewBox="0 0 24 24" style="margin:0 auto 8px;">
                <path stroke-linecap="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p style="font-size:12px; color:var(--text-3); font-weight:500;">You're all caught up!</p>
        </div>
    </div>
</div>
        <div class="user-chip">
            <div class="user-avatar" aria-hidden="true">{{ auth()->user()->initials() }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
    </div>
</nav>

{{-- ════════════════════════ LAYOUT ════════════════════════ --}}
<div class="layout">

    <aside class="sidebar">
        {{-- Icons + links are injected by JS below so icons are ALWAYS guaranteed --}}
        <nav class="sidebar-nav" id="sidebarNav"></nav>
        <div class="sidebar-footer">
            <button type="button" class="logout-btn" id="logoutTrigger">
                <span class="logout-icon">
                    <svg fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </span>
                <span class="nav-label">Log out</span>
            </button>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <main class="main-content">
        @include('partials.flash')
        @yield('content')
    </main>
</div>

{{-- LOGOUT MODAL --}}
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
        <div class="modal-icon">
            <svg width="26" height="26" fill="none" stroke="#EF4444" stroke-width="2.3" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>
        <div class="modal-title">Log out?</div>
        <div class="modal-desc">You'll be signed out of your account. Any unsaved progress may be lost.</div>
        <div class="modal-actions">
            <button type="button" class="modal-cancel" id="modalCancel">Cancel</button>
            <form method="POST" action="{{ route('logout') }}" style="flex:1;display:flex;">
                @csrf
                <button type="submit" class="modal-confirm" style="width:100%;">Log out</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    /* ─────────────────────────────────────────────────────────────
       NAV ITEMS — defined here so icons are ALWAYS rendered
       correctly regardless of which child view extends this layout.
       Add / remove items here to update the sidebar globally.
    ───────────────────────────────────────────────────────────── */
const NAV_ITEMS = [
@php($navRole = strtolower(trim((string) auth()->user()->role)))
@if($navRole === 'parent')
    {
        href : '{{ route('parent.dashboard') }}',
        label: 'Dashboard',
        icon : `<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>`
    },
    {
        href : '{{ route('parent.child-records') }}',
        label: "Child's Records",
        icon : `<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/>`
    },
@else
    {
        href : '{{ route('student.dashboard') }}',
        label: 'Dashboard',
        icon : `<rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>`
    },
    {
        href : '{{ route('student.modules') }}',
        label: 'Learning Modules',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                     C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                     C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13
                     C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>`
    },
    {
    href : '{{ route('student.subjects') }}',
    label: 'My Subjects',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14
                 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>`
},
    {
        href : '{{ route('student.quizzes') }}',
        label: 'Quizzes',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                     M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>`
    },
    {
        href : '{{ route('student.schedule') }}',
        label: 'My Schedule',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>`
    },
    {
        href : '{{ route('student.attendance') }}',
        label: 'Attendance',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>`
    },
    {
        href : '{{ route('student.grades') }}',
        label: 'My Grades',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0
                     V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0
                     V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>`
    },
  {
    href : '{{ route('student.messages') }}',
    label: 'Messages',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14
                 a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>`
},
    @if(auth()->user()->studentProfile)
    {
        href : '{{ route('student.parent-link') }}',
        label: 'Parent',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-6 0"/>`
    },
    @endif
    {
        href : '{{ route('student.face.register') }}',
        label: 'Face Registration',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M7 4H5a1 1 0 00-1 1v2m13-3h2a1 1 0 011 1v2M7 20H5a1 1 0 01-1-1v-2m13 3h2a1 1 0 001-1v-2
                     M9.5 14s1 1.2 2.5 1.2 2.5-1.2 2.5-1.2M9.5 9.5h.01M14.5 9.5h.01"/>`
    },

    @if(in_array((string) auth()->user()->grade_level, ['11','12']))
    {
        href : '{{ route('student.science-game') }}',
        label: 'Science Game',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`
    },
    @endif

    @if(!auth()->user()->studentEnrollment)
    {
        href : '{{ route('student.enroll') }}',
        label: 'Enrollment Form',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                     a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>`
    },
    @endif
@endif
];
    /* Build links */
    const nav     = document.getElementById('sidebarNav');
    const curPath = window.location.pathname.replace(/\/$/, '');

    NAV_ITEMS.forEach(item => {
        const cleanHref = new URL(item.href, window.location.href).pathname.replace(/\/$/, '');
        const isActive  = curPath === cleanHref || curPath.startsWith(cleanHref + '/');

        const a         = document.createElement('a');
        a.href          = item.href;

        if (isActive) a.classList.add('active');

        a.innerHTML = `
            <span class="nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     width="20" height="20" stroke-width="1.8" aria-hidden="true">
                    ${item.icon}
                </svg>
            </span>
            <span class="nav-label">${item.label}</span>
            <span class="sb-tooltip">${item.label}</span>
        `;

        a.addEventListener('click', function () {
            // Reflect active state immediately; the browser does a normal, reliable navigation.
            document.querySelectorAll('#sidebarNav a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
        // Prefetch on hover — the page is already loading before the click
        a.addEventListener('pointerenter', function () {
            if (this.dataset.prefetched) return;
            this.dataset.prefetched = '1';
            const l = document.createElement('link');
            l.rel = 'prefetch'; l.href = this.href;
            document.head.appendChild(l);
        }, { passive: true });
        nav.appendChild(a);
    });

    /* Collapse toggle. A page that needs the room (Messages) marks itself with data-focus-page:
       on a wide screen the menu tucks away while it is open, without changing the saved choice,
       and the menu button still brings it back. */
    const body = document.body;
    const CKEY = 'dp-lms-collapsed';
    const IS_MOBILE = () => window.matchMedia('(max-width: 768px)').matches;
    const savedCollapsed = () => { try { return localStorage.getItem(CKEY) === '1'; } catch (e) { return false; } };

    function applyFocusPage() {
        const focus = !IS_MOBILE() && !!document.querySelector('.main-content [data-focus-page]');
        if (focus === body.hasAttribute('data-focus')) return;
        body.toggleAttribute('data-focus', focus);
        requestAnimationFrame(() => body.classList.toggle('collapsed', focus || savedCollapsed()));   // next frame, so it slides
    }
    applyFocusPage();

    document.getElementById('collapseBtn').addEventListener('click', () => {
        if (IS_MOBILE()) {
            body.classList.toggle('mobile-open');
        } else {
            body.classList.toggle('collapsed');
            if (!body.hasAttribute('data-focus')) { try { localStorage.setItem(CKEY, body.classList.contains('collapsed') ? '1' : '0'); } catch (e) {} }
        }
    });

    // Unsent message drafts stay on this device only while signed in (school computers are shared)
    document.querySelectorAll('form[action$="/logout"]').forEach(f => f.addEventListener('submit', () => {
        try { Object.keys(localStorage).forEach(k => { if (k.indexOf('dp-draft:') === 0) localStorage.removeItem(k); }); } catch (e) {}
    }));
    const _backdrop = document.getElementById('sidebarBackdrop');
    if (_backdrop) _backdrop.addEventListener('click', () => body.classList.remove('mobile-open'));
    const _snav = document.getElementById('sidebarNav');
    if (_snav) _snav.addEventListener('click', (e) => {
        if (IS_MOBILE() && e.target.closest('a')) body.classList.remove('mobile-open');
    });

    /* Logout modal */
    const modal   = document.getElementById('logoutModal');
    document.getElementById('logoutTrigger').addEventListener('click', () => modal.classList.add('open'));
    document.getElementById('modalCancel').addEventListener('click',   () => modal.classList.remove('open'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') modal.classList.remove('open'); });

    /* Entrance handled by pure CSS (instant — no waiting for CDN/window.load) */


    /* ── Notifications ── */
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge    = document.getElementById('notifBadge');
const notifList     = document.getElementById('notifList');
const notifEmpty    = document.getElementById('notifEmpty');

// Escape text before it goes into innerHTML — material/quiz titles come
// from teachers, and an embedded <script>/onerror must never run.
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));

// url(): also works when served from an XAMPP subfolder (not an absolute "/notifications/...")
const APP_BASE  = @json(rtrim(url('/'), '/'));
const NOTIF_URL = {
    data:    @json(url('notifications/data')),
    read:    @json(url('notifications/__ID__/read')),
    readAll: @json(url('notifications/read-all')),
};
const CSRF = () => document.querySelector('meta[name=csrf-token]').content;
// Notification links are stored as "/student/…" — put them under the app's real base URL
const notifHref = (u) => (typeof u === 'string' && u.startsWith('/') && !u.startsWith('//')) ? APP_BASE + u : '#';

const TYPE_ICONS = {
    attendance_open:  { bg:'#eff6ff', stroke:'#2563eb', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    class_attendance: { bg:'#eff6ff', stroke:'#2563eb', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    arrival:    { bg:'#f0fdf4', stroke:'#16a34a', path:'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z' },
    quiz:       { bg:'#faf5ff', stroke:'#a855f7', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    module:     { bg:'#eff6ff', stroke:'#3b82f6', path:'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    announcement: { bg:'#fefce8', stroke:'#ca8a04', path:'M3.5 10.5v3a1 1 0 001 1H7l7 4.5V5L7 9.5H4.5a1 1 0 00-1 1zM7 14.5L8.2 20h2.3l-.9-4.6M17.5 9.5a3.5 3.5 0 010 5' },
    enrollment:   { bg:'#f0fdf4', stroke:'#16a34a', path:'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    meet:         { bg:'#eff6ff', stroke:'#2563eb', path:'M5 6h8.5A2.5 2.5 0 0116 8.5v7a2.5 2.5 0 01-2.5 2.5H5a2.5 2.5 0 01-2.5-2.5v-7A2.5 2.5 0 015 6zm11 4.5l5.5-3v9l-5.5-3' },
    assignment: { bg:'#fff7ed', stroke:'#f97316', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
};

function loadNotifs() {
    fetch(NOTIF_URL.data, { headers: { 'Accept': 'application/json' } })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(({ unread, items }) => {
            // Badge
            if (unread > 0) {
                notifBadge.style.display = 'block';
                notifBadge.textContent   = unread > 99 ? '99+' : unread;
            } else {
                notifBadge.style.display = 'none';
            }

            // List
            notifList.innerHTML = '';
            if (!items.length) {
                notifEmpty.style.display = 'block';
                return;
            }
            notifEmpty.style.display = 'none';

            items.forEach(n => {
                const ic = TYPE_ICONS[n.type] || TYPE_ICONS.assignment;
                const el = document.createElement('a');
                el.href  = notifHref(n.url);
                el.style.cssText = `
                    display:flex; align-items:flex-start; gap:12px;
                    padding:12px 16px; text-decoration:none;
                    border-bottom:1px solid var(--border);
                    transition:background .18s;
                `;
                el.onmouseenter = () => el.style.background = 'var(--accent-lt)';
                el.onmouseleave = () => el.style.background = '';
                el.innerHTML = `
                    <div style="width:36px;height:36px;border-radius:10px;
                        background:${ic.bg};display:flex;align-items:center;
                        justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="16" height="16" fill="none" stroke="${ic.stroke}"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${ic.path}"/>
                        </svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:12px;font-weight:700;color:var(--text-1);
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            ${esc(n.title)}
                        </p>
                        <p style="font-size:11px;color:var(--text-2);margin-top:2px;">
                            ${[n.subject, n.instructor].filter(Boolean).map(esc).join(' &nbsp;·&nbsp; ')}
                        </p>
                        <p style="font-size:10px;color:var(--text-3);margin-top:3px;">${esc(n.time)}</p>
                    </div>
                `;
                el.addEventListener('click', () => {
                    // keepalive: the link navigates away at once, and without it the browser
                    // cancels the request — the notification would never be marked read
                    fetch(NOTIF_URL.read.replace('__ID__', encodeURIComponent(n.id)), {
                        method: 'POST',
                        keepalive: true,
                        headers: { 'X-CSRF-TOKEN': CSRF() }
                    }).then(() => { if (el.getAttribute('href') === '#') loadNotifs(); }).catch(() => {});
                });
                notifList.appendChild(el);
            });
        })
        .catch(() => {});   // session expired / offline — ignore; the next poll tries again
}

// Toggle dropdown
notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = notifDropdown.style.display === 'block';
    notifDropdown.style.display = open ? 'none' : 'block';
    notifBtn.setAttribute('aria-expanded', open ? 'false' : 'true');
    if (!open) loadNotifs();
});

// Close on outside click
document.addEventListener('click', (e) => {
    if (!document.getElementById('notifWrapper').contains(e.target)) {
        notifDropdown.style.display = 'none';
        notifBtn.setAttribute('aria-expanded', 'false');
    }
});

// Mark all read
document.getElementById('markAllRead').addEventListener('click', () => {
    fetch(NOTIF_URL.readAll, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF() }
    }).then(() => loadNotifs()).catch(() => {});
});

// Poll every 30s for new notifs
loadNotifs();
setInterval(loadNotifs, 30000);
})();

// A signed-in page restored from the back/forward cache may belong to a session that has
// ended (logout) or show old data — ask the server again; it sends a signed-out visitor to login.
window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });
</script>
<script src="{{ asset('js/scroll-cue.js') }}?v={{ filemtime(public_path('js/scroll-cue.js')) }}" defer></script>
@include('partials.flash-toast')
</body>
</html>