<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Shared look for activation / parent pages. Plain CSS + tokens —
         no new Tailwind classes (the CSS build is stale). --}}
    <style>
        :root {
            --accent: #2563EB; --accent-dk: #1d4ed8; --accent-lt: #eff6ff;
            --ok: #16a34a; --ok-dk: #15803d; --ok-lt: #f0fdf4;
            --danger: #dc2626;
            --text-1: #0f172a; --text-2: #475569; --text-3: #94a3b8;
            --surface: #fff; --field: #f8fafc; --border: #e2e8f0;
            --r-sm: 10px; --r-md: 12px; --r-lg: 22px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex; align-items: flex-start; justify-content: center;
            padding: 40px 16px;
            background: #1e293b url('/images/bg.jpg') center/cover no-repeat fixed;
            position: relative; color: var(--text-1);
        }
        body::after { content: ''; position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 0; }

        .ac-wrap { position: relative; z-index: 1; width: 100%; max-width: 440px; animation: acUp .4s cubic-bezier(.16,1,.3,1) both; }
        .ac-wrap.is-wide { max-width: 600px; }
        @keyframes acUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        .ac-card {
            background: var(--surface); border-radius: var(--r-lg); overflow: hidden;
            box-shadow: 0 24px 60px rgba(2,6,23,.35), 0 0 0 1px rgba(255,255,255,.06);
        }

        /* Compact brand header */
        .ac-brand {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 28px; border-bottom: 1px solid var(--border); background: #fbfdff;
        }
        .ac-brand img { width: 44px; height: 44px; object-fit: contain; border-radius: 10px; background: #fff; padding: 3px; box-shadow: 0 0 0 1px var(--border); }
        .ac-brand-name { font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: var(--text-1); line-height: 1.3; }
        .ac-brand-school { font-size: 12.5px; color: var(--text-2); }

        .ac-body { padding: 26px 28px 22px; }

        /* Step indicator */
        .ac-steps { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; list-style: none; }
        .ac-step { display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; color: var(--text-3); }
        .ac-step-dot {
            width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center;
            font-size: 12px; font-weight: 700; border: 2px solid var(--border); color: var(--text-3); background: #fff;
        }
        .ac-step.is-active { color: var(--text-1); }
        .ac-step.is-active .ac-step-dot { background: var(--accent); border-color: var(--accent); color: #fff; }
        .ac-step.is-done .ac-step-dot { background: var(--ok); border-color: var(--ok); color: #fff; }
        .ac-step-line { flex: 1; height: 2px; background: var(--border); border-radius: 2px; max-width: 60px; }

        .ac-heading { font-size: 22px; font-weight: 800; letter-spacing: -.01em; margin-bottom: 6px; }
        .ac-desc { font-size: 14px; color: var(--text-2); line-height: 1.6; margin-bottom: 20px; }

        .ac-alert { border-radius: var(--r-sm); padding: 11px 14px; font-size: 13.5px; margin-bottom: 16px; line-height: 1.55; }
        .ac-alert ul { padding-left: 18px; }
        .ac-alert-ok   { background: var(--ok-lt); border: 1px solid #86efac; color: var(--ok-dk); }
        .ac-alert-err  { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .ac-alert-info { background: var(--accent-lt); border: 1px solid #bfdbfe; color: #1e40af; }

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
            width: 100%; min-height: 46px; border: 1.5px solid var(--border); border-radius: var(--r-sm);
            padding: 11px 13px; font-size: 15px; color: var(--text-1); background: var(--field);
            outline: none; font-family: inherit; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .ac-input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(37,99,235,.12); background: #fff; }
        .ac-input.is-error { border-color: #f87171; background: #fffafa; }
        .ac-input::placeholder { color: #b6c0cf; }
        .ac-code { text-align: center; font-size: 26px; font-weight: 800; letter-spacing: .35em; text-transform: uppercase; font-family: ui-monospace, Consolas, monospace; }

        /* Password with show/hide */
        .ac-pw { position: relative; }
        .ac-pw .ac-input { padding-right: 70px; }
        .ac-pw-toggle {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            min-height: 34px; padding: 0 10px; border: none; border-radius: 8px; background: transparent;
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
            box-shadow: 0 8px 20px rgba(37,99,235,.25); transition: background .15s, transform .1s;
        }
        .ac-btn:hover { background: var(--accent-dk); }
        .ac-btn:active { transform: scale(.99); }
        .ac-btn[disabled] { opacity: .7; cursor: progress; }
        .ac-btn-link { background: none; border: none; color: var(--accent); font-weight: 700; font-size: 13.5px; cursor: pointer; padding: 6px 4px; min-height: 44px; font-family: inherit; }
        .ac-btn-link:hover { text-decoration: underline; }

        .ac-footer {
            padding: 14px 28px 18px; border-top: 1px solid var(--border); background: #fbfdff;
            font-size: 13.5px; color: var(--text-2); display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px 16px;
        }
        .ac-footer a { color: var(--accent); font-weight: 700; text-decoration: none; }
        .ac-footer a:hover { text-decoration: underline; }

        .ac-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .ac-copy { text-align: center; font-size: 11.5px; color: rgba(255,255,255,.75); margin-top: 16px; }

        :focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        .ac-input:focus-visible { outline: none; }

        @media (max-width: 560px) {
            body { padding: 16px 12px; }
            .ac-grid, .ac-rules { grid-template-columns: 1fr; }
            .ac-brand, .ac-body, .ac-footer { padding-left: 18px; padding-right: 18px; }
            .ac-heading { font-size: 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ac-wrap { animation: none; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>
<main class="ac-wrap @yield('wrap-class')">
    <div class="ac-card">
        <header class="ac-brand">
            <img src="{{ asset('images/logo.png') }}" alt="Sto. Domingo National High School logo">
            <div>
                <div class="ac-brand-name">DP-LMS</div>
                <div class="ac-brand-school">Sto. Domingo National High School</div>
            </div>
        </header>

        <div class="ac-body">
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

    // Prevent double submit
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = btn.getAttribute('data-busy') || 'Please wait…'; }
        });
    });
</script>
</body>
</html>
