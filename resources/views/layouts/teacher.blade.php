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

        @keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
@keyframes skeletonFade {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
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
            opacity: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ═══════════════════════════════════════════
           TOP NAV
        ═══════════════════════════════════════════ */
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--nav-h);
            background: var(--nav-bg);
   
            display: flex; align-items: center; padding: 0 24px 0 0;
         
        }

        /* Brand zone — same width as sidebar */
        .nav-brand-zone {
            width: var(--sidebar-w); flex-shrink: 0;
            display: flex; align-items: center; padding: 0 14px; gap: 10px;
            height: 100%;
            background: var(--sb-bg);
            transition: width .32s cubic-bezier(.4,0,.2,1);
        }
        body.collapsed .nav-brand-zone { width: var(--sidebar-w-collapsed); }

        /* Collapse button */
        .collapse-btn {
            width: 34px; height: 34px; border-radius: var(--r-sm);
            border: 1px solid rgba(255,255,255,.10); cursor: pointer;
            background: rgba(255,255,255,.06); color: #64748B; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: background .2s, color .2s, transform .18s, box-shadow .2s;
        }
        .collapse-btn:hover {
            background: var(--sb-active); border-color: var(--sb-active);
            color: #fff; box-shadow: 0 4px 14px var(--sb-active-glow);
            transform: scale(1.07);
        }
        .collapse-btn svg { transition: transform .36s cubic-bezier(.4,0,.2,1); }
        body.collapsed .collapse-btn svg { transform: rotate(180deg); }

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
            opacity: 0; min-width: 0;
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
        @media (min-width: 769px) {
            .sidebar-backdrop { display: none; }
        }

        /* ═══════════════════════════════════════════
           ✦ DESIGN ENHANCEMENTS  (smoother motion & polish)
           Appended last so these rules win the cascade —
           nothing above is modified, so existing logic is safe.
        ═══════════════════════════════════════════ */
        @keyframes notifDropIn {
            from { opacity: 0; transform: translateY(-10px) scale(.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes notifItemIn {
            from { opacity: 0; transform: translateX(8px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.45); }
            50%      { box-shadow: 0 0 0 5px rgba(239,68,68,0); }
        }

        /* Notification dropdown — entrance origin + subtle badge pulse */
        #notifDropdown { transform-origin: top right; }
        #notifBadge    { animation: badgePulse 2s ease-in-out infinite; }

        /* Active nav item — animated left accent bar */
        .sidebar-nav a.active::before {
            content: '';
            position: absolute; left: -3px; top: 50%;
            width: 3px; height: 0; border-radius: 3px;
            background: #fff; transform: translateY(-50%);
            animation: navBarIn .32s cubic-bezier(.34,1.56,.64,1) forwards;
        }
        @keyframes navBarIn { to { height: 22px; } }
        body.collapsed .sidebar-nav a.active::before { display: none; }

        /* Tactile press feedback on every interactive control */
        .collapse-btn:active,
        .nav-icon-btn:active,
        .user-chip:active { transform: scale(.94); }
        .modal-cancel:active,
        .modal-confirm:active { transform: scale(.97); }

        /* Nav icon micro-interaction on hover */
        .nav-icon { transition: background .22s, transform .22s cubic-bezier(.34,1.56,.64,1); }
        .sidebar-nav a:hover .nav-icon { transform: scale(1.06); }

        /* Springy nav-right buttons */
        .nav-icon-btn {
            transition: background .2s, border-color .2s, color .2s,
                        transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
        }

        /* Honor reduced-motion preferences */
        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
        }
    </style>
</head>
<body>

{{-- ════════════════════════ TOP NAV ════════════════════════ --}}
<nav class="top-nav">
    <div class="nav-brand-zone">
        <button class="collapse-btn" id="collapseBtn" title="Toggle sidebar" aria-label="Toggle sidebar">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M15 18l-6-6 6-6"/>
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
    {
        href : '/teacher/dashboard',
        label: 'Dashboard',
        icon : `<rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>`
    },
     {
        href : '/teacher/gradebook',
        label: 'Gradebook',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0
                     V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0
                     V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>`
    },
     {
        href : '/teacher/materials',
        label: 'Materials',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1
                     1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>`
    },
    {
        href : '/teacher/assignments',
        label: 'Quizzes',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                     M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2
                     m-3 7h3m-3 4h3m-5-4h.01M9 16h.01"/>`
    },
    {
        href : '/teacher/announcements',
        label: 'Announcements',
        icon : `<path stroke-linecap="round" stroke-linejoin="round"
                  d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 16h2a2 2 0
                     002-2v-4a2 2 0 00-2-2h-2.343M11 5.882A2 2 0 0112.83 4h.842a2 2 0
                     011.995 1.858L17 16H8.343M11 5.882L8.343 16"/>`
    },

    {
    href : '/teacher/students',
    label: 'Students',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                 M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>`
},

{
    href : '/teacher/attendance',
    label: 'Attendance',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0
                 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0
                 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>`
},

{
    href : '/teacher/messages',
    label: 'Messages',
    icon : `<path stroke-linecap="round" stroke-linejoin="round"
              d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0
                 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6
                 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>`
},

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

       /* REPLACE with: */
a.addEventListener('click', function (e) {
    e.preventDefault();
    const url = this.href;

    // ✅ Full navigation for pages with forms (create, edit)
    const isFormPage = url.includes('/create') || url.includes('/edit');
    if (isFormPage) {
        window.location.href = url;
        return;
    }

    document.querySelectorAll('#sidebarNav a').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
    history.pushState(null, '', url);

    const curMain = document.querySelector('.main-content');
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

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('.main-content');
            if (newMain && curMain) {
                curMain.style.opacity = '0';
                curMain.style.transition = 'none';
                curMain.innerHTML = '';
                const range = document.createRange();
                range.selectNode(curMain);
                curMain.appendChild(range.createContextualFragment(newMain.innerHTML));
                requestAnimationFrame(() => {
                    curMain.style.transition = 'opacity .18s ease';
                    curMain.style.opacity    = '1';
                });
            }
        })
        .catch(() => { window.location.href = url; });
});
        nav.appendChild(a);
    });

    // Handle browser back/forward
    window.addEventListener('popstate', () => {
        const url = window.location.pathname;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const parser  = new DOMParser();
                const doc     = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('.main-content');
                const curMain = document.querySelector('.main-content');
                if (newMain && curMain) curMain.innerHTML = newMain.innerHTML;

                // Sync active link
                const curPath = window.location.pathname.replace(/\/$/, '');
                document.querySelectorAll('#sidebarNav a').forEach(l => {
                    const href = l.getAttribute('href').replace(/\/$/, '');
                    l.classList.toggle('active', curPath === href || curPath.startsWith(href + '/'));
                });
            });
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

    /* GSAP entrance */
    window.addEventListener('load', () => {
        if (typeof gsap === 'undefined') {
            body.style.opacity = '1';
            document.querySelector('.main-content').style.opacity = '1';
            return;
        }
        gsap.timeline({ defaults: { ease: 'power3.out' } })
            .to('body',            { opacity: 1,                          duration: .28 })
            .from('.top-nav',      { y: -22, opacity: 0,                  duration: .38 }, '-=.14')
            .from('.sidebar',      { x: -18, opacity: 0,                  duration: .38 }, '-=.3')
            .to('.main-content',   { opacity: 1,                          duration: .42 }, '-=.26')
            .from('#sidebarNav a', { x: -8, opacity: 0, stagger: .06,    duration: .28 }, '-=.32');
    });


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
    fetch('/teacher/notifications/data')
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
                            ${n.title}
                        </p>
                        <p style="font-size:11px;color:var(--text-2);margin-top:2px;">
                            ${n.subject} &nbsp;·&nbsp; ${n.instructor}
                        </p>
                        <p style="font-size:10px;color:var(--text-3);margin-top:3px;">${n.time}</p>
                    </div>
                `;
                el.addEventListener('click', () => {
                   fetch(`/teacher/notifications/${n.id}/read`,{
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
    }
});

// Mark all read
document.getElementById('markAllRead').addEventListener('click', () => {
    fetch('/teacher/notifications/read-all',{
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(() => loadNotifs());
});

// Poll every 30s for new notifs
loadNotifs();
setInterval(loadNotifs, 30000);
})();
</script>
@include('partials.flash-toast')
</body>
</html>