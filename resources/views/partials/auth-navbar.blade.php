{{--
    Top navigation bar for the Login / Register pages.
    Self-contained: its own scoped CSS (dpnav-*) + vanilla JS. No framework needed.
    Usage:  add this ONE line right after <body> in login.blade.php and register.blade.php
            @include('partials.auth-navbar')
--}}

<style>
    /* ── Scoped top nav (dpnav-*) ─────────────────────────────────────────── */
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
        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.10);
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
        background: transparent; padding: 0;
        flex-shrink: 0;
    }
    .dpnav-brand .dpnav-txt { line-height: 1.15; min-width: 0; }
    .dpnav-brand .dpnav-name {
        font-size: 18px; font-weight: 800; color: #14532d;
        letter-spacing: 0.2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dpnav-brand .dpnav-sub {
        font-size: 11.5px; font-weight: 500; color: #6b7280;
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
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.18);
    }
    .dpnav-link:active { transform: translateY(0) scale(0.97); }

    /* Active page pill (solid brand blue) */
    .dpnav-link.is-active {
        background: linear-gradient(135deg, #16a34a, #15803d);
        border-color: transparent;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(22, 163, 74, 0.45);
    }
    .dpnav-link.is-active:hover { background: linear-gradient(135deg, #16a34a, #15803d); }

    /* About modal */
    .dpnav-modal {
        position: fixed; inset: 0; z-index: 4000;
        display: flex; align-items: center; justify-content: center;
        padding: 20px;
        background: rgba(6, 12, 26, 0.55);
        backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
        opacity: 0; pointer-events: none;
        transition: opacity 0.28s ease;
    }
    .dpnav-modal.show { opacity: 1; pointer-events: all; }
    .dpnav-modal-card {
        width: 100%; max-width: 440px;
        background: #fff; border-radius: 22px;
        padding: 28px 26px 24px;
        box-shadow: 0 30px 70px rgba(0, 0, 0, 0.4);
        transform: translateY(16px) scale(0.97);
        transition: transform 0.32s cubic-bezier(0.23, 1, 0.32, 1);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        max-height: 88vh; overflow-y: auto;
    }
    .dpnav-modal.show .dpnav-modal-card { transform: translateY(0) scale(1); }
    .dpnav-modal-head { display: flex; align-items: center; gap: 13px; margin-bottom: 14px; }
    .dpnav-modal-head img {
        width: 52px; height: 52px; object-fit: contain;
        border-radius: 13px; background: #f2f5fa; padding: 6px;
    }
    .dpnav-modal-head h3 { font-size: 18px; font-weight: 800; color: #12203a; margin: 0; }
    .dpnav-modal-head p  { font-size: 12.5px; color: #6b7280; margin: 2px 0 0; }
    .dpnav-modal-body p {
        font-size: 13.5px; line-height: 1.7; color: #374151; margin: 0 0 12px;
    }
    .dpnav-feat { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
    .dpnav-feat svg { width: 18px; height: 18px; color: #16a34a; flex-shrink: 0; margin-top: 1px; }
    .dpnav-feat span { font-size: 13px; color: #374151; line-height: 1.5; }
    .dpnav-modal-close {
        margin-top: 8px; width: 100%;
        padding: 12px; border: none; border-radius: 13px;
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: #fff; font-size: 14px; font-weight: 700; cursor: pointer;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }
    .dpnav-modal-close:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(22,163,74,0.4); }

    /* Push page content down a touch so the fixed bar never hides the top */
    body { scroll-padding-top: 76px; }

    /* Responsive */
    @media (max-width: 560px) {
        .dpnav { padding: 10px 14px; }
        .dpnav-brand .dpnav-sub { display: none; }
        .dpnav-brand img { width: 46px; height: 46px; }
        .dpnav-brand .dpnav-name { font-size: 16px; }
        .dpnav-link { padding: 8px 11px; font-size: 12px; }
        .dpnav-link .dpnav-label { display: none; }   /* icons only on small screens */
        .dpnav-link svg { width: 18px; height: 18px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dpnav { animation: none; opacity: 1; transform: none; }
        .dpnav-modal, .dpnav-modal-card { transition: none; }
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
           class="dpnav-link dpnav-nav {{ request()->routeIs('login') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            <span class="dpnav-label">Login</span>
        </a>

        {{-- Register --}}
        <a href="{{ route('register') }}"
           class="dpnav-link dpnav-nav {{ request()->routeIs('register') ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            <span class="dpnav-label">Register</span>
        </a>

        {{-- About (opens modal) --}}
        <button type="button" class="dpnav-link" id="dpnavAboutBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span class="dpnav-label">About</span>
        </button>
    </div>
</nav>

{{-- About modal --}}
<div class="dpnav-modal" id="dpnavModal" role="dialog" aria-modal="true" aria-labelledby="dpnavAboutTitle">
    <div class="dpnav-modal-card">
        <div class="dpnav-modal-head">
            <img src="{{ asset('images/logo.png') }}" alt="School logo">
            <div>
                <h3 id="dpnavAboutTitle">Sto. Domingo NHS</h3>
                <p>DP-LMS &middot; Digital Portal &amp; Learning Management System</p>
            </div>
        </div>
        <div class="dpnav-modal-body">
            <p>DP-LMS is the official learning-management portal of Sto. Domingo National
               High School. It connects students, teachers, and parents in one secure place.</p>

            <div class="dpnav-feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Learning materials, quizzes, and grades — anytime, anywhere.</span>
            </div>
            <div class="dpnav-feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Face-recognition attendance with present, late, and absent tracking.</span>
            </div>
            <div class="dpnav-feat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span>Instant notifications for announcements, activities, and results.</span>
            </div>

            <p style="margin-top:14px;font-size:12px;color:#9ca3af;">
                &copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.
            </p>
        </div>
        <button type="button" class="dpnav-modal-close" id="dpnavModalClose">Got it</button>
    </div>
</div>

<script>
(function () {
    // ── About modal open / close ───────────────────────────────────────────
    var modal = document.getElementById('dpnavModal');
    var openBtn = document.getElementById('dpnavAboutBtn');
    var closeBtn = document.getElementById('dpnavModalClose');

    function openModal()  { modal.classList.add('show'); }
    function closeModal() { modal.classList.remove('show'); }

    if (openBtn)  openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
})();
</script>