<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DP-LMS') — Sto. Domingo NHS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

            /* Layout */
            --sidebar-w:            264px;
            --sidebar-w-collapsed:   72px;
            --nav-h:                 64px;

            /* Radii & shadows */
            --r-sm:  8px;
            --r-md:  12px;
            --r-lg:  16px;
            --shadow-sm:  0 1px 2px rgba(15,23,42,.05);
            --shadow-md:  0 4px 16px rgba(15,23,42,.08), 0 1px 3px rgba(15,23,42,.05);
            --shadow-lg:  0 16px 48px rgba(15,23,42,.14), 0 4px 12px rgba(15,23,42,.06);
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        @keyframes skeletonFade {
            from { opacity: 0; }
            to   { opacity: 1; }
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
            -moz-osx-font-smoothing: grayscale;
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
            transition: width .28s cubic-bezier(.4,0,.2,1), padding .28s cubic-bezier(.4,0,.2,1);
        }
        body.collapsed .nav-brand-zone { width: var(--sidebar-w-collapsed); padding: 0 17px; }
        body.collapsed .logo-mark { width: 38px; height: 38px; }

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
        .logo-mark { flex-shrink: 0; width: 38px; height: 38px; display: block; }
        .brand-text {
            min-width: 0; white-space: nowrap; overflow: hidden;
            transition: opacity .18s ease, max-width .28s cubic-bezier(.4,0,.2,1);
            max-width: 196px;
        }
        .brand-title {
            display: block; font-size: 15px; font-weight: 800; color: var(--text-1);
            letter-spacing: -.01em; line-height: 1.2;
        }
        .brand-sub { display: block; font-size: 12px; font-weight: 500; color: var(--text-3); line-height: 1.3; margin-top: 1px; }
        body.collapsed .brand-text { opacity: 0; max-width: 0; }

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
            font-size: 13px; font-weight: 800; flex-shrink: 0;
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
            transition: width .28s cubic-bezier(.4,0,.2,1);
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
            min-height: 42px; padding: 0 12px; border-radius: var(--r-sm);
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
            max-width: 180px; flex: 1; min-width: 0;
            transition: opacity .18s ease, max-width .28s cubic-bezier(.4,0,.2,1);
        }
        body.collapsed .nav-label { opacity: 0; max-width: 0; }
        body.collapsed .sidebar-nav a { justify-content: center; padding: 0; }
        body.collapsed .sidebar-nav a.active::before { display: none; }

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
            width: 100%; min-height: 42px; padding: 0 12px; border-radius: var(--r-sm);
            border: none; cursor: pointer; background: transparent; text-align: left;
            font-family: inherit; font-size: 14px; font-weight: 600;
            color: var(--sb-text); white-space: nowrap; position: relative;
            transition: background-color .15s ease, color .15s ease;
        }
        .logout-btn:hover { background: #FEF2F2; color: #B91C1C; }
        .logout-icon { width: 20px; height: 20px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .logout-icon svg { width: 20px; height: 20px; stroke: var(--sb-icon); stroke-width: 1.8; transition: stroke .15s ease; }
        .logout-btn:hover .logout-icon svg { stroke: #B91C1C; }
        body.collapsed .logout-btn { justify-content: center; padding: 0; }
        body.collapsed .logout-btn .nav-label { opacity: 0; max-width: 0; overflow: hidden; }

        /* ═══════════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════════ */
        .main-content {
            flex: 1; margin-left: var(--sidebar-w);
            padding: 32px 40px 48px;
            transition: margin-left .28s cubic-bezier(.4,0,.2,1);
            min-width: 0;
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

        /* Pagination (Paginator::defaultView = pagination::default) */
        ul.pagination { display:flex; flex-wrap:wrap; gap:6px; list-style:none; margin:0; padding:0; align-items:center; }
        ul.pagination li > a, ul.pagination li > span {
            display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px; padding:0 10px;
            border:1px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text-2);
            text-decoration:none; font-weight:600; font-size:13px; transition:border-color .15s, color .15s;
        }
        ul.pagination li > a:hover { border-color:var(--accent); color:var(--accent); }
        ul.pagination li.active > span { background:var(--accent-dark); border-color:var(--accent-dark); color:#fff; }
        ul.pagination li.disabled > span { opacity:.45; cursor:default; }

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
            transition: background-color .15s ease, border-color .15s ease, transform .1s ease;
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
            body.collapsed .sidebar-nav a,
            body.collapsed .logout-btn { justify-content: flex-start; padding: 0 12px; }
            body.collapsed .sidebar-nav a.active::before { display: block; }
            body.collapsed .nav-label,
            body.collapsed .logout-btn .nav-label { opacity: 1 !important; max-width: none !important; }
            body.collapsed .sb-tooltip { display: none; }

            .main-content,
            body.collapsed .main-content {
                margin-left: 0 !important;
                padding: 18px 16px;
            }
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
        @media (min-width: 769px) {
            .sidebar-backdrop { display: none; }
        }

        /* Notification dropdown (opens on click) */
        @keyframes notifDropIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: none; }
        }
        @keyframes notifItemIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        #notifDropdown { transform-origin: top right; }

        /* Press feedback */
        .collapse-btn:active,
        .nav-icon-btn:active { transform: scale(.96); }
        .modal-cancel:active,
        .modal-confirm:active { transform: scale(.98); }

        /* ══════════════════════════════════════════════════════
           PAGE SHELL — shared by every teacher page so widths, headers,
           back links and buttons are the same everywhere
        ══════════════════════════════════════════════════════ */
        .tp-page { max-width: 1040px; margin: 0 auto; }
        .tp-page.tp-narrow { max-width: 760px; }
        .tp-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px 16px; margin-bottom: 20px; }
        .tp-title { font-size: 24px; font-weight: 800; color: var(--text-1); letter-spacing: -.01em; line-height: 1.25; }
        .tp-sub { font-size: 14px; color: var(--text-2); margin-top: 4px; line-height: 1.55; max-width: 68ch; }
        .tp-back { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; margin: -6px 0 2px; font-size: 13.5px; font-weight: 600; color: var(--text-2); text-decoration: none; }
        .tp-back:hover { color: var(--accent-dark); }
        .tp-back svg { width: 16px; height: 16px; flex-shrink: 0; }
        .tp-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }
        .tp-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px;
            border-radius: var(--r-md); border: 1px solid transparent; background: var(--accent); color: #fff;
            font-family: inherit; font-size: 14px; font-weight: 700; line-height: 1; text-decoration: none; cursor: pointer; white-space: nowrap;
        }
        .tp-btn:hover { background: var(--accent-dark); }
        .tp-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
        .tp-btn.ghost { background: var(--surface); color: var(--text-1); border-color: var(--border); }
        .tp-btn.ghost:hover { background: #F8FAFF; border-color: #CBD5E1; }
        .tp-btn.danger { background: var(--surface); color: #B91C1C; border-color: #FECACA; }
        .tp-btn.danger:hover { background: #FEF2F2; }
        .tp-btn.sm { min-height: 38px; padding: 0 14px; font-size: 13px; }
        .tp-btn[disabled] { opacity: .6; cursor: progress; }
        .tp-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .tp-pill.ok    { background: #DCFCE7; color: #166534; }
        .tp-pill.warn  { background: #FEF3C7; color: #92400E; }
        .tp-pill.bad   { background: #FEE2E2; color: #991B1B; }
        .tp-pill.muted { background: #F1F5F9; color: #475569; }
        .tp-pill.info  { background: var(--accent-lt); color: var(--accent-dark); }
        .tp-empty { padding: 40px 20px; text-align: center; font-size: 14px; color: var(--text-2); line-height: 1.55; }
        .tp-empty b { display: block; font-size: 15px; color: var(--text-1); margin-bottom: 4px; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        /* Keeps hidden labels (e.g. an "Action" column header) inside a table that scrolls sideways */
        .main-content table { position: relative; }

        /* ══════════════════════════════════════════════════════
           GLOBAL CONTENT RESPONSIVENESS — every teacher page
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

            /* Inline 3-column grids (stat cards, form rows) → 1 column */
            .main-content [style*="repeat(3,1fr)"],
            .main-content [style*="repeat(3, 1fr)"],
            .main-content [style*="repeat(4,1fr)"],
            .main-content [style*="repeat(4, 1fr)"] {
                grid-template-columns: 1fr !important;
            }
            /* "sidebar + content" grids (e.g. 320px 1fr) → stacked */
            .main-content [style*="320px 1fr"] {
                grid-template-columns: 1fr !important;
            }

            /* Panel headers / action rows wrap */
            .main-content .panel-header,
            .main-content .page-header,
            .main-content .gb-actions {
                flex-wrap: wrap;
                gap: 10px;
            }

            /* Long words/links: never overflow */
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
            .main-content .btn,
            .main-content .tp-btn { min-height: 44px; }
        }

        /* Log out: <button> centers its text by default — align with the links above */
        .logout-btn { text-align: left; }

        /* Phones: avatar only in the top bar (the name overflowed past the screen edge) */
        @media (max-width: 600px) {
            .top-nav { padding-right: 12px; }
            .user-chip { padding: 3px; }
            .user-chip > div:last-child { display: none; }
        }

        /* Keyboard focus — visible on the dark sidebar and on the light content */
        :focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        .sidebar :focus-visible, .nav-brand-zone :focus-visible { outline-color: #93C5FD; }

        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
        }
    </style>
</head>
<body>

{{-- ════════════════════════ TOP NAV ════════════════════════ --}}
<nav class="top-nav">
    <div class="nav-brand-zone">
        {{-- Logo mark + school name --}}
        <a class="nav-brand" href="{{ route('teacher.dashboard') }}" aria-label="Sto. Domingo NHS, go to the dashboard">
            <svg class="logo-mark" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M20 2.5L5 8.5v10.2c0 8.7 6.4 16.7 15 18.8 8.6-2.1 15-10.1 15-18.8V8.5L20 2.5z" fill="#1D4ED8"/>
                <path d="M12 15.5c2.6-1.4 5.3-2 8-2s5.4.6 8 2v9.5c-2.6-1.4-5.3-2-8-2s-5.4.6-8 2v-9.5z" fill="#fff"/>
                <path d="M20 13.5v9.5" stroke="#1D4ED8" stroke-width="1.4" stroke-linecap="round"/>
                <circle cx="20" cy="9.3" r="1.9" fill="#fff"/>
            </svg>
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
    <button class="nav-icon-btn" id="notifBtn" title="Notifications" aria-label="Notifications" aria-haspopup="true" aria-expanded="false" aria-controls="notifDropdown">
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
        width:320px; background:var(--surface); border-radius:var(--r-lg);
        border:1px solid var(--border); box-shadow:var(--shadow-lg);
        z-index:500; overflow:hidden;
    ">
        <div style="padding:14px 16px 10px; border-bottom:1px solid var(--border);
                    display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:13px; font-weight:800; color:var(--text-1);">Notifications</span>
            <button id="markAllRead" style="font-size:11px; color:var(--accent);
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
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
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
        {{-- Rendered on the server so the menu is there on the first paint; the script below
             only adds in-page navigation to these links --}}
        @php
            $navItems = [
                ['teacher.dashboard', 'Dashboard', '<rect x="3" y="3" width="7" height="7" rx="1.5"/> <rect x="14" y="3" width="7" height="7" rx="1.5"/> <rect x="3" y="14" width="7" height="7" rx="1.5"/> <rect x="14" y="14" width="7" height="7" rx="1.5"/>'],
                ['teacher.gradebook.index', 'Gradebook', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0 V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0 V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                ['teacher.materials.index', 'Materials', '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
                ['teacher.assignments.index', 'Quizzes', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2 M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2 m-3 7h3m-3 4h3m-5-4h.01M9 16h.01"/>'],
                ['teacher.announcements.index', 'Announcements', '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 16h2a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 5.882A2 2 0 0112.83 4h.842a2 2 0 011.995 1.858L17 16H8.343M11 5.882L8.343 16"/>'],
                ['teacher.students.index', 'Students', '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857 M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ['teacher.enrollments.index', 'Enrollments', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                ['teacher.attendance.index', 'Attendance', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
                ['teacher.face-kiosk.index', 'Face Registration', '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2 M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0"/>'],
                ['teacher.messages', 'Messages', '<path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>'],
            ];
        @endphp
        <nav class="sidebar-nav" id="sidebarNav" aria-label="Main">
            @foreach ($navItems as [$navRoute, $navLabel, $navIcon])
                @php $navHref = route($navRoute); $navPath = trim(parse_url($navHref, PHP_URL_PATH) ?? '', '/'); @endphp
                <a href="{{ $navHref }}" @class(['active' => request()->is($navPath, $navPath . '/*')]) @if (request()->is($navPath, $navPath . '/*')) aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20" stroke-width="1.8">{!! $navIcon !!}</svg>
                    </span>
                    <span class="nav-label">{{ $navLabel }}</span>
                    <span class="sb-tooltip" aria-hidden="true">{{ $navLabel }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <button class="logout-btn" id="logoutTrigger" type="button">
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
        @if(session('success'))
            <div class="flash flash-success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif
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
            <button class="modal-cancel" id="modalCancel">Cancel</button>
            <form method="POST" action="{{ route('logout') }}" style="flex:1;display:flex;">
                @csrf
                <button type="submit" class="modal-confirm" style="width:100%;">Log out</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    /* In-page navigation for the menu links (rendered by Blade above) */
    const pathOf = (u) => new URL(u, window.location.href).pathname.replace(/\/$/, '');

    function syncActive() {
        const cur = pathOf(window.location.href);
        document.querySelectorAll('#sidebarNav a').forEach(l => {
            const href = pathOf(l.href);
            const on = cur === href || cur.startsWith(href + '/');
            l.classList.toggle('active', on);
            if (on) l.setAttribute('aria-current', 'page'); else l.removeAttribute('aria-current');
        });
    }

    document.querySelectorAll('#sidebarNav a').forEach(a => {
        a.addEventListener('click', function (e) {
            // Ctrl/Cmd/Shift/middle click → let the browser open a new tab/window
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
            e.preventDefault();

            // Full navigation for pages with forms (create, edit)
            if (this.href.includes('/create') || this.href.includes('/edit')) {
                window.location.href = this.href;
                return;
            }
            loadPage(this.href, true);
        });
    });
    syncActive();

    /* ─────────────────────────────────────────────────────────────
       In-page navigation: fetch the page, swap only .main-content.
       Falls back to a normal page load whenever the answer is not a
       teacher page (session expired → login, error, JSON), so it never
       hangs on the skeleton. Back/Forward uses the same loader so the
       page's scripts run.
    ───────────────────────────────────────────────────────────── */
    let navSeq = 0;

    function loadPage(url, push) {
        const seq     = ++navSeq;
        const curMain = document.querySelector('.main-content');
        if (push) {
            document.querySelectorAll('#sidebarNav a').forEach(l => l.classList.toggle('active', l.href === url));
        }
        showSkeleton(curMain);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(r => {
                if (!r.ok || r.redirected) throw new Error('not a page');
                return r.text();
            })
            .then(html => {
                if (seq !== navSeq) return;   // a newer click won
                const doc     = new DOMParser().parseFromString(html, 'text/html');
                const newMain = doc.querySelector('.main-content');
                if (!newMain) throw new Error('not a page');

                // Each inline script in its own block: a second visit would otherwise
                // throw "Identifier has already been declared" on top-level const/let.
                newMain.querySelectorAll('script:not([src])').forEach(s => {
                    if (!s.type || s.type === 'text/javascript') s.textContent = '{\n' + s.textContent + '\n}';
                });

                if (push) history.pushState(null, '', url);
                if (doc.title) document.title = doc.title;

                curMain.style.transition = 'none';
                curMain.style.opacity = '0';
                curMain.innerHTML = '';
                const range = document.createRange();
                range.selectNode(curMain);
                curMain.appendChild(range.createContextualFragment(newMain.innerHTML));   // runs the page's scripts
                syncActive();
                window.scrollTo(0, 0);
                requestAnimationFrame(() => {
                    curMain.style.transition = 'opacity .18s ease';
                    curMain.style.opacity    = '1';
                });
            })
            .catch(() => { if (seq === navSeq) window.location.href = url; });
    }

    window.addEventListener('popstate', () => loadPage(window.location.href, false));

    function showSkeleton(curMain) {
    curMain.style.opacity = '0';
    curMain.style.transition = 'none';

    // Show skeleton
    curMain.innerHTML = `
        <div style="animation: skeletonFade .3s ease both;">
            <div style="height:32px;width:220px;background:#e2e8f0;border-radius:8px;margin-bottom:24px;
                        animation:shimmer 1.4s ease-in-out infinite;background-size:200% 100%;
                        background-image:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);"></div>
            <div style="background:#fff;border-radius:16px;border:1.5px solid #f1f5f9;overflow:hidden;">
                ${[1,2,3,4,5].map(() => `
                    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:14px;">
                        <div style="width:40px;height:40px;border-radius:10px;flex-shrink:0;
                                    background-image:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);
                                    background-size:200% 100%;animation:shimmer 1.4s ease-in-out infinite;"></div>
                        <div style="flex:1;">
                            <div style="height:13px;width:60%;border-radius:6px;margin-bottom:6px;
                                        background-image:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);
                                        background-size:200% 100%;animation:shimmer 1.4s ease-in-out infinite;"></div>
                            <div style="height:11px;width:35%;border-radius:6px;
                                        background-image:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);
                                        background-size:200% 100%;animation:shimmer 1.4s ease-in-out infinite;"></div>
                        </div>
                        <div style="height:22px;width:64px;border-radius:20px;
                                    background-image:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);
                                    background-size:200% 100%;animation:shimmer 1.4s ease-in-out infinite;"></div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    curMain.style.opacity = '1';
    }

    /* Collapse toggle */
    const body = document.body;
    const CKEY = 'dp-lms-collapsed';
    if (localStorage.getItem(CKEY) === '1') body.classList.add('collapsed');

    const IS_MOBILE = () => window.matchMedia('(max-width: 768px)').matches;
    document.getElementById('collapseBtn').addEventListener('click', () => {
        if (IS_MOBILE()) {
            body.classList.toggle('mobile-open');
        } else {
            body.classList.toggle('collapsed');
            localStorage.setItem(CKEY, body.classList.contains('collapsed') ? '1' : '0');
        }
    });
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

    /* ── Notifications ── */
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge    = document.getElementById('notifBadge');
const notifList     = document.getElementById('notifList');
const notifEmpty    = document.getElementById('notifEmpty');
// url(): also works when served from an XAMPP subfolder (not an absolute "/teacher/...")
const NOTIF_URL = {
    data:    @json(url('teacher/notifications/data')),
    read:    @json(url('teacher/notifications/__ID__/read')),
    readAll: @json(url('teacher/notifications/read-all')),
};
// Escape before it goes into innerHTML (XSS) — same as layouts/app.blade.php
const tEsc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));

const TYPE_ICONS = {
    quiz:       { bg:'#faf5ff', stroke:'#a855f7', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    module:     { bg:'#eff6ff', stroke:'#3b82f6', path:'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    assignment: { bg:'#fff7ed', stroke:'#f97316', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
};

function loadNotifs() {
    fetch(NOTIF_URL.data, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
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

            items.forEach((n, i) => {
                const ic = TYPE_ICONS[n.type] || TYPE_ICONS.assignment;
                const el = document.createElement('a');
                el.href  = n.url;
                el.style.cssText = `
                    display:flex; align-items:flex-start; gap:12px;
                    padding:12px 16px; text-decoration:none;
                    border-bottom:1px solid var(--border);
                    transition:background .18s;
                `;
                // ✦ stagger each item in for a smoother reveal
                el.style.animation = 'notifItemIn .26s ease both';
                el.style.animationDelay = (i * 0.035) + 's';
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
                            ${tEsc(n.title)}
                        </p>
                        <p style="font-size:11px;color:var(--text-2);margin-top:2px;">
                            ${tEsc(n.subject)} &nbsp;·&nbsp; ${tEsc(n.instructor)}
                        </p>
                        <p style="font-size:10px;color:var(--text-3);margin-top:3px;">${tEsc(n.time)}</p>
                    </div>
                `;
                el.addEventListener('click', () => {
                   fetch(NOTIF_URL.read.replace('__ID__', encodeURIComponent(n.id)), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                });
                notifList.appendChild(el);
            });
        });
}

// Toggle dropdown (with smooth entrance animation)
notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = notifDropdown.style.display === 'block';
    notifBtn.setAttribute('aria-expanded', open ? 'false' : 'true');
    if (open) {
        notifDropdown.style.display = 'none';
    } else {
        notifDropdown.style.display = 'block';
        notifDropdown.style.animation = 'notifDropIn .24s cubic-bezier(.34,1.56,.64,1) both';
        loadNotifs();
    }
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
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(() => loadNotifs());
});

// Poll every 30s for new notifs
loadNotifs();
setInterval(loadNotifs, 30000);
})();

// A signed-in page restored from the back/forward cache may belong to a session that has
// ended (logout) or show old data — ask the server again; it sends a signed-out visitor to login.
window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });
</script>
</body>
</html>