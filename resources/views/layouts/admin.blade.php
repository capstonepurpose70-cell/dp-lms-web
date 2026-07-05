<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        (function () {
            try {
                if (localStorage.getItem('admin-theme') === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
    <style>
        /* ═══════════ DARK MODE ═══════════ */
        html[data-theme="dark"] {
            --surface-page:    #0b1220;
            --surface-card:    #131c2e;
            --surface-nav:     #0f1827;
            --surface-sidebar: #0a1422;
            --white:           #131c2e;
            --slate-50:        #0f1827;
            --slate-200:       #2a3850;
            --slate-400:       #6b788d;
            --slate-500:       #93a0b4;
            --slate-700:       #c2cbd9;
            --slate-900:       #e8eef7;
            --border-default:  #25324a;
            --border-nav:      rgba(255,255,255,0.07);
            --um-blue-50:      #14233c;
            --um-blue-100:     #1b2f4d;
            /* light surfaces used by hover states -> darken so text stays visible */
            --slate-100:       #1b2740;
            --blue-50:         #14233c;
            --blue-100:        #1b2f4d;
            --blue-700:        #85b8f7;  /* hover text -> light blue (readable on dark) */
        }
        /* Keep sidebar brand + nav hover/active text WHITE (they use var(--white) which is now dark) */
        [data-theme="dark"] .sidebar-brand-name,
        [data-theme="dark"] .nav-link:hover,
        [data-theme="dark"] .nav-link.active { color: #ffffff !important; }
        /* Inputs/search readable on dark */
        [data-theme="dark"] input,
        [data-theme="dark"] textarea,
        [data-theme="dark"] select { color: var(--slate-900); }
        [data-theme="dark"] ::placeholder { color: var(--slate-400); }
        /* smooth, GPU-cheap transitions (targeted, NOT global * — avoids lag) */
        html, body, .topbar, .main-content, .app-shell, .sidebar,
        .notif-panel, .settings-panel, .user-chip, .nav-link, .icon-btn {
            transition: background-color .28s ease, color .28s ease, border-color .28s ease;
        }
        /* dark-toggle icon swap */
        #darkToggle .dt-moon { display: none; }
        [data-theme="dark"] #darkToggle .dt-sun  { display: none; }
        [data-theme="dark"] #darkToggle .dt-moon { display: block; }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — DP-LMS</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
         
        :root {
            --font-ui:      'Plus Jakarta Sans', sans-serif;
            --font-display: 'Outfit', sans-serif;

            /* Idagdag sa :root ng admin.blade.php */
--um-blue-500:   #2478e4;
--um-blue-600:   #1a62be;
--um-blue-50:    #f0f6ff;
--um-blue-100:   #ddeafa;
--um-blue-200:   #bcd6fc;
--um-green-500:  #059669;
--um-green-600:  #047857;
--um-green-glow: rgba(5, 150, 105, 0.18);
--um-amber-500:  #d97706;
--um-purple-500: #8b5cf6;
--um-red-600:    #dc2626;

            /* Blues */
            --blue-950: #030d1a;
            --blue-900: #07203f;
            --blue-800: #0c3566;
            --blue-700: #124a8e;
            --blue-600: #1a62be;
            --blue-500: #2478e4;
            --blue-400: #4d96f0;
            --blue-300: #85b8f7;
            --blue-200: #bcd6fc;
            --blue-100: #ddeafa;
            --blue-50:  #f0f6ff;

            /* Neutrals */
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
            --white:     #ffffff;

            /* Semantic */
            --danger:        #dc2626;
            --danger-light:  #fef2f2;
            --success:       #059669;
            --success-light: #ecfdf5;
            --warn:          #d97706;
            --warn-light:    #fffbeb;

            /* Surfaces */
            --surface-page:     var(--slate-50);
            --surface-card:     var(--white);
            --surface-sidebar:  var(--blue-900);
            --surface-nav:      var(--white);

            /* Text on sidebar (dark bg) */
            --sidebar-text:      rgba(255,255,255,0.9);
            --sidebar-muted:     rgba(255,255,255,0.45);
            --sidebar-active-bg: rgba(255,255,255,0.12);
            --sidebar-hover-bg:  rgba(255,255,255,0.07);

            /* Borders */
            --border-default: var(--slate-200);
            --border-nav:     rgba(0,0,0,0.06);

            /* Elevation */
            --shadow-sm: 0 1px 3px 0 rgba(0,0,0,0.08), 0 1px 2px -1px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px -2px rgba(0,0,0,0.10), 0 2px 4px -2px rgba(0,0,0,0.06);
            --shadow-lg: 0 12px 32px -4px rgba(0,0,0,0.14), 0 4px 8px -2px rgba(0,0,0,0.06);
            --shadow-xl: 0 24px 56px -8px rgba(0,0,0,0.18);

            /* Tooltip shadow */
            --shadow-tooltip: 0 8px 24px -4px rgba(0,0,0,0.22), 0 2px 6px -1px rgba(0,0,0,0.12);

            /* Radii */
            --r-sm:  6px;
            --r-md:  10px;
            --r-lg:  14px;
            --r-xl:  20px;
            --r-full: 999px;

            /* Sidebar widths */
            --sidebar-w:           240px;
            --sidebar-w-collapsed: 72px;

            /* Transitions */
            --ease-out:    cubic-bezier(0.22, 1, 0.36, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --sidebar-dur: 0.32s;
        }

        /* ═══════════════════════════════════════
           GLOBAL RESET
        ═══════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: var(--font-ui);
            font-size: 14px;
            line-height: 1.6;
            color: var(--slate-900);
            background: var(--surface-page);
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }
        button { font-family: inherit; cursor: pointer; border: none; background: none; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--slate-200); border-radius: var(--r-full); }
        ::-webkit-scrollbar-thumb:hover { background: var(--slate-400); }

        /* ═══════════════════════════════════════
           APP SHELL
        ═══════════════════════════════════════ */
        .app-shell {
            display: grid;
            grid-template-rows: 56px 1fr;
            grid-template-columns: var(--sidebar-w) 1fr;
            grid-template-areas:
                "sidebar topbar"
                "sidebar content";
            height: 100vh;
            transition: grid-template-columns var(--sidebar-dur) var(--ease-out);
        }

        .app-shell.collapsed {
            grid-template-columns: var(--sidebar-w-collapsed) 1fr;
        }

        /* ═══════════════════════════════════════
           TOP NAV BAR
        ═══════════════════════════════════════ */
        .topbar {
            grid-area: topbar;
            background: var(--surface-nav);
            border-bottom: 1px solid var(--border-nav);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            gap: 1rem;
            position: relative;
            z-index: 30;
        }

        /* Search */
        .topbar-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--slate-100);
            border: 1px solid var(--border-default);
            border-radius: var(--r-full);
            padding: 0 1rem;
            width: 220px;
            transition: all 0.2s var(--ease-out);
        }
        .topbar-search:focus-within {
            background: var(--white);
            border-color: var(--blue-400);
            box-shadow: 0 0 0 3px rgba(36, 120, 228, 0.12);
            width: 280px;
        }
        .topbar-search svg { width: 14px; height: 14px; color: var(--slate-400); flex-shrink: 0; }
        .topbar-search input {
            background: none; border: none; outline: none;
            font-family: var(--font-ui); font-size: 13px;
            color: var(--slate-700); width: 100%; padding: 0.45rem 0;
        }
        .topbar-search input::placeholder { color: var(--slate-400); }

        /* Right actions cluster */
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Icon buttons in topbar */
        .icon-btn {
            position: relative;
            width: 36px; height: 36px;
            border-radius: var(--r-md);
            display: flex; align-items: center; justify-content: center;
            color: var(--slate-500);
            transition: all 0.15s var(--ease-out);
            background: transparent;
        }
        .icon-btn:hover {
            background: var(--slate-100);
            color: var(--blue-600);
        }
        .icon-btn svg { width: 18px; height: 18px; }

        /* Notification badge */
        .notif-badge {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid var(--white);
            display: none;
        }
        .notif-badge.visible {
            display: block;
            animation: popIn 0.3s var(--ease-spring);
        }
        @keyframes popIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        /* Topbar divider */
        .topbar-divider {
            width: 1px; height: 22px;
            background: var(--border-default);
            margin: 0 0.25rem;
        }

        /* User chip */
        .user-chip {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.3rem 0.75rem 0.3rem 0.3rem;
            border-radius: var(--r-full);
            border: 1px solid var(--border-default);
            background: var(--white);
            cursor: pointer;
            transition: all 0.15s var(--ease-out);
        }
        .user-chip:hover {
            background: var(--slate-50);
            border-color: var(--blue-300);
            box-shadow: var(--shadow-sm);
        }
        .user-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-500), var(--blue-700));
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; color: #fff;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }
        .user-info { line-height: 1.2; }
        .user-info-name {
            font-size: 13px; font-weight: 600;
            color: var(--slate-900);
            white-space: nowrap;
        }
        .user-info-role {
            font-size: 10px; font-weight: 600;
            color: var(--blue-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ═══════════════════════════════════════
           NOTIFICATION PANEL
        ═══════════════════════════════════════ */
        .notif-panel {
            position: fixed;
            top: 64px; right: 16px;
            width: 340px;
            background: var(--white);
            border-radius: var(--r-xl);
            border: 1px solid var(--border-default);
            box-shadow: var(--shadow-xl);
            z-index: 100;
            overflow: hidden;
            transform: translateY(-8px) scale(0.97);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.25s var(--ease-spring), opacity 0.2s ease;
        }
        .notif-panel.open {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }
        .notif-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.25rem 0.75rem;
            border-bottom: 1px solid var(--border-default);
        }
        .notif-header h3 {
            font-family: var(--font-display);
            font-size: 14px; font-weight: 600; color: var(--slate-900);
        }
        .notif-count-pill {
            font-size: 10px; font-weight: 700;
            background: var(--blue-500); color: #fff;
            padding: 2px 8px; border-radius: var(--r-full);
        }
        .notif-list { max-height: 320px; overflow-y: auto; }
        .notif-item {
            display: flex; gap: 0.75rem; align-items: flex-start;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--slate-100);
            transition: background 0.15s;
            cursor: pointer;
        }
        .notif-item:hover { background: var(--blue-50); }
        .notif-item:last-child { border-bottom: none; }
        .notif-dot {
            width: 8px; height: 8px; border-radius: 50%;
            flex-shrink: 0; margin-top: 5px;
        }
        .notif-dot.unread { background: var(--blue-500); }
        .notif-dot.read   { background: var(--slate-300); }
        .notif-body { flex: 1; min-width: 0; }
        .notif-title { font-size: 13px; font-weight: 600; color: var(--slate-800); line-height: 1.4; }
        .notif-meta  { font-size: 11px; color: var(--slate-400); margin-top: 2px; }

        .notif-empty {
            padding: 2rem 1.25rem;
            text-align: center;
            color: var(--slate-400);
            font-size: 13px;
        }
        .notif-empty svg {
            width: 32px; height: 32px;
            margin-bottom: 0.5rem;
            color: var(--slate-300);
        }

        .notif-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid var(--border-default);
            text-align: center;
        }
        .notif-footer a {
            font-size: 12px; font-weight: 600;
            color: var(--blue-600);
            transition: color 0.15s;
        }
        .notif-footer a:hover { color: var(--blue-800); }

        /* ═══════════════════════════════════════
           SETTINGS PANEL
        ═══════════════════════════════════════ */
        .settings-panel {
            position: fixed;
            top: 64px; right: 16px;
            width: 300px;
            background: var(--white);
            border-radius: var(--r-xl);
            border: 1px solid var(--border-default);
            box-shadow: var(--shadow-xl);
            z-index: 100;
            overflow: hidden;
            transform: translateY(-8px) scale(0.97);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.25s var(--ease-spring), opacity 0.2s ease;
        }
        .settings-panel.open {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }
        .settings-header {
            padding: 1rem 1.25rem 0.75rem;
            border-bottom: 1px solid var(--border-default);
        }
        .settings-header h3 {
            font-family: var(--font-display);
            font-size: 14px; font-weight: 600; color: var(--slate-900);
        }
        .settings-section { padding: 0.75rem 0; }
        .settings-label {
            font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--slate-400);
            padding: 0.25rem 1.25rem 0.5rem;
        }
        .settings-item {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.65rem 1.25rem;
            cursor: pointer;
            transition: background 0.15s;
            color: var(--slate-700);
            font-size: 13px; font-weight: 500;
        }
        .settings-item:hover { background: var(--slate-50); color: var(--blue-700); }
        .settings-item svg { width: 16px; height: 16px; color: var(--slate-400); flex-shrink: 0; }
        .settings-item:hover svg { color: var(--blue-500); }
        .settings-divider { height: 1px; background: var(--border-default); margin: 0 1.25rem; }

        /* ═══════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════ */
        .sidebar {
            grid-area: sidebar;
            background: var(--surface-sidebar);
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: var(--sidebar-w);
            overflow: hidden;
            position: relative;
            z-index: 20;
            /* Single unified transition for everything */
            transition: width var(--sidebar-dur) var(--ease-out);
            will-change: width;
        }

        .app-shell.collapsed .sidebar {
            width: var(--sidebar-w-collapsed);
        }

        /* Subtle sidebar texture */
        .sidebar::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 0% 0%, rgba(36,120,228,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 100% 100%, rgba(7,32,63,0.8) 0%, transparent 60%);
            pointer-events: none;
        }

        .sidebar > * { position: relative; z-index: 1; }

        /* ── Sidebar Brand ── */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1rem;
            height: 56px;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            overflow: hidden;
            white-space: nowrap;
            /* No transition here — width inherited from .sidebar */
        }

        /* Logo bigger as requested */
        .sidebar-logo {
            width: 46px; height: 46px;
            border-radius: var(--r-md);
            overflow: hidden;
            flex-shrink: 0;
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            transition: width var(--sidebar-dur) var(--ease-out),
                        height var(--sidebar-dur) var(--ease-out);
        }

        /* Slightly bigger logo in collapsed mode — centered nicely */
        .app-shell.collapsed .sidebar-logo {
            width: 48px; height: 48px;
        }

        .sidebar-logo img {
            width: 100%; height: 100%; object-fit: cover; display: block;
        }

        .sidebar-brand-text {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            opacity: 1;
            transform: translateX(0);
            transition:
                opacity   calc(var(--sidebar-dur) * 0.6) var(--ease-out),
                transform calc(var(--sidebar-dur) * 0.6) var(--ease-out);
        }

        .app-shell.collapsed .sidebar-brand-text {
            opacity: 0;
            transform: translateX(-6px);
            pointer-events: none;
            /* width collapses automatically because parent clips */
        }

        .sidebar-brand-name {
            font-family: var(--font-display);
            font-size: 14px; font-weight: 700;
            color: var(--white);
            letter-spacing: -0.01em;
            line-height: 1.2;
            white-space: nowrap;
        }
        .sidebar-brand-sub {
            font-size: 11px; font-weight: 400;
            color: var(--sidebar-muted);
            white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }

        /* ── Sidebar Nav ── */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.75rem 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* Section label wrapper — controls visibility */
        .nav-section-wrapper {
            overflow: hidden;
            /* Height animation for smooth appear/disappear */
            max-height: 32px;
            transition:
                max-height  calc(var(--sidebar-dur) * 0.5) var(--ease-out),
                opacity     calc(var(--sidebar-dur) * 0.4) var(--ease-out),
                margin      calc(var(--sidebar-dur) * 0.5) var(--ease-out);
            margin-top: 0.25rem;
        }

        .app-shell.collapsed .nav-section-wrapper {
            max-height: 0;
            opacity: 0;
            margin-top: 0;
            pointer-events: none;
        }

        .nav-section-label {
            font-size: 9.5px; font-weight: 700;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--sidebar-muted);
            padding: 0.5rem 1rem 0.25rem;
            display: block;
            white-space: nowrap;
        }

        /* ── Nav Links ── */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.62rem 0.875rem;
            border-radius: var(--r-md);
            margin: 0 0.5rem;
            color: var(--sidebar-muted);
            font-size: 13.5px; font-weight: 500;
            transition:
                background 0.18s var(--ease-out),
                color      0.18s var(--ease-out);
            position: relative;
            overflow: hidden; /* clip active indicator */
            white-space: nowrap;
        }

        /* In collapsed mode center the icon */
        .app-shell.collapsed .nav-link {
            justify-content: center;
            padding: 0.7rem 0;
            margin: 0 0.5rem;
        }

        .nav-link svg {
            width: 20px; height: 20px;
            flex-shrink: 0;
            transition: transform 0.18s var(--ease-out);
        }
        .nav-link:hover {
            background: var(--sidebar-hover-bg);
            color: var(--white);
        }
        .nav-link:hover svg { transform: scale(1.1); }
        .nav-link.active {
            background: var(--sidebar-active-bg);
            color: var(--white);
            font-weight: 600;
        }
    .nav-link.active::before {
    display: none;
}

        /* ── Nav Link Label (text + badge wrapper) ── */
        .nav-link-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            opacity: 1;
            transform: translateX(0);
            transition:
                opacity   calc(var(--sidebar-dur) * 0.5) var(--ease-out),
                transform calc(var(--sidebar-dur) * 0.5) var(--ease-out),
                max-width calc(var(--sidebar-dur)) var(--ease-out);
            max-width: 160px;
        }

        .app-shell.collapsed .nav-link-label {
            opacity: 0;
            transform: translateX(-6px);
            max-width: 0;
            pointer-events: none;
        }

        /* Nav link badge (e.g. count) */
        .nav-badge {
            font-size: 10px; font-weight: 700;
            background: var(--blue-500);
            color: #fff;
            padding: 1px 7px;
            border-radius: var(--r-full);
            line-height: 18px;
            display: none;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .nav-badge.visible {
            display: inline-block;
            animation: popIn 0.3s var(--ease-spring);
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 0.75rem 0.5rem;
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,0.08);
            overflow: hidden;
        }

        /* Logout button */
        .logout-btn {
            width: 100%;
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.62rem 0.875rem;
            border-radius: var(--r-md);
            color: rgba(255,255,255,0.5);
            font-size: 13.5px; font-weight: 500;
            transition: all 0.18s var(--ease-out);
            white-space: nowrap;
            overflow: hidden;
        }
        .logout-btn svg { width: 20px; height: 20px; flex-shrink: 0; }
        .logout-btn:hover {
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
        }

        .app-shell.collapsed .logout-btn {
            justify-content: center;
            padding: 0.7rem 0;
        }

        /* Logout label text */
        .logout-label {
            overflow: hidden;
            opacity: 1;
            transform: translateX(0);
            transition:
                opacity   calc(var(--sidebar-dur) * 0.5) var(--ease-out),
                transform calc(var(--sidebar-dur) * 0.5) var(--ease-out),
                max-width calc(var(--sidebar-dur)) var(--ease-out);
            max-width: 120px;
        }
        .app-shell.collapsed .logout-label {
            opacity: 0;
            transform: translateX(-6px);
            max-width: 0;
            pointer-events: none;
        }

        /* ── Floating Tooltip (JS-managed, appended to body) ── */
        /* Sidebar overflow:hidden would clip any CSS tooltip inside it,
           so we use a single shared floating tooltip element on <body>. */
        #sidebarFloatTooltip {
            position: fixed;
            z-index: 9999;
            background: #0c3566;
            color: #fff;
            font-family: var(--font-ui);
            font-size: 12.5px; font-weight: 600;
            padding: 6px 12px;
            border-radius: var(--r-md);
            white-space: nowrap;
            pointer-events: none;
            box-shadow: var(--shadow-tooltip);
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 0.14s ease, transform 0.14s var(--ease-out);
        }
        #sidebarFloatTooltip.visible {
            opacity: 1;
            transform: translateX(0);
        }
        /* Left-pointing arrow */
        #sidebarFloatTooltip::before {
            content: '';
            position: absolute;
            right: 100%; top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #0c3566;
        }

        /* ═══════════════════════════════════════
           MAIN CONTENT
        ═══════════════════════════════════════ */
        .main-content {
            grid-area: content;
            overflow-y: auto;
            padding: 1.75rem 2rem;
            background: var(--surface-page);
        }

        /* Success alert */
        .alert-success {
            position: fixed;
            top: 70px; left: 50%;
            transform: translateX(-50%) translateY(-18px);
            z-index: 9999;
            display: flex; align-items: center; gap: 0.75rem;
            background: var(--success-light);
            border: 1px solid rgba(5, 150, 105, 0.25);
            color: var(--success);
            padding: 0.85rem 1.25rem;
            border-radius: var(--r-lg);
            font-size: 13.5px; font-weight: 600;
            box-shadow: 0 12px 32px rgba(0,0,0,.14);
            max-width: 90vw;
            opacity: 0; pointer-events: none;
            transition: opacity .4s ease, transform .45s cubic-bezier(.16,1,.3,1);
        }
        .alert-success.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .alert-success svg { width: 18px; height: 18px; flex-shrink: 0; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        /* ═══════════════════════════════════════
           LOGOUT CONFIRMATION MODAL
        ═══════════════════════════════════════ */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 200;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .modal-backdrop.open {
            opacity: 1; pointer-events: auto;
        }
        .modal-box {
            background: var(--white);
            border-radius: var(--r-xl);
            box-shadow: var(--shadow-xl);
            width: 380px; max-width: calc(100vw - 2rem);
            padding: 2rem;
            transform: scale(0.94) translateY(12px);
            transition: transform 0.3s var(--ease-spring);
        }
        .modal-backdrop.open .modal-box {
            transform: scale(1) translateY(0);
        }
        .modal-icon {
            width: 52px; height: 52px;
            border-radius: var(--r-lg);
            background: var(--danger-light);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.25rem;
        }
        .modal-icon svg { width: 26px; height: 26px; color: var(--danger); }
        .modal-title {
            font-family: var(--font-display);
            font-size: 17px; font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 0.5rem;
        }
        .modal-desc {
            font-size: 13.5px; color: var(--slate-500); line-height: 1.6;
            margin-bottom: 1.75rem;
        }
        .modal-actions {
            display: flex; gap: 0.75rem;
        }
        .btn {
            flex: 1; padding: 0.7rem 1rem;
            border-radius: var(--r-md);
            font-family: var(--font-ui);
            font-size: 13.5px; font-weight: 600;
            transition: all 0.18s var(--ease-out);
            text-align: center;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-ghost {
            background: var(--slate-100);
            color: var(--slate-700);
            border: 1px solid var(--border-default);
        }
        .btn-ghost:hover { background: var(--slate-200); }
        .btn-danger {
            background: var(--danger);
            color: #fff;
            border: 1px solid transparent;
        }
        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
        }
        .btn-danger:active { transform: none; }

        /* Spinning loader inside danger button */
        .btn-spinner {
            display: none;
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin-right: 6px;
            flex-shrink: 0;
        }
        .btn-danger.loading .btn-spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ═══════════════════════════════════════
           MOBILE
        ═══════════════════════════════════════ */
        .mobile-menu-btn {
            display: none;
            width: 36px; height: 36px;
            align-items: center; justify-content: center;
            border-radius: var(--r-md);
            color: var(--slate-500);
        }
        .mobile-menu-btn:hover { background: var(--slate-100); }
        .mobile-menu-btn svg { width: 20px; height: 20px; }

        @media (max-width: 768px) {
            .app-shell {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "topbar"
                    "content";
            }
            .sidebar {
                position: fixed;
                left: 0; top: 0;
                height: 100vh;
                width: var(--sidebar-w) !important; /* always full on mobile */
                transform: translateX(-100%);
                transition: transform 0.3s var(--ease-out);
                z-index: 50;
            }
            .sidebar.open { transform: translateX(0); }
            .mobile-menu-btn { display: flex; }
            .topbar-search { display: none; }
            .sidebar-overlay {
                position: fixed; inset: 0;
                background: rgba(15,23,42,0.5);
                z-index: 45;
                opacity: 0; pointer-events: none;
                transition: opacity 0.3s;
            }
            .sidebar-overlay.open { opacity: 1; pointer-events: auto; }
            /* Never show tooltips on mobile */
            .nav-tooltip, .logout-tooltip { display: none !important; }
            /* Show labels on mobile even if collapsed class present */
            .sidebar.open .nav-link-label,
            .sidebar.open .logout-label {
                opacity: 1 !important;
                max-width: 160px !important;
                transform: none !important;
            }
            /* hide collapse btn on mobile */
            #collapseBtn { display: none; }
        }

        /* ═══════════════════════════════════════
   SKELETON LOADING
═══════════════════════════════════════ */
@keyframes skeletonShimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.skeleton {
    border-radius: var(--r-md);
    background: linear-gradient(
        90deg,
        var(--slate-200) 25%,
        var(--slate-100) 50%,
        var(--slate-200) 75%
    );
    background-size: 200% 100%;
    animation: skeletonShimmer 1.5s infinite;
}

/* For use inside dark sidebar */
.skeleton-dark {
    border-radius: var(--r-md);
    background: linear-gradient(
        90deg,
        rgba(255,255,255,0.07) 25%,
        rgba(255,255,255,0.13) 50%,
        rgba(255,255,255,0.07) 75%
    );
    background-size: 200% 100%;
    animation: skeletonShimmer 1.5s infinite;
}

/* Wrapper na nagtatago ng actual content */
#pageSkeletonWrapper {
    padding: 0;
}

