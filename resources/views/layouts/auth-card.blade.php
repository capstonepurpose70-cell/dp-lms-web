<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Shared look for activation / parent pages — same green brand, fonts and navbar as the
         login page. Plain CSS + tokens — no new Tailwind classes (the CSS build is stale). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #15803d; --accent-dk: #166534; --accent-lt: #f0fdf4;   /* green-600: white text 5:1 */
            --ok: #16a34a; --ok-dk: #15803d; --ok-lt: #f0fdf4;
            --danger: #dc2626;
            --text-1: #0f172a; --text-2: #475569; --text-3: #64748b; /* #94a3b8 was 2.6:1 on white — hints/rules unreadable */
            --surface: #fff; --field: #f8fafc; --border: #e2e8f0;
            --r-sm: 10px; --r-md: 12px; --r-lg: 22px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            display: flex; align-items: flex-start; justify-content: center;
            padding: 96px 16px 40px;   /* top: clears the fixed navbar */
            background: #1e293b;
            position: relative; color: var(--text-1);
        }
        /* Fixed photo layer — background-attachment: fixed is ignored (zoomed) on iOS.
           asset(): a root-relative /images/... breaks when served from an XAMPP subfolder. */
        body::before { content: ''; position: fixed; inset: 0; background: url('{{ asset("images/bg.jpg") }}') center/cover no-repeat; z-index: 0; }
        body::after { content: ''; position: fixed; inset: 0; background: linear-gradient(200deg, rgba(0,0,0,.35) 0%, rgba(0,0,0,.5) 50%, rgba(6,78,59,.85) 100%); z-index: 0; }

        .ac-wrap { position: relative; z-index: 1; width: 100%; max-width: 440px; animation: acUp .4s cubic-bezier(.16,1,.3,1) both; }
        .ac-wrap.is-wide { max-width: 600px; }
        @keyframes acUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        .ac-card {
            position: relative; background: var(--surface); border-radius: var(--r-lg); overflow: hidden;
            box-shadow: 0 24px 60px rgba(2,6,23,.35), 0 0 0 1px rgba(255,255,255,.06);
        }
        /* Brand accent bar — same as the login card */
        .ac-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--ok), var(--accent-dk)); }

        .ac-body { padding: 30px 28px 22px; }
        .ac-eyebrow { font-size: 11px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; color: var(--accent); margin-bottom: 8px; }

        /* Step indicator */
        .ac-steps { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; list-style: none; }
        .ac-step { display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; color: var(--text-3); }
        .ac-step-dot {
            width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center;
            font-size: 12px; font-weight: 700; border: 2px solid var(--border); color: var(--text-3); background: #fff;
        }
        .ac-step.is-active { color: var(--text-1); }
        .ac-step.is-active .ac-step-dot { background: var(--accent); border-color: var(--accent); color: #fff; }
        .ac-step.is-done .ac-step-dot { background: var(--ok-lt); border-color: var(--ok); color: var(--ok-dk); }
        .ac-step-line { flex: 1; height: 2px; background: var(--border); border-radius: 2px; max-width: 60px; }

        .ac-heading { font-family: 'Cormorant Garamond', serif; font-size: 31px; font-weight: 700; line-height: 1.1; margin-bottom: 6px; }
        .ac-desc { font-size: 14px; color: var(--text-2); line-height: 1.6; margin-bottom: 20px; }

        .ac-alert { border-radius: var(--r-sm); padding: 11px 14px; font-size: 13.5px; margin-bottom: 16px; line-height: 1.55; }
        .ac-alert ul { padding-left: 18px; }
        .ac-alert-ok   { background: var(--ok-lt); border: 1px solid #86efac; color: var(--ok-dk); }
        .ac-alert-err  { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .ac-alert-info { background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; }   /* neutral — green is "success" */

        .ac-section {
            font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
            color: var(--text-3); margin: 6px 0 10px; display: flex; align-items: center; gap: 10px;
        }
        .ac-section::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .ac-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
        .ac-span { grid-column: 1 / -1; }

        .ac-group { margin-bottom: 14px; min-width: 0; }
        .ac-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-1); margin-bottom: 6px; }
        .ac-opt { font-weight: 500; color: var(--text-3); }
        .ac-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; line-height: 1.45; }

        .ac-input {
            width: 100%; min-height: 48px; border: 1.5px solid var(--border); border-radius: var(--r-sm);
            padding: 11px 13px; font-size: 15px; color: var(--text-1); background: var(--field);
            outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .ac-input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(22,163,74,.18); background: #fff; }
        .ac-input.is-error { border-color: #f87171; background: #fffafa; }
        .ac-input::placeholder { color: #8b97a8; }
        .ac-code { text-align: center; font-size: 26px; font-weight: 800; letter-spacing: .35em; text-transform: uppercase; font-family: ui-monospace, Consolas, monospace; }

        /* Password with show/hide */
        .ac-pw { position: relative; }
        .ac-pw .ac-input { padding-right: 70px; }
        .ac-pw-toggle {
            position: absolute; right: 4px; top: 2px; bottom: 2px; /* 44px tall inside the 48px input */
            min-width: 56px; padding: 0 10px; border: none; border-radius: 8px; background: transparent;
            color: var(--accent); font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit;
        }
        .ac-pw-toggle:hover { background: var(--accent-lt); }

        /* Live password checklist */
        .ac-rules { list-style: none; display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px; margin: -4px 0 14px; }
        .ac-rules li { font-size: 12px; color: var(--text-3); display: flex; align-items: center; gap: 6px; }
        .ac-rules li::before { content: '○'; font-size: 11px; }
        .ac-rules li.is-met { color: var(--ok-dk); }
        .ac-rules li.is-met::before { content: '✓'; font-weight: 700; }

        .ac-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--text-2); margin: 2px 0 16px; cursor: pointer; line-height: 1.5; }
        .ac-check input { width: 18px; height: 18px; margin-top: 1px; accent-color: var(--accent); flex-shrink: 0; }

        .ac-btn {
            width: 100%; min-height: 48px; border: none; border-radius: var(--r-md); cursor: pointer;
            background: var(--accent); color: #fff; font-size: 15px; font-weight: 700; font-family: inherit;
            box-shadow: 0 8px 20px rgba(21,128,61,.25); transition: background .15s, transform .1s;
        }
        .ac-btn:hover { background: var(--accent-dk); }
        .ac-btn:active { transform: scale(.99); }
        .ac-btn[disabled] { opacity: .7; cursor: progress; }
        .ac-btn-link { background: none; border: none; color: var(--accent); font-weight: 700; font-size: 13.5px; cursor: pointer; padding: 6px 4px; min-height: 44px; font-family: inherit; }
        .ac-btn-link:hover { text-decoration: underline; }

        .ac-footer {
            padding: 14px 28px 18px; border-top: 1px solid var(--border); background: #f8fbf9;
            font-size: 13.5px; color: var(--text-2); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px 16px;
        }
        .ac-footer a { color: var(--accent); font-weight: 700; text-decoration: none; }
        .ac-footer a:hover { text-decoration: underline; }

        .ac-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .ac-copy { text-align: center; font-size: 11.5px; color: rgba(255,255,255,.75); margin-top: 16px; }

        :focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        .ac-input:focus-visible { outline: none; }

        @media (max-width: 560px) {
            body { padding: 80px 12px 16px; }
            .ac-grid, .ac-rules { grid-template-columns: 1fr; }
            .ac-body, .ac-footer { padding-left: 18px; padding-right: 18px; }
            .ac-heading { font-size: 26px; }
            .ac-eyebrow { font-size: 10px; letter-spacing: 2px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ac-wrap { animation: none; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>
@include('partials.auth-navbar')

<main class="ac-wrap @yield('wrap-class')">
    <div class="ac-card">
        <div class="ac-body">
            @hasSection('steps')
                <ol class="ac-steps" aria-label="Progress">@yield('steps')</ol>
            @endif

            <p class="ac-eyebrow">Sto. Domingo NHS</p>
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
        </div>

        @hasSection('footer')
            <footer class="ac-footer">@yield('footer')</footer>
        @endif
    </div>

    <p class="ac-copy">&copy; {{ date('Y') }} Sto. Domingo National High School</p>
</main>

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
</script>
</body>
</html>
