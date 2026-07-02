{{--
    Top navigation bar for the Login / Register / About pages.
    Self-contained: its own scoped CSS (dpnav-*). No JS needed.
    Usage:  add this ONE line right after <body>:
            @include('partials.auth-navbar')
--}}

<style>
    .dpnav {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 7px 22px;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        opacity: 0;
        transform: translateY(-14px);
        animation: dpnavDrop 0.6s cubic-bezier(0.23, 1, 0.32, 1) 0.1s forwards;
    }
    @keyframes dpnavDrop { to { opacity: 1; transform: translateY(0); } }

    /* Brand (logo + school name) */
    .dpnav-brand { display: flex; align-items: center; gap: 11px; min-width: 0; }
    .dpnav-brand img {
        width: 44px; height: 44px; object-fit: contain;
        flex-shrink: 0;
    }
    .dpnav-txt { min-width: 0; line-height: 1.15; }
    .dpnav-name {
        font-size: 17px; font-weight: 800; color: #14532d;
        letter-spacing: 0.2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dpnav-sub {
        font-size: 11px; font-weight: 500; color: #64748b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Nav links */
    .dpnav-links { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .dpnav-link {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 13px; border-radius: 11px;
        font-size: 13px; font-weight: 700; letter-spacing: 0.2px;
        color: #15803d; text-decoration: none; cursor: pointer;
        background: #f0fdf4;
        border: 1px solid #dcfce7;
        transition: transform 0.18s cubic-bezier(0.23,1,0.32,1),
                    background 0.18s ease, box-shadow 0.18s ease;
    }
    .dpnav-link svg { width: 17px; height: 17px; flex-shrink: 0; }
    .dpnav-link:hover {
        transform: translateY(-2px);
        background: #dcfce7;
        box-shadow: 0 6px 14px rgba(22, 163, 74, 0.18);
    }
    .dpnav-link:active { transform: translateY(0) scale(0.97); }

    /* Active page pill (solid brand green) */
    .dpnav-link.is-active {
        background: linear-gradient(135deg, #16a34a, #15803d);
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.45);
    }
    .dpnav-link.is-active:hover { background: linear-gradient(135deg, #16a34a, #15803d); }

    /* Push page content down a touch so the fixed bar never hides the top */
    body { scroll-padding-top: 76px; }

    /* Responsive */
    @media (max-width: 560px) {
        .dpnav { padding: 7px 14px; }
        .dpnav-brand img { width: 40px; height: 40px; }
        .dpnav-name { font-size: 15px; }
        .dpnav-sub { display: none; }
        .dpnav-link { padding: 7px 10px; font-size: 12px; }
        .dpnav-link .dpnav-label { display: none; }   /* icons only on small screens */
        .dpnav-link svg { width: 18px; height: 18px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dpnav { animation: none; opacity: 1; transform: none; }
    }
</style>

<nav class="dpnav">
    <div class="dpnav-brand">
        <img src="{{ asset('images/logo.png') }}" alt="School logo">
        <div class="dpnav-txt">
            <div class="dpnav-name">SDNHS Portal</div>
            <div class="dpnav-sub">Sto. Domingo National High School</div>
        </div>
    </div>

    <div class="dpnav-links">
        {{-- Login --}}
        <a href="{{ route('login') }}"
           class="dpnav-link {{ request()->routeIs('login') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            <span class="dpnav-label">Login</span>
        </a>

        {{-- Register --}}
        <a href="{{ route('register') }}"
           class="dpnav-link {{ request()->routeIs('register') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            <span class="dpnav-label">Register</span>
        </a>

        {{-- About (page) --}}
        <a href="{{ route('about') }}"
           class="dpnav-link {{ request()->routeIs('about') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span class="dpnav-label">About</span>
        </a>
    </div>
</nav>