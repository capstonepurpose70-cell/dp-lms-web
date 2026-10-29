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
            --gold:   #E8B23A;
            --blue:   #1D4ED8;
            --blue-2: #1E40AF;
            --ink:    #0F172A;
            --slate:  #475569;
            --muted:  #64748B;
            --line:   #CBD5E1;
            --paper:  #F5F7FA;
            --danger: #B91C1C;
            --ease:   cubic-bezier(.2, .7, .2, 1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink); background: var(--paper);
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
        }

        .page { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: minmax(380px, 44%) 1fr; }

        /* ═════ School panel ═════ */
        .school {
            position: relative; overflow: hidden; isolation: isolate;
            background: var(--navy); color: #fff;
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 48px 56px;
        }
        /* Rings from the round school seal: drawn in once, then the gold dot circles very slowly */
        .rings { position: absolute; right: -460px; top: -200px; width: 760px; height: 760px; z-index: -1; pointer-events: none; }
        .rings-move { transition: transform .6s var(--ease); will-change: transform; }
        .ring { fill: none; stroke: #fff; stroke-opacity: .1; stroke-width: 1.5; stroke-dasharray: 1; stroke-dashoffset: 1; animation: draw 1.4s var(--ease) forwards; animation-delay: calc(var(--i) * 110ms); }
        .ring.gold { stroke: var(--gold); stroke-opacity: .6; stroke-width: 2; animation-duration: 1.8s; }
        .orbit { transform-origin: 380px 380px; animation: orbit 60s linear infinite, fade-in .6s ease 1.6s both; }
        .orbit circle { fill: var(--gold); }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        @keyframes orbit { to { transform: rotate(360deg); } }

        .school-top { display: flex; align-items: center; gap: 14px; animation: rise .6s var(--ease) both; }
        .seal { width: 64px; height: 64px; border-radius: 50%; background: #fff; padding: 3px; flex-shrink: 0; box-shadow: 0 0 0 6px rgba(255,255,255,.06); }
        .seal img { width: 100%; height: 100%; display: block; border-radius: 50%; }
        .school-tag { font-size: 14px; font-weight: 600; color: #C7D4EE; line-height: 1.35; }
        .school-tag b { display: block; color: #fff; font-size: 15px; font-weight: 800; }

        .school-rule { display: block; width: 48px; height: 3px; border-radius: 2px; background: var(--gold); margin-bottom: 22px; transform-origin: left; animation: grow .7s var(--ease) .25s both; }
        .school-name { font-size: 42px; font-weight: 800; line-height: 1.08; letter-spacing: -.025em; }
        /* Each line of the name rises out of its own mask */
        .line { display: block; overflow: hidden; padding-bottom: .06em; }
        .line > span { display: inline-block; animation: line-up .8s var(--ease) both; animation-delay: calc(.3s + var(--i) * 90ms); }
        .school-place { margin-top: 14px; font-size: 16px; color: #C7D4EE; animation: rise .6s var(--ease) .65s both; }
        .school-foot { font-size: 13.5px; color: #C7D4EE; line-height: 1.55; max-width: 36ch; animation: rise .6s var(--ease) .8s both; }
        .school-foot b { color: #fff; font-weight: 700; }

        /* ═════ Form side ═════ */
        .side { display: flex; align-items: center; justify-content: center; padding: 48px 24px; }
        .panel { width: 100%; max-width: 400px; }
        /* One short entrance, in reading order */
        .enter { animation: rise .55s var(--ease) both; animation-delay: calc(.15s + var(--i) * 60ms); }

        .panel h1 { font-size: 30px; font-weight: 800; letter-spacing: -.025em; line-height: 1.15; }
        .lead { margin-top: 8px; font-size: 15px; color: var(--slate); line-height: 1.55; }

        .notice { display: flex; gap: 10px; align-items: flex-start; margin-top: 24px; padding: 12px 14px; border-radius: 10px; font-size: 14px; line-height: 1.5; animation: drop .4s var(--ease) both; }
        .notice svg { flex-shrink: 0; margin-top: 1px; }
        .notice.error { background: #FEF2F2; border: 1px solid #FECACA; color: var(--danger); }
        .notice.ok { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; }

        form { margin-top: 28px; display: grid; gap: 20px; }
        form.shake { animation: shake .42s cubic-bezier(.36,.07,.19,.97) .15s both; }
        .field label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; transition: color .15s ease; }
        .field:focus-within label { color: var(--blue); }
        .control { position: relative; }
        .control > svg.lead-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; pointer-events: none; transition: color .15s ease; }
        .field:focus-within .lead-icon { color: var(--blue); }
        .input {
            width: 100%; height: 50px; padding: 0 14px 0 44px;
            border: 1px solid var(--line); border-radius: 12px; background: #fff;
            font: inherit; font-size: 15px; color: var(--ink);
            transition: border-color .15s ease, box-shadow .2s ease;
        }
        .input::placeholder { color: #94A3B8; }
        .input:hover { border-color: #94A3B8; }
        .input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 4px rgba(29,78,216,.14); }
        .input[aria-invalid="true"] { border-color: var(--danger); }
        .field-error { margin-top: 6px; font-size: 13px; color: var(--danger); }
        .pw .input { padding-right: 52px; }
        .pw-toggle {
            position: absolute; right: 5px; top: 5px; width: 40px; height: 40px; border-radius: 9px;
            border: 0; background: transparent; color: var(--muted); cursor: pointer; display: grid; place-items: center;
            transition: background-color .15s ease, color .15s ease;
        }
        .pw-toggle:hover { background: #F1F5F9; color: var(--ink); }
        /* Eye icons cross-fade instead of popping */
        .pw-toggle svg { grid-area: 1 / 1; transition: opacity .18s ease, transform .18s ease, filter .18s ease; }
        .pw-toggle .off { opacity: 0; transform: scale(.7); filter: blur(2px); }
        .pw-toggle[aria-pressed="true"] .on { opacity: 0; transform: scale(.7); filter: blur(2px); }
        .pw-toggle[aria-pressed="true"] .off { opacity: 1; transform: none; filter: none; }
        .caps { margin-top: 6px; font-size: 13px; font-weight: 600; color: #92400E; animation: drop .25s var(--ease) both; }

        .submit {
            position: relative; height: 50px; border: 0; border-radius: 12px; background: var(--blue); color: #fff;
            font: inherit; font-size: 15px; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 1px 2px rgba(15,23,42,.12), 0 8px 20px -10px rgba(29,78,216,.55);
            transition: background-color .15s ease, transform .12s ease, box-shadow .2s ease;
        }
        .submit:hover { background: var(--blue-2); }
        .submit:active { transform: scale(.985); }
        .submit:disabled { cursor: progress; background: var(--blue-2); }
        /* The padlock opens on hover and while signing in */
        .padlock .shackle { transform-origin: 16px 10px; transition: transform .28s var(--ease); }
        .submit:hover .shackle, .submit.loading .shackle { transform: translateY(-2px) rotate(-18deg); }
        .spinner { width: 18px; height: 18px; border-radius: 50%; border: 2px solid rgba(255,255,255,.35); border-top-color: #fff; display: none; animation: spin .7s linear infinite; }
        .submit.loading .spinner { display: block; }
        .submit.loading .padlock { display: none; }

        /* Lock: circular countdown, echoing the seal's rings */
        .lock { margin-top: 28px; padding: 24px; border: 1px solid #FDE68A; border-radius: 14px; background: #FFFBEB; display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: center; animation: drop .45s var(--ease) both; }
        .lock-dial { position: relative; width: 104px; height: 104px; }
        .lock-dial svg { width: 104px; height: 104px; transform: rotate(-90deg); }
        .lock-dial circle { fill: none; stroke-width: 7; }
        .lock-dial .track { stroke: #FDE68A; }
        .lock-dial .left { stroke: #D97706; stroke-linecap: round; stroke-dasharray: 1; transition: stroke-dashoffset 1s linear; }
        .lock-time { position: absolute; inset: 0; display: grid; place-items: center; font-size: 20px; font-weight: 800; color: #78350F; font-variant-numeric: tabular-nums; }
        .lock h2 { font-size: 17px; font-weight: 800; color: #78350F; }
        .lock p { margin-top: 6px; font-size: 14px; color: #92400E; line-height: 1.55; }

        .other { margin-top: 28px; padding-top: 20px; border-top: 1px solid #E2E8F0; font-size: 14px; color: var(--slate); line-height: 1.55; }
        .other a { color: var(--blue); font-weight: 700; text-decoration: none; background-image: linear-gradient(currentColor, currentColor); background-size: 0 1.5px; background-position: 0 100%; background-repeat: no-repeat; transition: background-size .25s var(--ease); }
        .other a:hover { background-size: 100% 1.5px; }
        .copy { margin-top: 16px; font-size: 13px; color: var(--muted); }

        :focus-visible { outline: 3px solid var(--blue); outline-offset: 2px; }
        .school :focus-visible { outline-color: #93C5FD; }

        @keyframes rise    { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
        @keyframes drop    { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        @keyframes line-up { from { transform: translateY(105%); } to { transform: none; } }
        @keyframes grow    { from { transform: scaleX(0); } to { transform: none; } }
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes spin    { to { transform: rotate(360deg); } }
        @keyframes shake   { 10%, 90% { transform: translateX(-1px); } 20%, 80% { transform: translateX(3px); } 30%, 50%, 70% { transform: translateX(-6px); } 40%, 60% { transform: translateX(6px); } }

        @media (max-width: 960px) {
            .page { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .school { padding: 24px; gap: 18px; }
            .rings { width: 420px; height: 420px; right: -170px; top: auto; bottom: -230px; }
            .seal { width: 52px; height: 52px; }
            .school-name { font-size: 24px; }
            .line { display: inline; }
            .line > span { display: inline; animation-name: rise; }
            .school-place { margin-top: 6px; font-size: 14px; }
            .school-rule, .school-foot { display: none; }
            .side { align-items: flex-start; padding: 32px 20px 40px; }
            .panel h1 { font-size: 26px; }
            .lock { grid-template-columns: 1fr; justify-items: start; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .001ms !important; animation-delay: 0s !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
            .ring { stroke-dashoffset: 0; }
        }
    </style>
</head>
<body>
<main class="page">

    <section class="school" aria-label="Sto. Domingo National High School">
        <svg class="rings" viewBox="0 0 760 760" aria-hidden="true">
            <g class="rings-move" id="rings">
                <circle class="ring" style="--i:0" cx="380" cy="380" r="370" pathLength="1"/>
                <circle class="ring" style="--i:1" cx="380" cy="380" r="310" pathLength="1"/>
                <circle class="ring" style="--i:2" cx="380" cy="380" r="190" pathLength="1"/>
                <circle class="ring" style="--i:3" cx="380" cy="380" r="130" pathLength="1"/>
                <circle class="ring gold" style="--i:4" cx="380" cy="380" r="250" pathLength="1"/>
                <g class="orbit"><circle cx="203" cy="557" r="5"/></g>
            </g>
        </svg>

        <div class="school-top">
            <span class="seal"><img src="{{ asset('images/logo.png') }}" alt="" width="58" height="58" decoding="async" fetchpriority="high"></span>
            <p class="school-tag"><b>DP-LMS</b>Administration</p>
        </div>

        <div>
            <span class="school-rule" aria-hidden="true"></span>
            <p class="school-name">
                <span class="line"><span style="--i:0">Sto. Domingo</span></span>
                <span class="line"><span style="--i:1">National</span></span>
                <span class="line"><span style="--i:2">High School</span></span>
            </p>
            <p class="school-place">San Manuel, Pangasinan</p>
        </div>

        <p class="school-foot"><b>For school administrators only.</b> Every sign-in and failed attempt on this page is recorded in the audit log.</p>
    </section>

    <section class="side">
        <div class="panel">
            <h1 class="enter" style="--i:1">Administrator sign in</h1>
            <p class="lead enter" style="--i:2">Use the email and password of your administrator account.</p>

            @if ($lockSeconds > 0)
                <div class="lock" role="alert" id="lock" data-seconds="{{ $lockSeconds }}">
                    <div class="lock-dial" aria-hidden="true">
                        <svg viewBox="0 0 104 104"><circle class="track" cx="52" cy="52" r="46"/><circle class="left" id="lockRing" cx="52" cy="52" r="46" pathLength="1"/></svg>
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
                    <div class="notice ok" role="status">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>You have signed out.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm" novalidate
                      class="{{ session('error') ? 'shake' : '' }}" @if (session('signed_out')) data-signed-out @endif>
                    @csrf
                    <div class="field enter" style="--i:3">
                        <label for="admin-email">Email</label>
                        <div class="control">
                            <svg class="lead-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 7l8 6 8-6"/></svg>
                            <input class="input" type="email" id="admin-email" name="email" value="{{ old('email') }}"
                                   autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required
                                   placeholder="name@school.edu.ph"
                                   @error('email') aria-invalid="true" aria-describedby="err-email" @enderror>
                        </div>
                        @error('email') <p class="field-error" id="err-email">{{ $message }}</p> @enderror
                    </div>

                    <div class="field enter" style="--i:4">
                        <label for="admin-pw">Password</label>
                        <div class="control pw">
                            <svg class="lead-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.03 5.91c-.56-.1-1.16.03-1.56.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.82c0-.6.24-1.17.66-1.59l6.5-6.5c.4-.4.53-1 .43-1.56A6 6 0 1121.75 8.25z"/></svg>
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

                    <button type="submit" class="submit enter" style="--i:5" id="adminSubmit">
                        <svg class="padlock" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path class="shackle" stroke-linecap="round" d="M8 11V8a4 4 0 018 0v3"/>
                        </svg>
                        <span class="spinner" aria-hidden="true"></span>
                        <span id="adminSubmitText">Sign in</span>
                    </button>
                </form>
            @endif

            <p class="other enter" style="--i:6">Students, teachers and parents sign in on the <a href="{{ route('login') }}">main sign-in page</a>.</p>
            <p class="copy enter" style="--i:7">&copy; {{ date('Y') }} Sto. Domingo National High School</p>
        </div>
    </section>

</main>

<script>
(function () {
    const form = document.getElementById('adminLoginForm');
    const pw = document.getElementById('admin-pw');
    const toggle = document.getElementById('pwToggle');
    const submit = document.getElementById('adminSubmit');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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

    // The seal rings follow the pointer a little (desktop only); transform only, one frame at a time
    const rings = document.getElementById('rings');
    const panel = document.querySelector('.school');
    if (rings && panel && finePointer && !reduceMotion) {
        let frame = 0, x = 0, y = 0;
        panel.addEventListener('pointermove', (e) => {
            const r = panel.getBoundingClientRect();
            x = ((e.clientX - r.left) / r.width - .5) * -24;
            y = ((e.clientY - r.top) / r.height - .5) * -24;
            if (!frame) frame = requestAnimationFrame(() => { rings.style.transform = `translate(${x}px, ${y}px)`; frame = 0; });
        });
        panel.addEventListener('pointerleave', () => { rings.style.transform = ''; });
    }

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
</body>
</html>
