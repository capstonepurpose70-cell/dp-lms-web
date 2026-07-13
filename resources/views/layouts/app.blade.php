<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DP-LMS') — Sto. Domingo NHS</title>
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
            /* Sidebar — deep navy */
            --sb-bg:            #0F172A;
            --sb-bg-hover:      #1E293B;
            --sb-active:        #2563EB;
            --sb-active-glow:   rgba(37,99,235,.30);
            --sb-border:        rgba(255,255,255,.06);
            --sb-text:          #64748B;
            --sb-text-hover:    #CBD5E1;
            --sb-text-active:   #FFFFFF;
            --sb-icon-bg:       rgba(255,255,255,.06);
            --sb-icon-hover:    rgba(37,99,235,.20);

            /* Page & nav */
            --nav-bg:           #FFFFFF;
            --bg:               #F1F5FF;
            --surface:          #FFFFFF;
            --border:           #E2E8F4;

            /* Accent */
            --accent:           #2563EB;
            --accent-dark:      #1D4ED8;
            --accent-lt:        #EFF6FF;
            --accent-glow:      rgba(37,99,235,.18);

            /* Text */
            --text-1:           #0F172A;
            --text-2:           #475569;
            --text-3:           #94A3B8;

            /* Status */
            --danger:           #EF4444;
            --green:            #16A34A;
            --green-lt:         #DCFCE7;
            --green-dark:       #14532D;
            --green-mid:        #22C55E;
            --green-glow:       rgba(22,163,74,.22);

            /* Layout */
            --sidebar-w:            248px;
            --sidebar-w-collapsed:   68px;
            --nav-h:                 62px;

            /* Radii & shadows */
            --r-sm:  8px;
            --r-md:  12px;
            --r-lg:  18px;
            --shadow-sm:  0 1px 4px rgba(15,23,42,.07);
            --shadow-md:  0 4px 18px rgba(15,23,42,.09), 0 1px 4px rgba(15,23,42,.04);
            --shadow-lg:  0 16px 48px rgba(15,23,42,.15), 0 4px 12px rgba(15,23,42,.07);
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
            box-shadow: var(--shadow-sm);
        }

        /* Brand zone — same width as sidebar */
        .nav-brand-zone {
            width: var(--sidebar-w); flex-shrink: 0;
            display: flex; align-items: center; padding: 0 14px; gap: 10px;
            height: calc(100% + 1px); /* covers the top-nav border line under the brand */
            background: var(--sb-bg);
            transition: width .32s cubic-bezier(.4,0,.2,1);
        }
        body.collapsed .nav-brand-zone { width: var(--sidebar-w-collapsed); }

        /* Collapse button — clean hamburger, no box */
        .collapse-btn {
            width: 36px; height: 36px; border-radius: var(--r-sm);
            border: none; cursor: pointer;
            background: transparent; color: #94A3B8; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: background .2s, color .2s;
        }
        .collapse-btn:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        /* Logo + school name */
        .nav-brand {
    display: flex; align-items: center; gap: 9px;
    white-space: nowrap; overflow: hidden;
    transition: opacity .22s, max-width .32s;
    max-width: 190px;
    flex: 1; min-width: 0;
}
        body.collapsed .nav-brand { opacity: 0; max-width: 0; pointer-events: none; }

        .logo-mark { flex-shrink: 0; width: 30px; height: 30px; }

        .brand-text { line-height: 1.2; }
        .brand-title { font-size: 12px; font-weight: 800; color: #FFFFFF; letter-spacing: .01em; }
        .brand-sub   { font-size: 9.5px; font-weight: 500; color: #475569; }

        /* Nav right */
        .nav-right { flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 10px; }

        .nav-icon-btn {
            position: relative; width: 38px; height: 38px; border-radius: var(--r-sm);
            border: 1px solid var(--border); background: var(--surface); cursor: pointer;
            display: flex; align-items: center; justify-content: center; color: var(--text-2);
            transition: background .2s, border-color .2s, color .2s, transform .15s, box-shadow .2s;
        }
        .nav-icon-btn:hover {
            background: var(--accent-lt); border-color: var(--accent);
            color: var(--accent); transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--accent-glow);
        }
        .notif-dot {
            position: absolute; top: 8px; right: 8px; width: 7px; height: 7px;
            border-radius: 50%; background: var(--danger); border: 2px solid var(--surface);
        }

        .user-chip {
            display: flex; align-items: center; gap: 9px;
            padding: 5px 14px 5px 5px; border-radius: 40px;
            border: 1px solid var(--border); background: var(--surface); cursor: default;
            transition: border-color .2s, box-shadow .2s;
        }
        .user-chip:hover { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-lt); }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 700; color: var(--text-1); line-height: 1.25; }
        .user-role { font-size: 11px; color: var(--text-3); font-weight: 500; }

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
    display: flex; flex-direction: column; overflow: hidden;
    transition: width .32s cubic-bezier(.4,0,.2,1);
    z-index: 90;
}
        body.collapsed .sidebar { width: var(--sidebar-w-collapsed); }
        body.collapsed .sidebar {
    overflow: visible;   /* let tooltips escape the collapsed sidebar */
}
body.collapsed .sidebar-nav {
    overflow: visible;
}

        /* Top gradient accent line */
   

        .sidebar-section-label {
            padding: 18px 14px 6px;
            font-size: 9.5px; font-weight: 700; letter-spacing: .1em;
            color: #374151; text-transform: uppercase;
            white-space: nowrap; overflow: hidden;
            transition: opacity .22s;
        }
        body.collapsed .sidebar-section-label { opacity: 0; }

        .sidebar-nav {
            padding: 6px 10px; flex: 1;
            overflow-y: auto; overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.07) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 3px; }

        /* ── Nav link ── */
      .sidebar-nav a {
    display: flex; align-items: center; gap: 11px;
    padding: 8px 8px 8px 6px; border-radius: var(--r-md);
    font-size: 13px; font-weight: 600; color: var(--sb-text);
    text-decoration: none; margin-bottom: 3px;
    white-space: nowrap; position: relative;
    overflow: visible;   /* ← allow tooltip to escape */
    transition: background .22s, color .22s, transform .18s;
}
        .sidebar-nav a:hover {
            background: var(--sb-bg-hover);
            color: var(--sb-text-hover);
            transform: translateX(3px);
        }
        .sidebar-nav a.active {
            background: var(--sb-active);
            color: var(--sb-text-active);
            font-weight: 700;
            box-shadow: 0 4px 18px var(--sb-active-glow);
            transform: none;
        }

        /* Icon pill */
        .nav-icon {
            width: 36px; height: 36px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--r-sm);
            background: var(--sb-icon-bg);
            transition: background .22s;
        }
        .sidebar-nav a:hover:not(.active) .nav-icon { background: var(--sb-icon-hover); }
        .sidebar-nav a.active .nav-icon { background: rgba(255,255,255,.18); }
        .nav-icon svg { width: 17px; height: 17px; stroke-width: 1.9; flex-shrink: 0; }

        /* Text label */
        .nav-label {
    white-space: nowrap; overflow: hidden;
    max-width: 160px; flex: 1; min-width: 0;
    transition: opacity .22s, max-width .32s;
}
        body.collapsed .nav-label { opacity: 0; max-width: 0; }

        /* Collapsed: center icons */
        body.collapsed .sidebar-nav a { justify-content: center; padding: 8px; }

        /* Tooltip */
        .sb-tooltip {
            position: absolute;
            left: calc(100% + 14px); top: 50%;
            transform: translateY(-50%) translateX(-6px);
            background: #1E293B; color: #F1F5F9;
            padding: 7px 12px; border-radius: var(--r-sm);
            font-size: 12px; font-weight: 600; white-space: nowrap;
            pointer-events: none; opacity: 0; visibility: hidden;
            transition: opacity .2s, visibility .2s, transform .2s;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,.06); z-index: 300;
        }
        .sb-tooltip::before {
            content: '';
            position: absolute; right: 100%; top: 50%; transform: translateY(-50%);
            border: 6px solid transparent; border-right-color: #1E293B;
        }
        body.collapsed .sidebar-nav a:hover .sb-tooltip {
            opacity: 1; visibility: visible;
            transform: translateY(-50%) translateX(0);
        }

        /* ══════════════════════════════
           LOGOUT — green zoom
        ══════════════════════════════ */
        .sidebar-footer {
            padding: 10px 10px 18px;
            border-top: 1px solid var(--sb-border);
        }

        .logout-btn {
            display: flex; align-items: center; gap: 11px;
            width: 100%; padding: 8px 8px 8px 6px; border-radius: var(--r-md);
            border: none; cursor: pointer; background: transparent;
            font-family: inherit; font-size: 13px; font-weight: 600;
            color: var(--sb-text); white-space: nowrap; position: relative;
            transition: background .25s, color .25s,
                        transform .28s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
        }
       .logout-btn:hover {
    background: rgba(22,163,74,.14);
    color: var(--green-mid);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px var(--green-glow);
}
        .logout-btn:active { transform: scale(.98); }

        .logout-icon {
            width: 36px; height: 36px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--r-sm);
            background: rgba(22,163,74,.14);
            transition: background .25s, transform .28s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
        }
   .logout-btn:hover .logout-icon {
    background: var(--green);
    transform: scale(1.04) rotate(-2deg);
    box-shadow: 0 4px 14px var(--green-glow);
}
        .logout-icon svg {
            width: 16px; height: 16px;
            stroke: var(--green-mid); stroke-width: 2.2;
            transition: stroke .25s, transform .25s;
        }
        .logout-btn:hover .logout-icon svg { stroke: #fff; transform: translateX(2px); }

        body.collapsed .logout-btn { justify-content: center; padding: 8px; }
        body.collapsed .logout-btn .nav-label { opacity: 0; max-width: 0; overflow: hidden; }

        /* ═══════════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════════ */
        .main-content {
            flex: 1; margin-left: var(--sidebar-w);
            padding: 30px 36px;
            transition: margin-left .32s cubic-bezier(.4,0,.2,1);
            min-width: 0;
            animation: contentIn .22s ease-out both;
        }
        body.collapsed .main-content { margin-left: var(--sidebar-w-collapsed); }

        /* Flash */
        .flash {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 16px; border-radius: var(--r-md);
            font-size: 13px; font-weight: 600; margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .flash-success { background: #F0FDF4; border: 1.5px solid #86EFAC; color: #15803D; }
        .flash-error   { background: #FEF2F2; border: 1.5px solid #FCA5A5; color: #991B1B; }

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
        .modal-cancel  { background: var(--accent-lt); color: var(--accent); border: 1.5px solid var(--border); }
        .modal-confirm { background: var(--danger); color: #fff; box-shadow: 0 4px 14px rgba(239,68,68,.25); }
        .modal-cancel:hover  { opacity: .85; transform: translateY(-1px); }
        .modal-confirm:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 22px rgba(239,68,68,.35); }

        /* ═══════════ MOBILE RESPONSIVE ═══════════ */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .3s cubic-bezier(.4,0,.2,1);
                width: 250px !important;
                box-shadow: 4px 0 24px rgba(0,0,0,.18);
            }
            body.mobile-open .sidebar { transform: translateX(0); }
            body.collapsed .sidebar { width: 250px !important; }
            body.collapsed .sidebar-nav a { justify-content: flex-start; padding: 10px 14px; }
            body.collapsed .nav-label { opacity: 1 !important; max-width: none !important; }
            body.collapsed .sidebar-section-label { opacity: 1 !important; }

            .main-content,
            body.collapsed .main-content {
                margin-left: 0 !important;
                padding: 18px 16px;
            }
            .nav-brand-zone,
            body.collapsed .nav-brand-zone { width: auto !important; }
            body.collapsed .nav-brand { opacity: 1 !important; max-width: 190px !important; pointer-events: auto !important; }

            .sidebar-backdrop {
                position: fixed; inset: var(--nav-h) 0 0 0; z-index: 85;
                background: rgba(15,23,42,.45); opacity: 0; pointer-events: none;
                transition: opacity .3s;
            }
            body.mobile-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }
        }
        /* ══════════════════════════════════════════════════════
           GLOBAL CONTENT RESPONSIVENESS — sakop LAHAT ng student/parent pages
        ══════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            /* Tables: naka-horizontal scroll sa phone imbes na sumabog */
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

            /* Headers / action rows: hayaang mag-wrap */
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
            /* 16px inputs = walang iOS auto-zoom; touch-friendly */
            .main-content input,
            .main-content select,
            .main-content textarea { font-size: 16px !important; min-height: 44px; }
            .main-content button,
            .main-content .btn { min-height: 44px; }
        }

        @media (min-width: 769px) {
            .sidebar-backdrop { display: none; }
        }
    </style>