#pageSkeletonWrapper .sk-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

#pageSkeletonWrapper .sk-card {
    background: var(--surface-card);
    border-radius: var(--r-lg);
    border: 1px solid var(--border-default);
    padding: 20px;
    box-shadow: var(--shadow-sm);
}

/* ── Skeleton ── */
@keyframes skeletonShimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.sk {
    display: block;
    border-radius: 6px;
    background: linear-gradient(90deg, var(--slate-200) 25%, var(--slate-100) 50%, var(--slate-200) 75%);
    background-size: 200% 100%;
    animation: skeletonShimmer 1.5s infinite;
}




    </style>
</head>
<body>

{{-- ░░ APP SHELL ░░ --}}
<div class="app-shell" id="appShell">

    {{-- ════════════════════════════════
         SIDEBAR
    ════════════════════════════════ --}}
    <aside class="sidebar" id="sidebar">
        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <img src="{{ asset('images/logo.png') }}" alt="School Logo">
            </div>
            <div class="sidebar-brand-text">
                <div class="sidebar-brand-name">DP-LMS</div>
                <div class="sidebar-brand-sub">Sto. Domingo NHS</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav" aria-label="Main navigation">

            {{-- Section: Main --}}
            <div class="nav-section-wrapper">
                <span class="nav-section-label">Main</span>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
               data-tooltip="Dashboard"
               data-barba-prevent="self">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6zM4 14a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4z"/>
                </svg>
                <span class="nav-link-label">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               data-tooltip="User Management"
               data-barba-prevent="self">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87a4 4 0 100-8 4 4 0 000 8zm6-12a3 3 0 11-6 0 3 3 0 016 0zM6 8a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
                <span class="nav-link-label">User Management</span>
            </a>

            <a href="{{ route('admin.face.index') }}"
               class="nav-link {{ request()->routeIs('admin.face.*') ? 'active' : '' }}"
               data-tooltip="Face Verification"
               data-barba-prevent="self">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
