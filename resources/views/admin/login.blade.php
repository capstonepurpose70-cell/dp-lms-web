<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Administrator sign in — Sto. Domingo NHS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="{{ asset('images/logo.png') }}">
    <style>
        :root {
            --navy:   #0E2A5C;
            --navy-2: #163B7A;
            --gold:   #E8B23A;
            --blue:   #1D4ED8;
            --blue-2: #1E40AF;
            --ink:    #0F172A;
            --slate:  #475569;
            --muted:  #64748B;
            --line:   #CBD5E1;
            --paper:  #F5F7FA;
            --danger: #B91C1C;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink); background: var(--paper);
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
        }

        .page { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: minmax(380px, 44%) 1fr; }

        /* ── School panel ── */
        .school {
            position: relative; overflow: hidden; isolation: isolate;
            background: var(--navy); color: #fff;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 48px 56px;
        }
        /* Rings taken from the round school seal — the one decorative element, drawn in CSS-sized SVG */
        .rings { position: absolute; right: -460px; top: -200px; width: 760px; height: 760px; z-index: -1; pointer-events: none; }
        .school-top { display: flex; align-items: center; gap: 14px; }
        .seal { width: 64px; height: 64px; border-radius: 50%; background: #fff; padding: 3px; flex-shrink: 0; }
        .seal img { width: 100%; height: 100%; display: block; border-radius: 50%; }
        .school-tag { font-size: 14px; font-weight: 600; color: #C7D4EE; line-height: 1.35; }
        .school-tag b { display: block; color: #fff; font-size: 15px; font-weight: 800; }

        .school-name { font-size: 40px; font-weight: 800; line-height: 1.1; letter-spacing: -.02em; max-width: 12ch; text-wrap: balance; }
        .school-place { margin-top: 14px; font-size: 16px; color: #C7D4EE; }
        .school-rule { display: block; width: 48px; height: 3px; border-radius: 2px; background: var(--gold); margin-bottom: 22px; }

        .school-foot { font-size: 13.5px; color: #C7D4EE; line-height: 1.55; max-width: 36ch; }
        .school-foot b { color: #fff; font-weight: 700; }

        /* ── Form side ── */
        .side { display: flex; align-items: center; justify-content: center; padding: 48px 24px; }
        .panel { width: 100%; max-width: 400px; }
        .panel h1 { font-size: 28px; font-weight: 800; letter-spacing: -.02em; line-height: 1.2; }
        .lead { margin-top: 8px; font-size: 15px; color: var(--slate); line-height: 1.55; }

        .notice { display: flex; gap: 10px; align-items: flex-start; margin-top: 24px; padding: 12px 14px; border-radius: 10px; font-size: 14px; line-height: 1.5; }
        .notice svg { flex-shrink: 0; margin-top: 1px; }
        .notice.error { background: #FEF2F2; border: 1px solid #FECACA; color: var(--danger); }
        .notice.ok { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }

        form { margin-top: 28px; display: grid; gap: 20px; }
        .field label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; }
        .input {
            width: 100%; height: 48px; padding: 0 14px;
            border: 1px solid var(--line); border-radius: 10px; background: #fff;
            font: inherit; font-size: 15px; color: var(--ink);
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .input::placeholder { color: #94A3B8; }
        .input:hover { border-color: #94A3B8; }
        .input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(29,78,216,.18); }
        .input[aria-invalid="true"] { border-color: var(--danger); }
        .field-error { margin-top: 6px; font-size: 13px; color: var(--danger); }

        .pw { position: relative; }
        .pw .input { padding-right: 52px; }
        .pw-toggle {
            position: absolute; right: 4px; top: 4px; width: 40px; height: 40px; border-radius: 8px;
            border: 0; background: transparent; color: var(--muted); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .pw-toggle:hover { background: #F1F5F9; color: var(--ink); }
        .pw-toggle .off, .pw-toggle[aria-pressed="true"] .on { display: none; }
        .pw-toggle[aria-pressed="true"] .off { display: block; }
        .caps { margin-top: 6px; font-size: 13px; font-weight: 600; color: #92400E; }

        .submit {
            height: 48px; border: 0; border-radius: 10px; background: var(--blue); color: #fff;
            font: inherit; font-size: 15px; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            transition: background-color .15s ease, transform .1s ease;
        }
        .submit:hover { background: var(--blue-2); }
        .submit:active { transform: scale(.985); }
        .submit:disabled { cursor: progress; background: var(--blue-2); }
        .spinner { width: 18px; height: 18px; border-radius: 50%; border: 2px solid rgba(255,255,255,.35); border-top-color: #fff; display: none; animation: spin .7s linear infinite; }
        .submit.loading .spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .lock { margin-top: 28px; padding: 22px; border: 1px solid #FDE68A; border-radius: 12px; background: #FFFBEB; }
        .lock h2 { font-size: 17px; font-weight: 800; color: #78350F; }
        .lock p { margin-top: 6px; font-size: 14px; color: #92400E; line-height: 1.55; }
        .lock-time { margin-top: 14px; font-size: 32px; font-weight: 800; color: #78350F; font-variant-numeric: tabular-nums; letter-spacing: -.01em; }
        .lock-bar { margin-top: 12px; height: 6px; border-radius: 3px; background: #FDE68A; overflow: hidden; }
        .lock-bar span { display: block; height: 100%; background: #D97706; transition: width 1s linear; }

        .other { margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; font-size: 14px; color: var(--slate); line-height: 1.55; }
        .other a { color: var(--blue); font-weight: 700; text-decoration: none; }
        .other a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .copy { margin-top: 16px; font-size: 13px; color: var(--muted); }

        :focus-visible { outline: 3px solid var(--blue); outline-offset: 2px; }
        .school :focus-visible { outline-color: #93C5FD; }

        @media (max-width: 960px) {
            .page { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .school { padding: 24px; gap: 18px; }
            .rings { width: 420px; height: 420px; right: -170px; top: auto; bottom: -230px; }
            .seal { width: 52px; height: 52px; }
            .school-name { font-size: 24px; max-width: none; }
            .school-place { margin-top: 6px; font-size: 14px; }
            .school-rule, .school-foot { display: none; }
            .side { align-items: flex-start; padding: 32px 20px 40px; }
            .panel h1 { font-size: 24px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .001ms !important; animation-duration: .001ms !important; }
        }
    </style>
</head>
<body>
<main class="page">

    <section class="school" aria-label="Sto. Domingo National High School">
        <svg class="rings" viewBox="0 0 760 760" fill="none" aria-hidden="true">
            @foreach ([370, 310, 250, 190, 130] as $r)
                <circle cx="380" cy="380" r="{{ $r }}" stroke="#FFFFFF" stroke-opacity="{{ $r === 250 ? 0 : .09 }}" stroke-width="1.5"/>
            @endforeach
            <circle cx="380" cy="380" r="250" stroke="#E8B23A" stroke-opacity=".55" stroke-width="2"/>
        </svg>

        <div class="school-top">
            <span class="seal"><img src="{{ asset('images/logo.png') }}" alt="" width="58" height="58" decoding="async" fetchpriority="high"></span>
            <p class="school-tag"><b>DP-LMS</b>Administration</p>
        </div>

        <div>
            <span class="school-rule" aria-hidden="true"></span>
            <p class="school-name">Sto. Domingo National High School</p>
            <p class="school-place">San Manuel, Pangasinan</p>
        </div>

        <p class="school-foot"><b>For school administrators only.</b> Every sign-in and failed attempt on this page is recorded in the audit log.</p>
    </section>

    <section class="side">
        <div class="panel">
            <h1>Administrator sign in</h1>
            <p class="lead">Use the email and password of your administrator account.</p>

            @if ($lockSeconds > 0)
                <div class="lock" role="alert" id="lock" data-seconds="{{ $lockSeconds }}">
                    <h2>Sign-in is paused</h2>
                    <p>There were too many incorrect attempts. For security, you can try again when the timer ends.</p>
                    <p class="lock-time" id="lockTime" aria-live="off">{{ sprintf('%02d:%02d', intdiv($lockSeconds, 60), $lockSeconds % 60) }}</p>
                    <div class="lock-bar" aria-hidden="true"><span id="lockBar" style="width:100%;"></span></div>
                </div>
            @else
                @if (session('error'))
                    <div class="notice error" role="alert">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @elseif (session('signed_out'))
                    <div class="notice ok" role="status">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>You have signed out.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm" novalidate @if (session('signed_out')) data-signed-out @endif>
                    @csrf
                    <div class="field">
                        <label for="admin-email">Email</label>
                        <input class="input" type="email" id="admin-email" name="email" value="{{ old('email') }}"
                               autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required
                               placeholder="name@school.edu.ph"
                               @error('email') aria-invalid="true" aria-describedby="err-email" @enderror>
                        @error('email') <p class="field-error" id="err-email">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label for="admin-pw">Password</label>
                        <div class="pw">
                            <input class="input" type="password" id="admin-pw" name="password"
                                   autocomplete="current-password" required
                                   @error('password') aria-invalid="true" aria-describedby="err-pw" @enderror>
                            <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password" aria-pressed="false">
                                <svg class="on" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="off" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A10.6 10.6 0 0112 5c6 0 9.5 7 9.5 7a17 17 0 01-3.1 4M6.6 6.6A16.8 16.8 0 002.5 12S6 19 12 19a9.8 9.8 0 005.4-1.6M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                            </button>
                        </div>
                        <p class="caps" id="capsNote" hidden>Caps Lock is on.</p>
                        @error('password') <p class="field-error" id="err-pw">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="submit" id="adminSubmit">
                        <span class="spinner" aria-hidden="true"></span>
                        <span id="adminSubmitText">Sign in</span>
                    </button>
                </form>
            @endif

            <p class="other">Students, teachers and parents sign in on the <a href="{{ route('login') }}">main sign-in page</a>.</p>
            <p class="copy">&copy; {{ date('Y') }} Sto. Domingo National High School</p>
        </div>
    </section>

</main>

<script>
(function () {
    const form = document.getElementById('adminLoginForm');
    const pw = document.getElementById('admin-pw');
    const toggle = document.getElementById('pwToggle');
    const submit = document.getElementById('adminSubmit');

    function showPassword(on) {
        if (!pw) return;
        pw.type = on ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', on ? 'true' : 'false');
        toggle.setAttribute('aria-label', on ? 'Hide password' : 'Show password');
    }

    if (form) {
        toggle.addEventListener('click', () => { showPassword(pw.type === 'password'); pw.focus(); });

        const caps = document.getElementById('capsNote');
        const checkCaps = (e) => { if (e.getModifierState) caps.hidden = !e.getModifierState('CapsLock'); };
        pw.addEventListener('keydown', checkCaps);
        pw.addEventListener('keyup', checkCaps);
        pw.addEventListener('blur', () => { caps.hidden = true; });

        form.addEventListener('submit', (e) => {
            const email = document.getElementById('admin-email');
            if (!email.value.trim() || !pw.value) {
                e.preventDefault();
                (email.value.trim() ? pw : email).focus();
                return;
            }
            if (submit.disabled) { e.preventDefault(); return; }
            submit.disabled = true;
            submit.classList.add('loading');
            document.getElementById('adminSubmitText').textContent = 'Signing in…';
        });

        // Desktop only: on phones this would open the keyboard over the page right away
        if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
            const email = document.getElementById('admin-email');
            (email.value ? pw : email).focus();
        }
    }

    /* Clear the form right after logout, and whenever Back/Forward brings this page back with the
       typed email and password still in it (the password possibly shown as text). Also re-enable
       the button if the page comes back from the back/forward cache mid-submit. */
    window.addEventListener('pageshow', (e) => {
        if (!form) return;
        submit.disabled = false;
        submit.classList.remove('loading');
        document.getElementById('adminSubmitText').textContent = 'Sign in';
        const nav = performance.getEntriesByType('navigation')[0];
        if (!(e.persisted || (nav && nav.type === 'back_forward') || form.hasAttribute('data-signed-out'))) return;
        form.reset();
        pw.value = '';
        showPassword(false);
    });

    // Lockout: the time comes from the server; when it ends, reload so the server decides again
    const lock = document.getElementById('lock');
    if (lock) {
        const total = parseInt(lock.dataset.seconds, 10) || 0;
        const end = Date.now() + total * 1000;
        const time = document.getElementById('lockTime');
        const bar = document.getElementById('lockBar');
        const tick = () => {
            const left = Math.max(0, Math.round((end - Date.now()) / 1000));
            time.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
            bar.style.width = (total ? (left / total) * 100 : 0) + '%';
            if (left <= 0) { clearInterval(timer); window.location.replace(window.location.pathname); }
        };
        const timer = setInterval(tick, 1000);
        tick();
    }
})();
</script>
</body>
</html>
