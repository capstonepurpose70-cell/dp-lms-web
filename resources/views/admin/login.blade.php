<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Administrator sign in — Sto. Domingo NHS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="{{ asset('images/bg.jpg') }}">
    <style>
        :root {
            --navy:   #12305F;
            --deep:   #0C2347;
            --red:    #B3262B;
            --ink:    #14202E;
            --slate:  #4A5768;
            --muted:  #66727F;
            --line:   #C5CDD8;
            --wash:   #F2F4F7;
            --danger: #B3261E;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'IBM Plex Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink); background: #fff;
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
        }

        .page { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(460px, 1fr); }

        /* Left: the school in navy, with its name on a solid plate */
        .photo { position: relative; overflow: hidden; isolation: isolate; background: var(--navy); color: #fff; }
        .photo img.scene {
            position: absolute; inset: 0; width: 100%; height: 100%;
            object-fit: cover; object-position: 100% 50%;
            mix-blend-mode: luminosity; filter: contrast(1.02) brightness(1.3);
        }
        /* Keeps the name beside the seal readable on bright parts of the photo */
        .photo::after { content: ''; position: absolute; inset: 0 0 auto; height: 150px; background: linear-gradient(rgba(12,35,71,.72), rgba(12,35,71,0)); }
        .brand, .plate { z-index: 1; }
        .brand { position: absolute; top: 32px; left: 40px; display: flex; align-items: center; gap: 12px; }
        .seal { width: 60px; height: 60px; border-radius: 50%; background: #fff; padding: 3px; flex-shrink: 0; }
        .seal img { width: 100%; height: 100%; display: block; border-radius: 50%; }
        .brand p { font-size: 13.5px; line-height: 1.35; font-weight: 500; text-shadow: 0 1px 3px rgba(12,35,71,.7); }
        .brand b { display: block; font-size: 16px; font-weight: 700; }
        .plate {
            position: absolute; left: 0; bottom: 0; width: min(600px, 88%);
            padding: 30px 40px 32px; background: var(--deep); border-top: 4px solid var(--red);
        }
        .plate-name { font-size: 34px; font-weight: 700; line-height: 1.14; letter-spacing: -.015em; text-wrap: balance; }
        .plate-place { margin-top: 8px; font-size: 15px; color: #C9D4E5; }
        .plate-note { margin-top: 16px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.18); font-size: 13.5px; color: #C9D4E5; line-height: 1.5; max-width: 52ch; }
        .plate-note b { color: #fff; font-weight: 600; }

        /* Right: the form */
        .side { display: flex; align-items: center; justify-content: center; padding: 48px 32px; background: #fff; }
        .panel { width: 100%; max-width: 372px; }
        .panel h1 { font-size: 28px; font-weight: 700; letter-spacing: -.015em; line-height: 1.2; }
        .lead { margin-top: 8px; font-size: 15px; color: var(--slate); line-height: 1.5; text-wrap: pretty; }

        .notice { display: flex; gap: 10px; align-items: flex-start; margin-top: 22px; padding: 11px 13px; border-radius: 6px; font-size: 14px; line-height: 1.5; }
        .notice svg { flex-shrink: 0; margin-top: 1px; }
        .notice.error { background: #FDF3F2; border: 1px solid #F1C9C5; color: var(--danger); }
        .notice.ok { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }

        form { margin-top: 26px; display: grid; gap: 18px; }
        form.shake { animation: shake .42s cubic-bezier(.36,.07,.19,.97) .1s both; }
        .field label { display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 6px; }
        .control { position: relative; }
        .input {
            width: 100%; height: 48px; padding: 0 13px;
            border: 1px solid var(--line); border-radius: 6px; background: #fff;
            font: inherit; font-size: 15px; color: var(--ink);
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .input::placeholder { color: #8591A0; }
        .input:hover { border-color: #97A3B2; }
        .input:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 3px rgba(18,48,95,.18); }
        .input[aria-invalid="true"] { border-color: var(--danger); }
        .field-error { margin-top: 6px; font-size: 13px; color: var(--danger); }
        .pw .input { padding-right: 50px; }
        .pw-toggle {
            position: absolute; right: 4px; top: 4px; width: 40px; height: 40px; border-radius: 4px;
            border: 0; background: transparent; color: var(--muted); cursor: pointer; display: grid; place-items: center;
            transition: background-color .15s ease, color .15s ease;
        }
        .pw-toggle:hover { background: var(--wash); color: var(--ink); }
        .pw-toggle svg { grid-area: 1 / 1; }
        .pw-toggle .off, .pw-toggle[aria-pressed="true"] .on { visibility: hidden; }
        .pw-toggle[aria-pressed="true"] .off { visibility: visible; }
        .caps { margin-top: 6px; font-size: 13px; font-weight: 600; color: #92400E; }

        .submit {
            height: 48px; margin-top: 4px; border: 0; border-radius: 6px; background: var(--navy); color: #fff;
            font: inherit; font-size: 15px; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            transition: background-color .15s ease;
        }
        .submit:hover { background: var(--deep); }
        .submit:disabled { cursor: progress; background: var(--deep); }
        .spinner { width: 18px; height: 18px; border-radius: 50%; border: 2px solid rgba(255,255,255,.35); border-top-color: #fff; display: none; animation: spin .7s linear infinite; }
        .submit.loading .spinner { display: block; }

        .lock { margin-top: 24px; padding: 20px; border: 1px solid #E9D8A6; border-radius: 6px; background: #FFFBEB; display: grid; grid-template-columns: auto 1fr; gap: 18px; align-items: center; }
        .lock-dial { position: relative; width: 96px; height: 96px; }
        .lock-dial svg { width: 96px; height: 96px; transform: rotate(-90deg); }
        .lock-dial circle { fill: none; stroke-width: 7; }
        .lock-dial .track { stroke: #F3E3B3; }
        .lock-dial .left { stroke: #B45309; stroke-dasharray: 1; transition: stroke-dashoffset 1s linear; }
        .lock-time { position: absolute; inset: 0; display: grid; place-items: center; font-size: 19px; font-weight: 700; color: #78350F; font-variant-numeric: tabular-nums; }
        .lock h2 { font-size: 16px; font-weight: 700; color: #78350F; }
        .lock p { margin-top: 5px; font-size: 14px; color: #92400E; line-height: 1.5; }

        .other { margin-top: 26px; padding-top: 18px; border-top: 1px solid #E2E6EC; font-size: 14px; color: var(--slate); line-height: 1.5; }
        .other a { color: var(--navy); font-weight: 600; text-underline-offset: 3px; }
        .copy { margin-top: 12px; font-size: 13px; color: var(--muted); }

        :focus-visible { outline: 2px solid var(--navy); outline-offset: 2px; }

        @keyframes spin  { to { transform: rotate(360deg); } }
        @keyframes shake { 10%, 90% { transform: translateX(-1px); } 20%, 80% { transform: translateX(3px); } 30%, 50%, 70% { transform: translateX(-6px); } 40%, 60% { transform: translateX(6px); } }

        @media (max-width: 960px) {
            .page { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .photo { height: 230px; }
            .brand { top: 16px; left: 20px; }
            .seal { width: 48px; height: 48px; }
            .plate { width: 100%; padding: 14px 20px 16px; border-top-width: 3px; }
            .plate-name { font-size: 20px; }
            .plate-place { margin-top: 2px; font-size: 13.5px; }
            .plate-note { display: none; }
            .side { align-items: flex-start; padding: 28px 20px 40px; }
            .panel { max-width: 440px; }
            .panel h1 { font-size: 25px; }
            .lock { grid-template-columns: 1fr; justify-items: start; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
        }
    </style>
</head>
<body>
<main class="page">

    <section class="photo" aria-label="Sto. Domingo National High School">
        <img class="scene" src="{{ asset('images/bg.jpg') }}" alt="" width="2048" height="907" decoding="async" fetchpriority="high">
        <div class="brand">
            <span class="seal"><img src="{{ asset('images/logo.png') }}" alt="" width="54" height="54" decoding="async"></span>
            <p><b>DP-LMS</b>Administration</p>
        </div>
        <div class="plate">
            <p class="plate-name">Sto. Domingo National High School</p>
            <p class="plate-place">San Manuel, Pangasinan</p>
            <p class="plate-note"><b>For school administrators only.</b> Every sign-in and failed attempt on this page is recorded in the audit log.</p>
        </div>
    </section>

    <section class="side">
        <div class="panel">
            <h1>Administrator sign in</h1>
            <p class="lead">Use the email and password of your administrator account.</p>

            @if ($lockSeconds > 0)
                <div class="lock" role="alert" id="lock" data-seconds="{{ $lockSeconds }}">
                    <div class="lock-dial" aria-hidden="true">
                        <svg viewBox="0 0 96 96"><circle class="track" cx="48" cy="48" r="42"/><circle class="left" id="lockRing" cx="48" cy="48" r="42" pathLength="1"/></svg>
                        <span class="lock-time" id="lockTime">{{ sprintf('%02d:%02d', intdiv($lockSeconds, 60), $lockSeconds % 60) }}</span>
                    </div>
                    <div>
                        <h2>Sign-in is paused</h2>
                        <p>There were too many incorrect attempts. For security, you can try again when the timer ends.</p>
                    </div>
                </div>
            @else
                @if (session('error'))
                    <div class="notice error" role="alert">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @elseif (session('signed_out'))
                    <div class="dp-flash" data-type="success" hidden>You have been signed out.</div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm" novalidate
                      class="{{ session('error') ? 'shake' : '' }}" @if (session('signed_out')) data-signed-out @endif>
                    @csrf
                    <div class="field">
                        <label for="admin-email">Email</label>
                        <div class="control">
                            <input class="input" type="email" id="admin-email" name="email" value="{{ old('email') }}"
                                   autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required
                                   placeholder="name@school.edu.ph"
                                   @error('email') aria-invalid="true" aria-describedby="err-email" @enderror>
                        </div>
                        @error('email') <p class="field-error" id="err-email">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label for="admin-pw">Password</label>
                        <div class="control pw">
                            <input class="input" type="password" id="admin-pw" name="password" placeholder="Your password"
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
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    function showPassword(on) {
        if (!pw) return;
        pw.type = on ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', on ? 'true' : 'false');
        toggle.setAttribute('aria-label', on ? 'Hide password' : 'Show password');
    }

    function resetButton() {
        if (!submit) return;
        submit.disabled = false;
        submit.classList.remove('loading');
        document.getElementById('adminSubmitText').textContent = 'Sign in';
    }

    if (form) {
        toggle.addEventListener('click', () => {
            showPassword(pw.type === 'password');
            pw.focus({ preventScroll: true });
            const end = pw.value.length;
            pw.setSelectionRange(end, end);
        });

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
                form.classList.remove('shake');
                void form.offsetWidth;            // restart the shake
                form.classList.add('shake');
                return;
            }
            if (submit.disabled) { e.preventDefault(); return; }
            submit.disabled = true;
            submit.classList.add('loading');
            document.getElementById('adminSubmitText').textContent = 'Signing in…';
        });
        form.addEventListener('animationend', (e) => { if (e.animationName === 'shake') form.classList.remove('shake'); });

        // Desktop only: on phones this would open the keyboard over the page right away
        if (finePointer) {
            const email = document.getElementById('admin-email');
            (email.value ? pw : email).focus();
        }
    }

    /* Clear the form right after logout, and whenever Back/Forward brings this page back with the
       typed email and password still in it (the password possibly shown as text). Also re-enable
       the button if the page comes back from the back/forward cache mid-submit. */
    window.addEventListener('pageshow', (e) => {
        if (!form) return;
        resetButton();
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
        const ring = document.getElementById('lockRing');
        const full = 1800;   // the dial shows the share of the 30-minute pause that is left
        const tick = () => {
            const left = Math.max(0, Math.round((end - Date.now()) / 1000));
            time.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
            ring.style.strokeDashoffset = String(1 - Math.min(1, left / full));
            if (left <= 0) { clearInterval(timer); window.location.replace(window.location.pathname); }
        };
        const timer = setInterval(tick, 1000);
        tick();
    }
})();
</script>
@include('partials.flash-toast')
</body>
</html>