</svg>
                <span class="nav-link-label">Face Verification</span>
            </a>

            <a href="{{ route('admin.enrollment.index') }}"
               class="nav-link {{ request()->routeIs('admin.enrollment.*') ? 'active' : '' }}"
               data-tooltip="Enrollment"
               data-barba-prevent="self">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="nav-link-label">
                    Enrollment
                    <span class="nav-badge" id="enrollmentBadge">{{ $pendingEnrollments ?? 0 }}</span>
                </span>
            </a>

            {{-- Section: System --}}
            <div class="nav-section-wrapper">
                <span class="nav-section-label">System</span>
            </div>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
               data-tooltip="Audit Logs"
               data-barba-prevent="self">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="nav-link-label">Audit Logs</span>
            </a>

            <a href="{{ route('admin.reports') }}"
               class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
               data-tooltip="Reports"
               data-barba-prevent="self">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="nav-link-label">Reports</span>
            </a>

        </nav>

        {{-- Footer — Sign Out --}}
        <div class="sidebar-footer">
            <button type="button" class="logout-btn" id="logoutTrigger" aria-label="Sign out">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="logout-label">Sign Out</span>
            </button>
        </div>
    </aside>

    {{-- Mobile sidebar overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ════════════════════════════════
         TOP BAR
    ════════════════════════════════ --}}
    <header class="topbar">
        {{-- Left: mobile toggle + collapse btn + search --}}
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Collapse Toggle Button --}}
            <button class="icon-btn" id="collapseBtn" aria-label="Toggle Sidebar" title="Toggle sidebar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="topbar-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" placeholder="Search anything…" aria-label="Search">
            </div>
        </div>

        {{-- Right: notifications, settings, user chip --}}
        <div class="topbar-right">

            {{-- Dark mode toggle --}}
            <button class="icon-btn" id="darkToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                <svg class="dt-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg class="dt-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- Notifications --}}
            <button class="icon-btn" id="notifBtn" aria-label="Notifications">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="notif-badge" id="notifBadge" aria-label="Unread notifications"></span>
            </button>

            {{-- Settings --}}
            <button class="icon-btn" id="settingsBtn" aria-label="Settings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>

            <div class="topbar-divider"></div>

            {{-- User chip --}}
            <div class="user-chip" tabindex="0" role="button" aria-label="User account">
                <div class="user-avatar" id="userAvatarInitials"></div>
                <div class="user-info">
                    <div class="user-info-name">{{ auth('admin')->user()->name }}</div>
                    <div class="user-info-role">Administrator</div>
                </div>
            </div>
        </div>
    </header>

    {{-- ════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════ --}}
    <main class="main-content" id="mainContent">
        @if(session('success'))
            <div class="alert-success" id="appToast" role="alert">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