</head>
<body>

{{-- ════════════════════════ TOP NAV ════════════════════════ --}}
<nav class="top-nav">
    <div class="nav-brand-zone">
        <button class="collapse-btn" id="collapseBtn" title="Toggle sidebar" aria-label="Toggle sidebar">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>

        {{-- Logo mark + school name --}}
        <div class="nav-brand">
            <svg class="logo-mark" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 3L5 9v10c0 8.5 6.4 16.4 15 18.4C28.6 35.4 35 27.5 35 19V9L20 3z"
                      fill="#1D4ED8"/>
                <path d="M20 3L5 9v10c0 8.5 6.4 16.4 15 18.4C28.6 35.4 35 27.5 35 19V9L20 3z"
                      fill="url(#lg1)" opacity=".45"/>
                <path d="M13 16v8.5M27 16v8.5M20 14v11" stroke="white" stroke-width="1.3" stroke-linecap="round" opacity=".5"/>
                <path d="M12 16c2.5-1.5 5.5-2 8-2s5.5.5 8 2" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
                <path d="M12 16l-1 8.5h18L28 16" stroke="white" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" opacity=".7"/>
                <circle cx="20" cy="10" r="2.5" fill="white" opacity=".8"/>
                <defs>
                    <linearGradient id="lg1" x1="5" y1="3" x2="35" y2="40" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#93C5FD"/>
                        <stop offset="1" stop-color="#1D4ED8"/>
                    </linearGradient>
                </defs>
            </svg>
            <div class="brand-text">
                <div class="brand-title">Sto. Domingo NHS</div>
                <div class="brand-sub">Learning Management System</div>
            </div>
        </div>
    </div>

    <div class="nav-right">
     <div style="position:relative;" id="notifWrapper">
    <button class="nav-icon-btn" id="notifBtn" title="Notifications">
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
        <div class="sidebar-section-label">Main Menu</div>
        {{-- Icons + links are injected by JS below so icons are ALWAYS guaranteed --}}
        <nav class="sidebar-nav" id="sidebarNav"></nav>
        <div class="sidebar-footer">
            <button class="logout-btn" id="logoutTrigger">
                <span class="logout-icon">
                    <svg fill="none" viewBox="0 0 24 24">
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
    /* ─────────────────────────────────────────────────────────────
       NAV ITEMS — defined here so icons are ALWAYS rendered
       correctly regardless of which child view extends this layout.
       Add / remove items here to update the sidebar globally.
    ───────────────────────────────────────────────────────────── */
