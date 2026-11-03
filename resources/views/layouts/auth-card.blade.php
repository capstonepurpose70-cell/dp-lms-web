<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — DP-LMS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Shared by activation, sign-in code, password and parent pages: school photo on the left, form on the
         right, same green brand and fonts as the login page. Plain CSS — no new Tailwind classes. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #15803d; --accent-dk: #166534; --accent-lt: #f0fdf4;   /* green-700: white text 5:1 */
            --ok: #16a34a; --ok-dk: #15803d; --ok-lt: #f0fdf4;
            --danger: #dc2626;
            --text-1: #0f172a; --text-2: #475569; --text-3: #64748b;
            --surface: #fff; --field: #f8fafc; --border: #e2e8f0;
            --r-sm: 10px; --r-md: 12px; --r-lg: 18px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; font-family: 'DM Sans', system-ui, -apple-system, sans-serif; color: var(--text-1); background: var(--surface); -webkit-font-smoothing: antialiased; }

        /* Two panes: school photo | form */
        .ac-shell { display: grid; grid-template-columns: minmax(0, 1fr) minmax(480px, 600px); min-height: 100vh; }
        .ac-photo { position: sticky; top: 0; height: 100vh; overflow: hidden; color: #fff; display: flex; flex-direction: column; justify-content: space-between; padding: 36px 44px; }
        /* asset(): a root-relative /images/... breaks when served from an XAMPP subfolder */
        .ac-photo::before { content: ''; position: absolute; inset: 0; background: url('{{ asset("images/bg.jpg") }}') center/cover no-repeat; }
        .ac-photo::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(2,44,34,.6) 0%, rgba(2,44,34,.2) 42%, rgba(2,44,34,.9) 100%); }
        .ac-photo > * { position: relative; z-index: 1; }
        .ac-school { display: flex; align-items: center; gap: 14px; text-decoration: none; color: #fff; }
        .ac-school img { width: 56px; height: 56px; border-radius: 50%; background: #fff; padding: 2px; box-shadow: 0 4px 16px rgba(0,0,0,.25); }
        .ac-school b { display: block; font-size: 17px; font-weight: 700; letter-spacing: -.01em; }
        .ac-school span span { display: block; font-size: 13px; color: rgba(255,255,255,.82); }
        .ac-quote { max-width: 460px; }
        .ac-quote blockquote { font-family: 'Cormorant Garamond', serif; font-size: 27px; font-weight: 600; font-style: italic; line-height: 1.3; }
        .ac-quote cite { display: block; margin-top: 10px; font-style: normal; font-size: 13px; color: rgba(255,255,255,.8); }
        .ac-system { margin-top: 26px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,.25); font-size: 13.5px; color: rgba(255,255,255,.85); }
        .ac-system b { display: block; font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 700; color: #fff; line-height: 1; margin-bottom: 4px; }

        .ac-main { display: flex; flex-direction: column; min-height: 100vh; padding: 28px 48px 20px; }
        .ac-mobile-brand { display: none; }
        .ac-wrap { width: 100%; max-width: 440px; margin: auto; animation: acUp .35s cubic-bezier(.16,1,.3,1) both; }
        .ac-wrap.is-wide { max-width: 520px; }
        @keyframes acUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

        .ac-card { position: relative; }

        /* Step indicator */
        .ac-steps { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; list-style: none; }
        .ac-step { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--text-3); }
        .ac-step-dot { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 700; border: 2px solid var(--border); color: var(--text-3); background: #fff; }
        .ac-step.is-active { color: var(--text-1); }
        .ac-step.is-active .ac-step-dot { background: var(--accent); border-color: var(--accent); color: #fff; }
        .ac-step.is-done .ac-step-dot { background: var(--ok-lt); border-color: var(--ok); color: var(--ok-dk); }
        .ac-step-line { flex: 1; height: 2px; background: var(--border); border-radius: 2px; max-width: 56px; }

        .ac-heading { font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 700; line-height: 1.08; margin-bottom: 6px; }
        .ac-desc { font-size: 14.5px; color: var(--text-2); line-height: 1.55; margin-bottom: 20px; }

        .ac-alert { border-radius: var(--r-sm); padding: 11px 14px; font-size: 13.5px; margin-bottom: 16px; line-height: 1.55; }
        .ac-alert ul { padding-left: 18px; }
        .ac-alert-ok   { background: var(--ok-lt); border: 1px solid #86efac; color: var(--ok-dk); }
        .ac-alert-err  { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .ac-alert-info { background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; }   /* neutral — green is "success" */

        .ac-section { font-size: 13px; font-weight: 700; color: var(--text-2); margin: 4px 0 10px; display: flex; align-items: center; gap: 10px; }
        .ac-section::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .ac-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
        .ac-span { grid-column: 1 / -1; }

        .ac-group { margin-bottom: 14px; min-width: 0; }
        .ac-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-1); margin-bottom: 6px; }
        .ac-opt { font-weight: 500; color: var(--text-3); }
        .ac-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; line-height: 1.45; }

        .ac-input {
            width: 100%; min-height: 46px; border: 1.5px solid var(--border); border-radius: var(--r-sm);
            padding: 10px 13px; font-size: 15px; color: var(--text-1); background: var(--field);
            outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .ac-input:hover { border-color: #cbd5e1; }
        .ac-input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(22,163,74,.16); background: #fff; }
        .ac-input.is-error { border-color: #f87171; background: #fffafa; }
        .ac-input::placeholder { color: #8b97a8; }
        .ac-code { text-align: center; font-size: 26px; font-weight: 800; letter-spacing: .35em; text-transform: uppercase; font-family: ui-monospace, Consolas, monospace; }

        /* Password with show/hide */
        .ac-pw { position: relative; }
        .ac-pw .ac-input { padding-right: 70px; }
        .ac-pw-toggle {
            position: absolute; right: 4px; top: 2px; bottom: 2px;
            min-width: 56px; padding: 0 10px; border: none; border-radius: 8px; background: transparent;
            color: var(--accent); font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit;
        }
        .ac-pw-toggle:hover { background: var(--accent-lt); }

        /* Live password checklist */
        .ac-rules { list-style: none; display: grid; grid-template-columns: repeat(3, auto); justify-content: start; gap: 4px 16px; margin: -4px 0 16px; }
        .ac-rules li { font-size: 12px; color: var(--text-3); display: flex; align-items: center; gap: 6px; }
        .ac-rules li::before { content: '○'; font-size: 11px; }
        .ac-rules li.is-met { color: var(--ok-dk); }
        .ac-rules li.is-met::before { content: '✓'; font-weight: 700; }

        /* Code expiry countdown (<span data-countdown="seconds">) */
        .ac-timer { text-align: center; font-size: 13px; color: var(--text-2); margin: -4px 0 16px; }
        .ac-timer [data-countdown] { font-weight: 700; color: var(--accent); font-variant-numeric: tabular-nums; }
        .ac-timer .is-expired { color: var(--danger); }

        .ac-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--text-2); margin: 2px 0 16px; cursor: pointer; line-height: 1.5; }
        .ac-check input { width: 18px; height: 18px; margin-top: 1px; accent-color: var(--accent); flex-shrink: 0; }
        .ac-check a { color: var(--accent); font-weight: 600; text-decoration: underline; text-underline-offset: 2px; }

        .ac-btn {
            width: 100%; min-height: 48px; border: none; border-radius: var(--r-md); cursor: pointer;
            background: var(--accent); color: #fff; font-size: 15px; font-weight: 700; font-family: inherit;
            transition: background-color .15s, transform .1s;
        }
        .ac-btn:hover { background: var(--accent-dk); }
        .ac-btn:active { transform: scale(.99); }
        .ac-btn[disabled] { opacity: .7; cursor: progress; }
        .ac-btn-link { background: none; border: none; color: var(--accent); font-weight: 700; font-size: 13.5px; cursor: pointer; padding: 6px 4px; min-height: 44px; font-family: inherit; }
        .ac-btn-link:hover { text-decoration: underline; }

        .ac-footer { margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 13.5px; color: var(--text-2); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px 16px; }
        .ac-footer a { color: var(--accent); font-weight: 700; text-decoration: none; }
        .ac-footer a:hover { text-decoration: underline; }

        .ac-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .ac-legal { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; width: 100%; max-width: 520px; margin: 18px auto 0; font-size: 12.5px; color: var(--text-3); }
        .ac-legal a { font-weight: 600; color: var(--text-2); text-decoration: none; }
        .ac-legal-links { display: inline-flex; gap: 16px; }
        .ac-legal a:hover { color: var(--accent); text-decoration: underline; }

        :focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        .ac-input:focus-visible { outline: none; }

        @media (max-width: 1100px) {
            .ac-shell { grid-template-columns: minmax(0, 1fr) minmax(440px, 520px); }
            .ac-main { padding: 24px 32px 18px; }
            .ac-quote blockquote { font-size: 23px; }
        }
        /* Tablets and phones: no photo pane, a slim school header instead */
        @media (max-width: 860px) {
            .ac-shell { grid-template-columns: 1fr; }
            .ac-photo { display: none; }
            .ac-main { padding: 18px 20px 16px; }
            .ac-mobile-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text-1); margin-bottom: 22px; }
            .ac-mobile-brand img { width: 40px; height: 40px; border-radius: 50%; }
            .ac-mobile-brand b { display: block; font-size: 15px; }
            .ac-mobile-brand span span { display: block; font-size: 12px; color: var(--text-3); }
            .ac-wrap { margin: 0 auto; }
        }
        @media (max-width: 560px) {
            .ac-grid { grid-template-columns: 1fr; }
            .ac-rules { grid-template-columns: 1fr 1fr; }
            .ac-heading { font-size: 29px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ac-wrap { animation: none; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>
<div class="ac-shell">
    <aside class="ac-photo" aria-hidden="true">
        <a class="ac-school" href="{{ route('login') }}" tabindex="-1">
            <img src="{{ asset('images/logo.png') }}" alt="">
            <span><b>Sto. Domingo National High School</b><span>San Manuel, Pangasinan</span></span>
        </a>
        <div class="ac-quote">
            <blockquote>“The fear of the LORD is the beginning of knowledge.”</blockquote>
            <cite>Proverbs 1:7</cite>
            <p class="ac-system"><b>DP-LMS</b>Digital Platform Learning Management System</p>
        </div>
    </aside>

    <div class="ac-main">
        <a class="ac-mobile-brand" href="{{ route('login') }}">
            <img src="{{ asset('images/logo.png') }}" alt="">
            <span><b>Sto. Domingo NHS</b><span>DP-LMS Portal</span></span>
        </a>

        <main class="ac-wrap @yield('wrap-class')">
            <div class="ac-card">
                @hasSection('steps')
                    <ol class="ac-steps" aria-label="Progress">@yield('steps')</ol>
                @endif

                <h1 class="ac-heading">@yield('heading')</h1>
                <p class="ac-desc">@yield('desc')</p>

                @if(session('success'))
                    <div class="ac-alert ac-alert-ok" role="status">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="ac-alert ac-alert-err" role="alert">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="ac-alert ac-alert-err" role="alert">
                        @if($errors->count() === 1)
                            {{ $errors->first() }}
                        @else
                            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        @endif
                    </div>
                @endif

                @yield('content')

                @hasSection('footer')
                    <footer class="ac-footer">@yield('footer')</footer>
                @endif
            </div>
        </main>

        <p class="ac-legal">
            <span>&copy; {{ date('Y') }} Sto. Domingo National High School</span>
            <span class="ac-legal-links"><a href="{{ route('terms') }}">Terms</a><a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('about') }}">About</a></span>
        </p>
    </div>
</div>

@stack('scripts')

<script>
    // Show / hide password
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });

    // Live password checklist: <ul data-pw-rules="passwordId" data-pw-confirm="confirmId">
    document.querySelectorAll('[data-pw-rules]').forEach(function (list) {
        var pw = document.getElementById(list.getAttribute('data-pw-rules'));
        var confirm = document.getElementById(list.getAttribute('data-pw-confirm'));
        var tests = {
            length: function (v) { return v.length >= 8; },
            mixed:  function (v) { return /[a-z]/.test(v) && /[A-Z]/.test(v); },
            number: function (v) { return /\d/.test(v); },
            symbol: function (v) { return /[^A-Za-z0-9]/.test(v); },
            match:  function (v) { return confirm && v !== '' && v === confirm.value; }
        };
        function update() {
            list.querySelectorAll('[data-rule]').forEach(function (li) {
                li.classList.toggle('is-met', tests[li.getAttribute('data-rule')](pw.value));
            });
        }
        pw.addEventListener('input', update);
        if (confirm) confirm.addEventListener('input', update);
    });

    // Digits only (LRN, email code): strips spaces/dashes, also on paste — "1234 5678 9012" would
    // otherwise be cut by maxlength before the spaces are removed.
    document.querySelectorAll('input[data-digits]').forEach(function (input) {
        var max = parseInt(input.getAttribute('maxlength'), 10) || 64;
        input.addEventListener('paste', function (e) {
            var text = (e.clipboardData || window.clipboardData).getData('text');
            if (!text) return;
            e.preventDefault();
            var start = input.selectionStart, end = input.selectionEnd;
            input.value = (input.value.slice(0, start) + text + input.value.slice(end)).replace(/\D/g, '').slice(0, max);
        });
        input.addEventListener('input', function () {
            var clean = input.value.replace(/\D/g, '');
            if (clean !== input.value) input.value = clean;
        });
    });

    // Countdown to a code's expiry. Clock-based, so a throttled background tab stays accurate.
    document.querySelectorAll('[data-countdown]').forEach(function (el) {
        var end = Date.now() + (parseInt(el.getAttribute('data-countdown'), 10) || 0) * 1000;
        (function tick() {
            var s = Math.max(0, Math.round((end - Date.now()) / 1000));
            el.textContent = s > 0 ? Math.floor(s / 60) + ':' + ('0' + s % 60).slice(-2) : 'expired';
            el.classList.toggle('is-expired', s === 0);
            if (s > 0) setTimeout(tick, 1000);
        })();
    });

    // Prevent double submit
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        var btn = form.querySelector('button[type="submit"]');
        if (!btn) return;
        var label = btn.textContent;
        form.addEventListener('submit', function () {
            btn.disabled = true; btn.textContent = btn.getAttribute('data-busy') || 'Please wait…';
        });
        // Back button restores the page from cache with the button still "busy"
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { btn.disabled = false; btn.textContent = label; }
        });
    });

    // Back/Forward can bring these pages back with typed passwords still in them (shown as
    // text if "Show" was used) — hide and clear them.
    window.addEventListener('pageshow', function (e) {
        var nav = performance.getEntriesByType('navigation')[0];
        if (!e.persisted && !(nav && nav.type === 'back_forward')) return;
        document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
            var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
            if (input && input.type !== 'password') btn.click();
        });
        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            input.value = '';
            input.dispatchEvent(new Event('input'));   // refreshes the password checklist
        });
    });
</script>
</body>
</html>