</div>{{-- /app-shell --}}

{{-- ════════════════════════════════
     NOTIFICATION PANEL
════════════════════════════════ --}}
<div class="notif-panel" id="notifPanel" role="dialog" aria-label="Notifications" aria-modal="true">
    <div class="notif-header">
        <h3>Notifications</h3>
        <span class="notif-count-pill" id="notifCountPill">0 new</span>
    </div>
    <div class="notif-list" id="notifListContainer">
        <div class="notif-empty">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p>No new notifications</p>
        </div>
    </div>
    <div class="notif-footer">
        <a href="#">View all notifications →</a>
    </div>
</div>

{{-- ════════════════════════════════
     SETTINGS PANEL
════════════════════════════════ --}}
<div class="settings-panel" id="settingsPanel" role="dialog" aria-label="Settings" aria-modal="true">
    <div class="settings-header">
        <h3>Settings</h3>
    </div>
    <div class="settings-section">
        <div class="settings-label">Account</div>
      <a href="{{ route('admin.profile.edit') }}" class="settings-item">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    Edit Profile
</a>
<a href="{{ route('admin.profile.change-password') }}" class="settings-item">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
    </svg>
    Change Password
</a>
    </div>
    <div class="settings-divider"></div>
    <div class="settings-section">
        <div class="settings-label">System</div>
        <div class="settings-item" role="button" tabindex="0" id="notifPrefBtn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Notification Preferences
        </div>
        <div class="settings-item" role="button" tabindex="0" id="exportDataBtn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Export Data
        </div>
        <div class="settings-item" role="button" tabindex="0">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Privacy & Security
        </div>
    </div>
</div>

{{-- ════════════════════════════════
     LOGOUT CONFIRMATION MODAL
════════════════════════════════ --}}
<div class="modal-backdrop" id="logoutModal" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
    <div class="modal-box">
        <div class="modal-icon" aria-hidden="true">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>
        <div class="modal-title" id="logoutModalTitle">Sign out of DP-LMS?</div>
        <p class="modal-desc">You'll need to sign in again to access the admin panel. Any unsaved changes will be lost.</p>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="logoutCancel">Stay signed in</button>
            <button class="btn btn-danger" id="logoutConfirm">
                <span class="btn-spinner" aria-hidden="true"></span>
                <span id="logoutConfirmText">Sign out</span>
            </button>
        </div>
    </div>
</div>

{{-- Hidden logout form (POST with CSRF) --}}
<form method="POST" action="{{ route('admin.logout') }}" id="logoutForm" style="display:none;">
    @csrf
</form>

{{-- Floating sidebar tooltip --}}
<div id="sidebarFloatTooltip" role="tooltip" aria-hidden="true"></div>

<script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>