const NAV_ITEMS = [
@php($navRole = strtolower(trim((string) auth()->user()->role)))
@if($navRole === 'parent')
    {
        href : '/parent/dashboard',
        label: 'Dashboard',
        icon : `<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>`
    },
    {
        href : '/parent/child-records',
        label: "Child's Records",
        icon : `<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/>`
    },
@else
    {
        href : '/student/dashboard',
        label: 'Dashboard',
        icon : `<rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>`
    },
    {
        href : '/student/modules',
        label: 'Learning Modules',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                     C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                     C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13
                     C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>`
    },
    {
    href : '/student/subjects',
    label: 'My Subjects',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14
                 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>`
},
    {
        href : '/student/quizzes',
        label: 'Quizzes',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                     M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>`
    },
    {
        href : '/student/grades',
        label: 'My Grades',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0
                     V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0
                     V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>`
    },
  {
    href : '/student/messages',
    label: 'Messages',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14
                 a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>`
},
    {
        href : '{{ route('student.face.register') }}',
        label: 'Register Face',
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
        href : '/student/enroll',
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
        const cleanHref = item.href.replace(/\/$/, '');
        const isActive  = curPath === cleanHref || curPath.startsWith(cleanHref + '/');

        const a         = document.createElement('a');
        a.href          = item.href;

        if (isActive) a.classList.add('active');

        a.innerHTML = `
            <span class="nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     width="17" height="17" stroke-width="1.9">
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
        // Prefetch on hover — parang SPA: preloaded na ang page bago pa i-click
        a.addEventListener('pointerenter', function () {
            if (this.dataset.prefetched) return;
            this.dataset.prefetched = '1';
            const l = document.createElement('link');
            l.rel = 'prefetch'; l.href = this.href;
            document.head.appendChild(l);
        }, { passive: true });
        nav.appendChild(a);
    });

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

    /* Entrance handled by pure CSS (instant — no waiting for CDN/window.load) */


    /* ── Notifications ── */
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge    = document.getElementById('notifBadge');
const notifList     = document.getElementById('notifList');
const notifEmpty    = document.getElementById('notifEmpty');

