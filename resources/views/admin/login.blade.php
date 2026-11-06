<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Sign in — Sto. Domingo NHS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="{{ asset('images/admin-login.jpg') }}">
    <style>
        :root {
            --ink:    #131F5C;
            --slate:  #5C6784;
            --muted:  #6E7893;
            --line:   #DDE2EF;
            --tile:   #E9EEFB;
            --wash:   #F3F6FD;
            --blue:   #2F47A8;
            --blue-2: #23378F;
            --sky:    #93A6FF;
            --danger: #B3261E;
            --ease:   cubic-bezier(.2, .7, .2, 1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Source Sans 3', system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink); background: var(--wash);
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
        }
        .sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }

        .page { position: relative; overflow: hidden; isolation: isolate; min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: 52% 48%; }
        /* Soft circles behind the card */
        .page::before, .page::after { content: ''; position: absolute; z-index: -2; border-radius: 50%; background: rgba(120,142,240,.14); }
        .page::before { top: -160px; right: -120px; width: 460px; height: 460px; box-shadow: 70px -60px 0 0 rgba(120,142,240,.2); }
        .page::after  { bottom: -300px; left: 42%; width: 560px; height: 440px; box-shadow: -30px 70px 0 0 rgba(120,142,240,.16); }

        /* Left: the school, cut on a slant */
        .photo {
            position: absolute; inset: 0 auto 0 0; width: 55.5%; z-index: -1; overflow: hidden; color: #fff;
            clip-path: polygon(0 0, 100% 0, 84% 100%, 0 100%);
            display: flex; flex-direction: column; justify-content: center;
            padding: 0 12% 4% 5.8%;
            animation: fade .5s ease both;
        }
        .photo > img { position: absolute; inset: 0; width: 100%; height: 100%; z-index: -2; object-fit: cover; object-position: 50% 42%; }
        .photo::after { content: ''; position: absolute; inset: 0; z-index: -1; background: linear-gradient(100deg, rgba(20,32,104,.82) 0%, rgba(26,42,128,.6) 48%, rgba(38,58,160,.34) 100%); }
        .photo-rule { display: block; width: 96px; height: 6px; border-radius: 3px; background: #5D74E8; margin-bottom: 26px; }
        .photo-name { font-size: clamp(38px, 4.1vw, 66px); font-weight: 600; line-height: 1.12; letter-spacing: -.01em; }
        .photo-name span { display: block; }
        .photo-name em { font-style: normal; color: var(--sky); }
        .photo-name span:last-child { color: #E4E8FF; }
        .photo-place { margin-top: 12px; font-size: clamp(18px, 1.75vw, 28px); color: #D9DEF5; }
        .motto {
            display: inline-flex; align-items: center; gap: 16px; align-self: flex-start;
            margin-top: 32px; padding: 16px 28px 16px 24px; border-radius: 16px;
            background: rgba(255,255,255,.12); font-size: 18px; line-height: 1.3;
        }
        .motto svg { flex-shrink: 0; }
        .motto p { padding-left: 16px; border-left: 1px solid rgba(255,255,255,.35); }

        /* Right: the card */
        .side { grid-column: 2; display: flex; align-items: center; justify-content: center; padding: 40px 32px; }
        .panel {
            width: 100%; max-width: 480px; padding: 32px 36px 28px;
            background: rgba(255,255,255,.94); border-radius: 26px;
            box-shadow: 0 18px 50px rgba(44,66,160,.12);
            animation: rise .45s var(--ease) both;
        }
        .school { text-align: center; }
        .seal { width: 108px; height: 108px; border-radius: 50%; display: block; margin: 0 auto 12px; }
        .school b { display: block; font-size: 22px; font-weight: 700; line-height: 1.25; }
        .school span { display: block; margin-top: 2px; font-size: 17px; color: var(--slate); }
        .panel h1 { display: flex; align-items: center; gap: 18px; margin-top: 20px; font-size: 23px; font-weight: 700; white-space: nowrap; }
        .panel h1::before, .panel h1::after { content: ''; flex: 1; height: 2px; background: var(--line); }

        .notice { display: flex; gap: 10px; align-items: flex-start; margin-top: 16px; padding: 11px 13px; border-radius: 10px; font-size: 15px; line-height: 1.4; animation: fade .2s ease both; }
        .notice svg { flex-shrink: 0; margin-top: 1px; }
        .notice.error { background: #FDF4F3; border: 1px solid #F3D3CF; color: var(--danger); }

        form { margin-top: 20px; display: grid; gap: 18px; }
        form.shake { animation: nudge .3s ease both; }
        .control {
            position: relative; display: flex; align-items: center; height: 58px; padding: 0 7px; overflow: hidden;
            border: 1.5px solid var(--line); border-radius: 14px; background: #fff;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .control:hover { border-color: #C4CDE2; }
        .control:focus-within { border-color: var(--blue); box-shadow: 0 0 0 4px rgba(47,71,168,.13); }
        .tile { width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center; background: var(--tile); color: #27366F; }
        .input { flex: 1; min-width: 0; height: 100%; padding: 0 12px 0 14px; border: 0; outline: 0; background: transparent; font: inherit; font-size: 18px; color: var(--ink); }
        .input::placeholder { color: #7A849E; opacity: 1; }
        /* A saved login keeps the page's own colors instead of the browser's blue */
        .input:-webkit-autofill, .input:-webkit-autofill:hover, .input:-webkit-autofill:focus { box-shadow: 0 0 0 40px #fff inset; -webkit-text-fill-color: var(--ink); }
        .field-error { margin-top: 6px; font-size: 14px; color: var(--danger); }
        .pw-toggle {
            width: 42px; height: 42px; flex-shrink: 0; border-radius: 10px;
            border: 0; background: transparent; color: var(--muted); cursor: pointer; display: grid; place-items: center;
            transition: background-color .18s ease, color .18s ease;
        }
        .pw-toggle:hover { background: var(--wash); color: var(--ink); }
        .pw-toggle svg { grid-area: 1 / 1; transition: opacity .18s ease; }
        .pw-toggle .off, .pw-toggle[aria-pressed="true"] .on { opacity: 0; }
        .pw-toggle[aria-pressed="true"] .off { opacity: 1; }
        .caps { margin-top: 6px; font-size: 14px; color: #8A5A00; }

        .submit {
            position: relative; overflow: hidden; isolation: isolate;
            height: 60px; border: 0; border-radius: 14px; color: #fff; cursor: pointer;
            background: linear-gradient(180deg, #3A55BE 0%, var(--blue-2) 100%);
            font: inherit; font-size: 20px; font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            transition: transform .12s ease, box-shadow .18s ease;
            box-shadow: 0 6px 16px rgba(35,55,143,.22);
        }
        /* A gradient cannot fade, so a dark layer fades over it */
        .submit::after { content: ''; position: absolute; inset: 0; z-index: -1; background: #1C2C78; opacity: 0; transition: opacity .18s ease; }
        .submit:hover::after, .submit:disabled::after { opacity: .55; }
        .submit:hover { box-shadow: 0 8px 20px rgba(35,55,143,.3); }
        .submit:active { transform: scale(.985); }
        .submit:disabled { cursor: progress; transform: none; }
        .go { width: 22px; margin-right: 12px; flex-shrink: 0; transition: width .2s ease, margin-right .2s ease, opacity .2s ease; }
        .submit.loading .go { width: 0; margin-right: 0; opacity: 0; }
        /* The spinner opens beside the words, so the label slides over instead of jumping */
        .spinner {
            width: 0; height: 18px; margin-right: 0; opacity: 0; border-radius: 50%;
            border: 0 solid rgba(255,255,255,.35); border-top-color: #fff;
            animation: spin .7s linear infinite;
            transition: width .2s ease, margin-right .2s ease, opacity .2s ease;
        }
        .submit.loading .spinner { width: 18px; margin-right: 10px; opacity: 1; border-width: 2px; }

        .lock { margin-top: 18px; padding: 14px 16px; border: 1px solid #EADFC2; border-radius: 12px; background: #FDFAF2; animation: fade .2s ease both; }
        .lock h2 { font-size: 17px; font-weight: 700; color: #6B4A00; }
        .lock p { margin-top: 4px; font-size: 16px; color: #7A5A12; line-height: 1.4; }
        .lock-time { font-weight: 700; font-variant-numeric: tabular-nums; }

        .copy { margin-top: 20px; text-align: center; font-size: 15px; color: var(--muted); }

        :focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }
        .input:focus-visible { outline: none; }

        @keyframes fade  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes rise  { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes nudge { 25% { transform: translateX(-3px); } 75% { transform: translateX(3px); } }
        @keyframes spin  { to { transform: rotate(360deg); } }

        @media (max-width: 960px) {
            .page { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .photo {
                position: relative; inset: auto; width: 100%; z-index: 0; padding: 32px 24px 56px;
                clip-path: polygon(0 0, 100% 0, 100% 84%, 0 100%);
            }
            .photo-rule { width: 56px; height: 4px; margin-bottom: 14px; }
            .photo-name { font-size: 30px; }
            .photo-place { margin-top: 6px; font-size: 16px; }
            .motto { display: none; }
            .side { grid-column: 1; align-items: flex-start; padding: 8px 16px 32px; }
            .panel { padding: 24px 20px 20px; border-radius: 20px; }
            .seal { width: 84px; height: 84px; }
            .school b { font-size: 19px; }
            .school span { font-size: 15.5px; }
        }
        /* Keeps the soft fades and color changes; only movement stops */
        @media (prefers-reduced-motion: reduce) {
            .panel { animation-name: fade; }
            form.shake { animation: none; }
            .submit:active { transform: none; }
        }
    </style>
</head>
<body>
<main class="page">

    <section class="photo" aria-label="Sto. Domingo National High School">
        <img src="{{ asset('images/admin-login.jpg') }}" alt="" width="800" height="1120" decoding="async" fetchpriority="high">
        <span class="photo-rule" aria-hidden="true"></span>
        <p class="photo-name"><span>Sto. Domingo</span><span>National <em>High School</em></span></p>
        <p class="photo-place">San Manuel, Pangasinan</p>
        <div class="motto">
            <svg width="44" height="50" viewBox="0 0 44 50" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 2.5 41 9v15c0 12-8 19.500-19 23.500C11 43.500 3 36 3 24V9L22 2.500Z"/>
                <path fill="currentColor" stroke="none" d="m22 15 15 6.500L22 28 7 21.500 22 15Z"/>
                <path d="M13 25.500V32c0 2 4 4.500 9 4.500s9-2.500 9-4.500v-6.500"/>
            </svg>
            <p>Academic Excellence<br>for a Brighter Future</p>
        </div>
    </section>

    <section class="side">
        <div class="panel">
            <div class="school">
                <img class="seal" src="{{ asset('images/logo.png') }}" alt="" width="52" height="52" decoding="async">
                <p><b>Sto. Domingo National High School</b><span>San Manuel, Pangasinan</span></p>
            </div>
            <h1>Admin Login</h1>

            @if ($lockSeconds > 0)
                <div class="lock" role="alert" id="lock" data-seconds="{{ $lockSeconds }}">
                    <h2>Sign-in is paused</h2>
                    <p>Too many tries. Try again in <span class="lock-time" id="lockTime">{{ sprintf('%02d:%02d', intdiv($lockSeconds, 60), $lockSeconds % 60) }}</span>.</p>
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
                        <label class="sr" for="admin-email">Email</label>
                        <div class="control">
                            <span class="tile" aria-hidden="true"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2.500"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.500 7.500 8.500 6 8.500-6"/></svg></span>
                            <input class="input" type="email" id="admin-email" name="email" placeholder="Email" value="{{ old('email') }}"
                                   autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required
                                   @error('email') aria-invalid="true" aria-describedby="err-email" @enderror>
                        </div>
                        @error('email') <p class="field-error" id="err-email">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label class="sr" for="admin-pw">Password</label>
                        <div class="control pw">
                            <span class="tile" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm-3 8V7a3 3 0 0 1 6 0v3H9Zm3 4a1.800 1.800 0 0 1 1 3.300V19h-2v-1.700a1.800 1.800 0 0 1 1-3.300Z"/></svg></span>
                            <input class="input" type="password" id="admin-pw" name="password" placeholder="Password"
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
                        <svg class="go" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/><path d="M3 12h12m-4-4 4 4-4 4"/></svg>
                        <span class="spinner" aria-hidden="true"></span>
                        <span id="adminSubmitText">Sign in</span>
                    </button>
                </form>
            @endif
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
        const tick = () => {
            const left = Math.max(0, Math.round((end - Date.now()) / 1000));
            time.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
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