<script>
(function () {
    'use strict';

    /* ── User avatar initials ── */
    const userName = @json(auth('admin')->user()->name);
    const initials = userName.trim().split(/\s+/).map(w => w[0]).join('').slice(0, 2).toUpperCase();
    document.getElementById('userAvatarInitials').textContent = initials;

    /* ═══════════════════════════════════════
       SIDEBAR COLLAPSE
       Persisted via localStorage. CSS transitions handle animation.
       applyCollapsedState() is called after every Barba navigation
       so the sidebar state is never lost.
    ═══════════════════════════════════════ */
    const appShell    = document.getElementById('appShell');
    const sidebarEl   = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('collapseBtn');

    function isCollapsed() {
        return localStorage.getItem('sidebarCollapsed') === 'true';
    }

    function applyCollapsedState(withAnimation) {
        if (!withAnimation) {
            appShell.style.transition  = 'none';
            sidebarEl.style.transition = 'none';
        }
        appShell.classList.toggle('collapsed', isCollapsed());
        if (!withAnimation) {
            requestAnimationFrame(() => requestAnimationFrame(() => {
                appShell.style.transition  = '';
                sidebarEl.style.transition = '';
            }));
        }
    }

    /* Restore on first paint — no flash */
    applyCollapsedState(false);

    collapseBtn.addEventListener('click', function () {
        appShell.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', appShell.classList.contains('collapsed'));
        hideFloatTooltip();
    });

    /* ═══════════════════════════════════════
       FLOATING SIDEBAR TOOLTIP
       Lives on <body> so sidebar overflow:hidden can't clip it.
       Only appears when sidebar is collapsed.
    ═══════════════════════════════════════ */
    const floatTip = document.getElementById('sidebarFloatTooltip');

    function showFloatTooltip(label, anchorEl, below) {
        if (!below && !isCollapsed()) return; /* nav tooltips: collapsed only */
        floatTip.textContent = label;
        floatTip.classList.remove('visible');

        const rect = anchorEl.getBoundingClientRect();
        if (below) {
            /* For topbar buttons — show below */
            floatTip.style.top  = (rect.bottom + 8) + 'px';
            floatTip.style.left = (rect.left + rect.width / 2 - 56) + 'px';
        } else {
            /* For sidebar items — show to the right, vertically centred */
            const approxH = 28;
            floatTip.style.top  = (rect.top + rect.height / 2 - approxH / 2) + 'px';
            floatTip.style.left = (rect.right + 12) + 'px';
        }

        void floatTip.offsetWidth; /* force reflow so transition fires */
        floatTip.classList.add('visible');
    }

    function hideFloatTooltip() {
        floatTip.classList.remove('visible');
    }

    function attachTooltips() {
        /* All nav links must have data-tooltip attribute */
        document.querySelectorAll('.nav-link[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', () => showFloatTooltip(el.dataset.tooltip, el, false));
            el.addEventListener('mouseleave', hideFloatTooltip);
        });

        /* Logout button */
        const lb = document.getElementById('logoutTrigger');
        if (lb) {
            lb.addEventListener('mouseenter', () => showFloatTooltip('Sign Out', lb, false));
            lb.addEventListener('mouseleave', hideFloatTooltip);
        }

        /* Collapse button — always shows, below the button */
        collapseBtn.addEventListener('mouseenter', function () {
            showFloatTooltip(isCollapsed() ? 'Expand Sidebar' : 'Collapse Sidebar', this, true);
        });
        collapseBtn.addEventListener('mouseleave', hideFloatTooltip);
        collapseBtn.addEventListener('click', hideFloatTooltip);
    }

    attachTooltips();

    /* ═══════════════════════════════════════
       DYNAMIC BADGES
    ═══════════════════════════════════════ */

    // 1. Enrollment Badge
    const enrollmentBadge = document.getElementById('enrollmentBadge');
    const enrollmentCount = parseInt('{{ $pendingEnrollments ?? 0 }}', 10);
    if (enrollmentCount > 0) {
        enrollmentBadge.textContent = enrollmentCount;
        enrollmentBadge.classList.add('visible');
    } else {
        enrollmentBadge.style.display = 'none';
    }

    // 2. Notification Badge & Panel Content
    const notifBadge         = document.getElementById('notifBadge');
    const notifCountPill     = document.getElementById('notifCountPill');
    const notifListContainer = document.getElementById('notifListContainer');

    function loadNotifications() {
        fetch('/admin/notifications', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const count         = data.count         || 0;
            const notifications = data.notifications || [];

            if (count > 0) {
                notifBadge.classList.add('visible');
                notifCountPill.textContent = count + ' new';
            } else {
                notifBadge.classList.remove('visible');
                notifCountPill.textContent = '0 new';
            }

            if (notifications.length === 0) {
                notifListContainer.innerHTML = `
                    <div class="notif-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p>No new notifications</p>
                    </div>`;
            } else {
                let html = '';
                notifications.forEach(notif => {
                    html += `
                        <div class="notif-item">
                            <span class="notif-dot ${notif.is_read ? 'read' : 'unread'}" aria-hidden="true"></span>
                            <div class="notif-body">
                                <div class="notif-title">${notif.title}</div>
                                <div class="notif-meta">${notif.created_at}</div>
                            </div>
                        </div>`;
                });
                notifListContainer.innerHTML = html;
            }
        })
        .catch(err => {
            console.error('Error loading notifications:', err);
            notifListContainer.innerHTML = `<div class="notif-empty"><p>Failed to load notifications</p></div>`;
        });
    }

    loadNotifications();

    /* ═══════════════════════════════════════
       PANEL MANAGER (one open at a time)
    ═══════════════════════════════════════ */
    const panels = {
        notif:    document.getElementById('notifPanel'),
        settings: document.getElementById('settingsPanel'),
    };

    function closeAll() {
        Object.values(panels).forEach(p => p.classList.remove('open'));
    }

    function togglePanel(key, triggerBtn) {
        const isOpen = panels[key].classList.contains('open');
        closeAll();
        if (!isOpen) {
            const rect   = triggerBtn.getBoundingClientRect();
            const panel  = panels[key];
            const panelW = parseInt(getComputedStyle(panel).width);
            let left = rect.right - panelW;
            if (left < 8) left = 8;
            panel.style.left  = left + 'px';
            panel.style.right = 'auto';
            panel.style.top   = (rect.bottom + 8) + 'px';
            panel.classList.add('open');
        }
    }

    document.getElementById('notifBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        togglePanel('notif', this);
    });

    document.getElementById('settingsBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        togglePanel('settings', this);
    });

       document.addEventListener('click', function (e) {
        const isInsidePanel = Object.values(panels).some(p => p.contains(e.target));
        const isInsideModal = logoutModal.contains(e.target);
        const isLogoutBtn   = logoutTrigger.contains(e.target);
        if (!isInsidePanel && !isInsideModal && !isLogoutBtn) closeAll();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAll();
            closeLogoutModal();
        }
    });

    /* ═══════════════════════════════════════
       LOGOUT MODAL
    ═══════════════════════════════════════ */
    const logoutModal   = document.getElementById('logoutModal');
    const logoutTrigger = document.getElementById('logoutTrigger');
    const logoutCancel  = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');
    const logoutForm    = document.getElementById('logoutForm');

    function openLogoutModal()  { logoutModal.classList.add('open');    logoutCancel.focus(); }
    function closeLogoutModal() { logoutModal.classList.remove('open'); }

    logoutTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
         closeAll();
        openLogoutModal();
    });
    logoutCancel.addEventListener('click', closeLogoutModal);

    logoutModal.addEventListener('click', function (e) {
        if (e.target === logoutModal) closeLogoutModal();
    });

    logoutConfirm.addEventListener('click', function () {
        if (this.disabled) return;
        this.classList.add('loading');
        this.disabled = true;
        document.getElementById('logoutConfirmText').textContent = 'Signing out…';
        setTimeout(() => logoutForm.submit(), 600);
    });

    /* ═══════════════════════════════════════
       MOBILE SIDEBAR
    ═══════════════════════════════════════ */
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileMenuBtn  = document.getElementById('mobileMenuBtn');

    function openSidebar()  { sidebar.classList.add('open');    sidebarOverlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('open'); }

    mobileMenuBtn.addEventListener('click', openSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);

    /* ═══════════════════════════════════════
       ACTIVE NAV LINK — JS-managed after Barba navigation
    ═══════════════════════════════════════ */
    function updateActiveLink() {
        const path = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = (link.getAttribute('href') || '').replace(/\?.*$/, '');
            const isActive = href && href !== '/' && path.startsWith(href);
            link.classList.toggle('active', isActive);
        });
    }

    /* ═══════════════════════════════════════
       BARBA PAGE TRANSITIONS
       Sidebar is OUTSIDE data-barba-container so it never
       re-renders. After each transition we re-apply the
       collapsed state from localStorage so it always stays.
    ═══════════════════════════════════════ */