const TYPE_ICONS = {
    quiz:       { bg:'#faf5ff', stroke:'#a855f7', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    module:     { bg:'#eff6ff', stroke:'#3b82f6', path:'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    assignment: { bg:'#fff7ed', stroke:'#f97316', path:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
};

function loadNotifs() {
    fetch('/notifications/data')
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

            items.forEach(n => {
                const ic = TYPE_ICONS[n.type] || TYPE_ICONS.assignment;
                const el = document.createElement('a');
                el.href  = n.url;
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
                            ${n.title}
                        </p>
                        <p style="font-size:11px;color:var(--text-2);margin-top:2px;">
                            ${n.subject} &nbsp;·&nbsp; ${n.instructor}
                        </p>
                        <p style="font-size:10px;color:var(--text-3);margin-top:3px;">${n.time}</p>
                    </div>
                `;
                el.addEventListener('click', () => {
                    fetch(`/notifications/${n.id}/read`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                });
                notifList.appendChild(el);
            });
        });
}

// Toggle dropdown
notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = notifDropdown.style.display === 'block';
    notifDropdown.style.display = open ? 'none' : 'block';
    if (!open) loadNotifs();
});

// Close on outside click
document.addEventListener('click', (e) => {
    if (!document.getElementById('notifWrapper').contains(e.target)) {
        notifDropdown.style.display = 'none';
    }
});

// Mark all read
document.getElementById('markAllRead').addEventListener('click', () => {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(() => loadNotifs());
});

// Poll every 30s for new notifs
loadNotifs();
setInterval(loadNotifs, 30000);
})();
</script>
</body>
</html>