/* ── SPA Navigation (fetch swap) ── */
function skeletonDashboard() {
    return `<div style="max-width:1280px;margin:0 auto;">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:1.75rem;">
            <div>
                <div class="sk" style="height:22px;width:140px;margin-bottom:8px;border-radius:8px;"></div>
                <div class="sk" style="height:13px;width:240px;"></div>
            </div>
            <div class="sk" style="height:32px;width:140px;border-radius:999px;"></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
            ${[0,1,2,3].map(() => `
            <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;padding:1.25rem 1.375rem;position:relative;overflow:hidden;">
                <div class="sk" style="position:absolute;top:0;left:0;right:0;height:3px;border-radius:14px 14px 0 0;"></div>
                <div class="sk" style="width:40px;height:40px;border-radius:10px;margin-bottom:14px;"></div>
                <div class="sk" style="height:9px;width:55%;margin-bottom:10px;"></div>
                <div class="sk" style="height:32px;width:45%;margin-bottom:10px;border-radius:6px;"></div>
                <div class="sk" style="height:9px;width:65%;"></div>
            </div>`).join('')}
        </div>
        <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;margin-bottom:1.25rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1.125rem 1.5rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);">
                <div class="sk" style="height:14px;width:180px;"></div>
                <div class="sk" style="height:30px;width:150px;border-radius:8px;"></div>
            </div>
            <div style="display:flex;gap:1.25rem;padding:0.75rem 1.5rem;border-bottom:1px solid var(--border-default);">
                <div class="sk" style="height:10px;width:90px;"></div>
                <div class="sk" style="height:10px;width:75px;"></div>
                <div class="sk" style="height:10px;width:60px;"></div>
            </div>
            <div style="padding:1.25rem 1.5rem;">
                <div class="sk" style="height:260px;width:100%;border-radius:8px;"></div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
            <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.375rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);">
                    <div class="sk" style="height:13px;width:160px;"></div>
                    <div class="sk" style="height:12px;width:55px;"></div>
                </div>
                <div style="padding:0 1.375rem;">
                    ${[0,1,2,3].map(() => `
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.875rem 0;border-bottom:1px solid var(--border-default);">
                        <div class="sk" style="width:36px;height:36px;border-radius:50%;flex-shrink:0;"></div>
                        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
                            <div class="sk" style="height:11px;width:65%;"></div>
                            <div class="sk" style="height:9px;width:50%;"></div>
                        </div>
                        <div class="sk" style="height:26px;width:90px;border-radius:999px;"></div>
                    </div>`).join('')}
                </div>
            </div>
            <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.375rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);">
                    <div class="sk" style="height:13px;width:130px;"></div>
                    <div class="sk" style="height:12px;width:55px;"></div>
                </div>
                <div style="padding:0 1.375rem;">
                    ${[0,1,2,3,4].map(() => `
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.875rem 0;border-bottom:1px solid var(--border-default);">
                        <div class="sk" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:4px;"></div>
                        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
                            <div class="sk" style="height:11px;width:75%;"></div>
                            <div class="sk" style="height:9px;width:55%;"></div>
                        </div>
                        <div class="sk" style="height:9px;width:40px;flex-shrink:0;"></div>
                    </div>`).join('')}
                </div>
            </div>
        </div>
    </div>`;
}

function skeletonUsers() {
    return `<div style="max-width:100%;">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:1.5rem;">
            <div>
                <div class="sk" style="height:22px;width:160px;margin-bottom:8px;border-radius:8px;"></div>
                <div class="sk" style="height:13px;width:280px;"></div>
            </div>
        </div>
        <div style="display:flex;gap:4px;border-bottom:1px solid var(--border-default);margin-bottom:1.25rem;">
            ${['120px','100px','90px','90px'].map(w => `
            <div style="padding:8px 14px;border-radius:8px 8px 0 0;">
                <div class="sk" style="height:12px;width:${w};"></div>
            </div>`).join('')}
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.25rem;">
            <div class="sk" style="height:34px;width:180px;border-radius:999px;"></div>
            <div class="sk" style="height:34px;width:120px;border-radius:8px;"></div>
            <div class="sk" style="height:34px;width:80px;border-radius:8px;"></div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:1.1rem;">
            <div class="sk" style="height:10px;width:40px;"></div>
            ${[0,1,2,3,4,5,6].map(() => `
            <div class="sk" style="height:30px;width:52px;border-radius:8px;"></div>`).join('')}
        </div>
        <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 80px;background:var(--slate-50);border-bottom:1px solid var(--border-default);padding:12px 20px;gap:16px;align-items:center;">
                ${['140px','80px','100px','80px','80px','40px'].map(w => `
                <div class="sk" style="height:10px;width:${w};"></div>`).join('')}
            </div>
            ${[0,1,2,3,4,5,6].map(i => `
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 80px;padding:14px 20px;border-bottom:1px solid var(--border-default);align-items:center;gap:16px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="sk" style="width:34px;height:34px;border-radius:8px;flex-shrink:0;"></div>
                    <div>
                        <div class="sk" style="height:11px;width:${100+(i*13)%60}px;margin-bottom:6px;"></div>
                        <div class="sk" style="height:9px;width:${80+(i*17)%50}px;"></div>
                    </div>
                </div>
                <div><div class="sk" style="height:22px;width:60px;border-radius:6px;"></div></div>
                <div>
                    <div class="sk" style="height:11px;width:70px;margin-bottom:5px;"></div>
                    <div class="sk" style="height:9px;width:40px;"></div>
                </div>
                <div><div class="sk" style="height:22px;width:72px;border-radius:999px;"></div></div>
                <div><div class="sk" style="height:11px;width:75px;"></div></div>
                <div style="display:flex;justify-content:flex-end;">
                    <div class="sk" style="width:30px;height:30px;border-radius:6px;"></div>
                </div>
            </div>`).join('')}
            <div style="padding:12px 20px;border-top:1px solid var(--border-default);background:var(--slate-50);display:flex;gap:6px;">
                ${[0,1,2,3,4].map(() => `
                <div class="sk" style="height:28px;width:32px;border-radius:6px;"></div>`).join('')}
            </div>
        </div>
    </div>`;
}

function skeletonDefault() {
    return `<div style="max-width:100%;">
        <div style="margin-bottom:1.5rem;">
            <div class="sk" style="height:22px;width:150px;margin-bottom:8px;border-radius:8px;"></div>
            <div class="sk" style="height:13px;width:220px;"></div>
        </div>
        <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);display:flex;justify-content:space-between;">
                <div class="sk" style="height:14px;width:160px;"></div>
                <div class="sk" style="height:30px;width:100px;border-radius:8px;"></div>
            </div>
            ${[0,1,2,3,4,5].map(() => `
            <div style="display:flex;align-items:center;gap:16px;padding:14px 20px;border-bottom:1px solid var(--border-default);">
                <div class="sk" style="width:34px;height:34px;border-radius:50%;flex-shrink:0;"></div>
                <div class="sk" style="height:11px;flex:2;"></div>
                <div class="sk" style="height:11px;flex:1;"></div>
                <div class="sk" style="height:22px;width:70px;border-radius:999px;"></div>
            </div>`).join('')}
        </div>
    </div>`;
}


// PALITAN ANG BUONG skeletonAuditLogs() NG GANITO:
function skeletonAuditLogs() {
    return `<div style="max-width:100%;">
        <div style="margin-bottom:1.5rem;">
            <div class="sk" style="height:22px;width:130px;margin-bottom:8px;border-radius:8px;"></div>
            <div class="sk" style="height:13px;width:260px;"></div>
        </div>
        <div style="display:flex;gap:0.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
            ${[0,1,2,3].map(() => `
            <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;padding:0.875rem 1.25rem;display:flex;align-items:center;gap:0.75rem;flex:1;min-width:140px;">
                <div class="sk" style="width:36px;height:36px;border-radius:10px;flex-shrink:0;"></div>
                <div>
                    <div class="sk" style="height:22px;width:45px;margin-bottom:6px;border-radius:6px;"></div>
                    <div class="sk" style="height:9px;width:70px;"></div>
                </div>
            </div>`).join('')}
        </div>
        <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;padding:0.875rem 1.25rem;display:flex;align-items:center;gap:0.65rem;margin-bottom:1.25rem;flex-wrap:wrap;">
            <div class="sk" style="height:34px;width:220px;border-radius:999px;"></div>
            <div class="sk" style="height:34px;width:120px;border-radius:10px;"></div>
            <div class="sk" style="height:34px;width:130px;border-radius:10px;"></div>
            <div class="sk" style="height:34px;width:140px;border-radius:10px;"></div>
            <div class="sk" style="height:34px;width:80px;border-radius:10px;"></div>
        </div>
        <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <div class="sk" style="width:15px;height:15px;border-radius:4px;"></div>
                    <div class="sk" style="height:13px;width:130px;"></div>
                </div>
                <div class="sk" style="height:24px;width:90px;border-radius:999px;"></div>
            </div>
            <div style="display:grid;grid-template-columns:1.2fr 1.4fr 0.8fr 0.9fr 2fr 1fr;background:var(--slate-50);border-bottom:1px solid var(--border-default);padding:10px 20px;gap:16px;align-items:center;">
                ${['80px','90px','60px','70px','120px','80px'].map(w => `
                <div class="sk" style="height:10px;width:${w};"></div>`).join('')}
            </div>
            ${[0,1,2,3,4,5,6,7].map(i => `
            <div style="display:grid;grid-template-columns:1.2fr 1.4fr 0.8fr 0.9fr 2fr 1fr;padding:14px 20px;border-bottom:1px solid var(--border-default);align-items:center;gap:16px;">
                <div>
                    <div class="sk" style="height:11px;width:85px;margin-bottom:5px;"></div>
                    <div class="sk" style="height:9px;width:60px;"></div>
                </div>
                <div style="display:flex;align-items:center;gap:0.6rem;">
                    <div class="sk" style="width:30px;height:30px;border-radius:8px;flex-shrink:0;"></div>
                    <div class="sk" style="height:11px;width:${80+(i*19)%50}px;"></div>
                </div>
                <div><div class="sk" style="height:22px;width:65px;border-radius:999px;"></div></div>
                <div><div class="sk" style="height:22px;width:75px;border-radius:6px;"></div></div>
                <div><div class="sk" style="height:11px;width:${120+(i*23)%80}px;"></div></div>
                <div><div class="sk" style="height:22px;width:90px;border-radius:6px;"></div></div>
            </div>`).join('')}
            <div style="padding:12px 20px;border-top:1px solid var(--border-default);background:var(--slate-50);display:flex;gap:6px;">
                ${[0,1,2,3,4].map(() => `
                <div class="sk" style="height:28px;width:32px;border-radius:6px;"></div>`).join('')}
            </div>
        </div>
    </div>`;
}

function skeletonForm() {
    return `<div style="max-width:900px;">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;">
            <div class="sk" style="width:34px;height:34px;border-radius:10px;flex-shrink:0;"></div>
            <div>
                <div class="sk" style="height:22px;width:220px;margin-bottom:8px;border-radius:8px;"></div>
                <div class="sk" style="height:13px;width:300px;"></div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">
            <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;">
                <div style="display:flex;align-items:center;gap:0.75rem;padding:1.1rem 1.5rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);">
                    <div class="sk" style="width:32px;height:32px;border-radius:10px;flex-shrink:0;"></div>
                    <div class="sk" style="height:14px;width:160px;"></div>
                </div>
                <div style="padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;background:#faf5ff;border:1px solid #e9d5ff;border-radius:10px;margin-bottom:1.25rem;">
                        <div class="sk" style="width:44px;height:44px;border-radius:10px;flex-shrink:0;"></div>
                        <div>
                            <div class="sk" style="height:14px;width:130px;margin-bottom:6px;"></div>
                            <div class="sk" style="height:10px;width:90px;"></div>
                        </div>
                    </div>
                    ${[0,1].map(() => `
                    <div style="margin-bottom:1.25rem;">
                        <div class="sk" style="height:10px;width:80px;margin-bottom:8px;border-radius:4px;"></div>
                        <div class="sk" style="height:40px;width:100%;border-radius:10px;"></div>
                    </div>`).join('')}
                    <div style="height:1px;background:var(--border-default);margin:1.25rem 0;"></div>
                    <div class="sk" style="height:10px;width:60px;margin-bottom:1rem;border-radius:4px;"></div>
                    ${[0,1].map(() => `
                    <div style="margin-bottom:1.25rem;">
                        <div class="sk" style="height:10px;width:80px;margin-bottom:8px;border-radius:4px;"></div>
                        <div class="sk" style="height:40px;width:100%;border-radius:10px;"></div>
                        <div style="display:flex;gap:4px;margin-top:6px;">
                            ${[0,1,2,3].map(() => `<div class="sk" style="flex:1;height:3px;border-radius:999px;"></div>`).join('')}
                        </div>
                    </div>`).join('')}
                </div>
            </div>
            <div>
                <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;margin-bottom:1rem;">
                    <div style="padding:1.5rem;">
                        <div class="sk" style="height:42px;width:100%;border-radius:10px;margin-bottom:0.6rem;"></div>
                        <div class="sk" style="height:40px;width:100%;border-radius:10px;"></div>
                    </div>
                </div>
                <div style="background:var(--surface-card);border:1px solid var(--border-default);border-radius:14px;overflow:hidden;">
                    <div style="padding:1rem 1.25rem 0.75rem;border-bottom:1px solid var(--border-default);background:var(--slate-50);">
                        <div class="sk" style="height:10px;width:140px;border-radius:4px;"></div>
                    </div>
                    <div style="padding:1.25rem;">
                        ${[0,1,2].map(() => `
                        <div style="display:flex;gap:0.65rem;margin-bottom:1rem;">
                            <div class="sk" style="width:20px;height:20px;border-radius:50%;flex-shrink:0;margin-top:1px;"></div>
                            <div style="flex:1;">
                                <div class="sk" style="height:11px;width:80px;margin-bottom:5px;border-radius:4px;"></div>
                                <div class="sk" style="height:10px;width:170px;border-radius:4px;"></div>
                            </div>
                        </div>`).join('')}
                    </div>
                </div>
            </div>
        </div>
    </div>`;
}

function showSkeleton(url) {
    const main = document.getElementById('mainContent');
    main.style.opacity = '1';
    main.style.transform = 'none';

    if (url.includes('/dashboard')) {
        main.innerHTML = skeletonDashboard();
    } else if (url.includes('create-faculty') || url.includes('create-teacher') || url.includes('profile/edit') || url.includes('profile/change-password')) {
        main.innerHTML = skeletonForm();
    } else if (url.includes('/users')) {
        main.innerHTML = skeletonUsers();
    } else if (url.includes('/audit-logs')) {
        main.innerHTML = skeletonAuditLogs();
    } else {
        main.innerHTML = skeletonDefault();
    }
}


function spaNavigate(url) {
    const curMain = document.getElementById('mainContent');

    curMain.style.transition = '';
    curMain.style.opacity    = '1';
    curMain.style.transform  = 'none';

    showSkeleton(url);

    fetch(url, { headers: { 'X-SPA-Request': '1' }, cache: 'no-store' })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(html => {
            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const newMain = doc.getElementById('mainContent');

            if (!newMain) {
                window.location.href = url;
                return;
            }

            curMain.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
            curMain.style.opacity    = '0';
            curMain.style.transform  = 'translateY(8px)';

            setTimeout(() => {
                curMain.innerHTML = '';
                Array.from(newMain.childNodes).forEach(node => {
                    if (node.nodeName !== 'SCRIPT') {
                        curMain.appendChild(node.cloneNode(true));
                    }
                });

                const scripts = Array.from(newMain.querySelectorAll('script'));

                function runNextScript(i) {
                    if (i >= scripts.length) {
                        requestAnimationFrame(() => {
                            curMain.style.transition = 'opacity 0.22s ease, transform 0.22s ease';
                            curMain.style.opacity    = '1';
                            curMain.style.transform  = 'translateY(0)';
                            curMain.scrollTop        = 0;
                        });
                        finishLoadBar();
                        applyCollapsedState(false);
                        updateActiveLink();
                        closeSidebar();
                        hideFloatTooltip();
                        return;
                    }

                    const oldScript = scripts[i];
                    const newScript = document.createElement('script');

                    if (oldScript.src) {
                        newScript.src     = oldScript.src;
                        newScript.onload  = () => runNextScript(i + 1);
                        newScript.onerror = () => runNextScript(i + 1);
                        document.body.appendChild(newScript);
                    } else {
                        try {
                            newScript.textContent = oldScript.textContent;
                            document.body.appendChild(newScript);
                        } catch (err) {
                            console.warn('Script error:', err);
                        }
                        runNextScript(i + 1);
                    }
                }

                runNextScript(0);

            }, 160);
        })
        .catch(err => {
            console.error('spaNavigate error:', err);
            curMain.style.opacity   = '1';
            curMain.style.transform = 'none';
            finishLoadBar();
            window.location.href = url;
        });
}
// Intercept all nav-link clicks
// Loading bar
const loadBar = document.createElement('div');
loadBar.style.cssText = `
    position:fixed; top:0; left:0; height:2px; width:0%;
    background:linear-gradient(90deg, var(--blue-400), var(--blue-600));
    z-index:9999; transition:width .3s ease, opacity .3s ease;
    pointer-events:none;
`;
document.body.appendChild(loadBar);

function showLoadBar() {
    loadBar.style.opacity = '1';
    loadBar.style.width   = '70%';
}
function finishLoadBar() {
    loadBar.style.width   = '100%';
    setTimeout(() => {
        loadBar.style.opacity = '0';
        setTimeout(() => { loadBar.style.width = '0%'; }, 300);
    }, 150);
}

// Intercept all nav-link clicks
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (!href || href.startsWith('#')) return;
        e.preventDefault();
        history.pushState(null, '', href);
        updateActiveLink();
        spaNavigate(href);
    });
});
// Logout button — skip SPA


// Browser back/forward
window.addEventListener('popstate', () => {
    updateActiveLink();
    spaNavigate(window.location.pathname);
});

    /* ═══════════════════════════════════════
       PAGE ENTRANCE ANIMATION (initial load only)
    ═══════════════════════════════════════ */
    anime({
        targets: '.sidebar-brand, .nav-section-wrapper, .nav-link',
        opacity: [0, 1],
        translateX: [-10, 0],
        duration: 400,
        easing: 'easeOutQuad',
        delay: anime.stagger(40, { start: 60 })
    });

document.getElementById('mainContent').addEventListener('click', function(e) {
    const link = e.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href');

    if (!href || href.startsWith('#') || href.startsWith('http')) return;
    if (link.hasAttribute('data-no-spa')) return;
    if (link.closest('form')) return;

    e.preventDefault();
    history.pushState(null, '', href);
    updateActiveLink();
    spaNavigate(href);
});

})();



</script>


<script>
(function () {
    var t = document.getElementById('appToast');
    if (!t) return;
    requestAnimationFrame(function () { t.classList.add('show'); });
    setTimeout(function () {
        t.classList.remove('show');
        setTimeout(function () { if (t && t.parentNode) t.parentNode.removeChild(t); }, 480);
    }, 3000);
})();
</script>

<script>
(function () {
    var btn = document.getElementById('darkToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        if (dark) {
            document.documentElement.removeAttribute('data-theme');
            try { localStorage.setItem('admin-theme', 'light'); } catch (e) {}
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            try { localStorage.setItem('admin-theme', 'dark'); } catch (e) {}
        }
    });
})();
</script>

{{-- ════════ EXPORT DATA MODAL ════════ --}}
<div class="modal-backdrop" id="exportModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon" style="background:var(--blue-50);color:var(--blue-600);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
        </div>
        <div class="modal-title">Export Data</div>
        <p class="modal-desc">Download system records as CSV files (opens in Excel / Sheets).</p>
        <div style="display:flex;flex-direction:column;gap:10px;margin:6px 0 4px;">
            <a href="{{ route('admin.export.users') }}" class="af-export-opt">
                <span>Users</span><span class="af-export-go">Download CSV ↓</span>
            </a>
            <a href="{{ route('admin.export.enrollments') }}" class="af-export-opt">
                <span>Enrollment Requests</span><span class="af-export-go">Download CSV ↓</span>
            </a>
        </div>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="exportClose" style="width:100%;">Close</button>
        </div>
    </div>
</div>

{{-- ════════ NOTIFICATION PREFERENCES MODAL ════════ --}}
<div class="modal-backdrop" id="notifPrefModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon" style="background:var(--blue-50);color:var(--blue-600);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div class="modal-title">Notification Preferences</div>
        <p class="modal-desc">Choose which alerts you want to receive in the admin panel.</p>
        <div class="np-list">
            <label class="np-row"><span>New enrollment requests</span><input type="checkbox" class="np-toggle" data-pref="enroll"></label>
            <label class="np-row"><span>Face verification requests</span><input type="checkbox" class="np-toggle" data-pref="face"></label>
            <label class="np-row"><span>New user registrations</span><input type="checkbox" class="np-toggle" data-pref="users"></label>
            <label class="np-row"><span>System updates</span><input type="checkbox" class="np-toggle" data-pref="system"></label>
        </div>
        <div class="modal-actions">
            <button class="btn btn-ghost" id="notifPrefClose">Cancel</button>
            <button class="btn btn-danger" id="notifPrefSave" style="background:var(--blue-600);box-shadow:none;">Save</button>
        </div>
    </div>
</div>

<style>
    .af-export-opt {
        display:flex; align-items:center; justify-content:space-between;
        padding:13px 15px; border:1px solid var(--border-default); border-radius:12px;
        text-decoration:none; color:var(--slate-800); font-weight:600; font-size:13.5px;
        background:var(--surface-card); transition:border-color .15s, background .15s;
    }
    .af-export-opt:hover { border-color:var(--blue-500); background:var(--blue-50); }
    .af-export-go { font-size:12px; font-weight:600; color:var(--blue-600); }
    .np-list { display:flex; flex-direction:column; margin:4px 0; text-align:left; }
    .np-row {
        display:flex; align-items:center; justify-content:space-between;
        padding:11px 2px; border-bottom:1px solid var(--border-default);
        font-size:13.5px; color:var(--slate-700); cursor:pointer;
    }
    .np-row:last-child { border-bottom:none; }
    .np-toggle {
        appearance:none; -webkit-appearance:none; width:40px; height:22px; border-radius:22px;
        background:var(--slate-300); position:relative; cursor:pointer; transition:background .2s; flex-shrink:0;
    }
    .np-toggle::after {
        content:''; position:absolute; top:2px; left:2px; width:18px; height:18px; border-radius:50%;
        background:#fff; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.25);
    }
    .np-toggle:checked { background:var(--blue-500); }
    .np-toggle:checked::after { transform:translateX(18px); }
</style>

<script>
(function () {
    function bindModal(btnId, modalId, closeId) {
        var btn = document.getElementById(btnId);
        var modal = document.getElementById(modalId);
        var close = document.getElementById(closeId);
        if (!btn || !modal) return null;
        btn.addEventListener('click', function () { modal.classList.add('open'); });
        if (close) close.addEventListener('click', function () { modal.classList.remove('open'); });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('open'); });
        return modal;
    }

    // Export modal
    bindModal('exportDataBtn', 'exportModal', 'exportClose');
    var em = document.getElementById('exportModal');
    if (em) em.querySelectorAll('.af-export-opt').forEach(function (a) {
        a.addEventListener('click', function () { setTimeout(function(){ em.classList.remove('open'); }, 300); });
    });

    // Notification preferences modal (persisted in localStorage)
    var npModal = bindModal('notifPrefBtn', 'notifPrefModal', 'notifPrefClose');
    if (npModal) {
        var KEY = 'admin-notif-prefs';
        var npBtn = document.getElementById('notifPrefBtn');
        var save = document.getElementById('notifPrefSave');
        function load() {
            var prefs = {};
            try { prefs = JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) {}
            document.querySelectorAll('.np-toggle').forEach(function (t) {
                var k = t.getAttribute('data-pref');
                t.checked = (k in prefs) ? !!prefs[k] : true; // default: ON
            });
        }
        npBtn.addEventListener('click', load);
        if (save) save.addEventListener('click', function () {
            var prefs = {};
            document.querySelectorAll('.np-toggle').forEach(function (t) { prefs[t.getAttribute('data-pref')] = t.checked; });
            try { localStorage.setItem(KEY, JSON.stringify(prefs)); } catch (e) {}
            var orig = save.textContent; save.textContent = 'Saved \u2713';
            setTimeout(function () { npModal.classList.remove('open'); save.textContent = orig; }, 650);
        });
    }

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var m1 = document.getElementById('exportModal'); if (m1) m1.classList.remove('open');
            var m2 = document.getElementById('notifPrefModal'); if (m2) m2.classList.remove('open');
        }
    });
})();
</script>

@include('partials.flash-toast')
</body>
</html>