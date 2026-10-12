<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Iisang itsura para sa activation / parent pages. Plain CSS + tokens —
         walang bagong Tailwind class (luma ang build). --}}
    <style>
        :root {
            --accent: #2563EB; --accent-dk: #1d4ed8; --accent-lt: #eff6ff;
            --ok: #16a34a; --ok-dk: #15803d;
            --text-1: #111827; --text-2: #6b7280; --text-3: #9ca3af;
            --surface: #fff; --field: #fafafa; --border: #e5e7eb;
            --r-sm: 9px; --r-md: 11px; --r-lg: 20px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
            background: #1e293b url('/images/bg.jpg') center/cover no-repeat fixed;
            position: relative;
        }
        body::after { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,.42); z-index: 0; }

        .ac-wrap {
            position: relative; z-index: 1; width: 100%; max-width: 440px;
            animation: acUp .45s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes acUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }

        .ac-logo {
            width: 72px; height: 72px; border-radius: 18px; overflow: hidden; margin: 0 auto 16px;
            background: #fff; box-shadow: 0 8px 28px rgba(29,78,216,.22);
            display: flex; align-items: center; justify-content: center;
        }
        .ac-logo img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }

        .ac-card {
            background: var(--surface); border-radius: var(--r-lg);
            box-shadow: 0 2px 4px rgba(0,0,0,.04), 0 16px 48px rgba(0,0,0,.09), 0 0 0 1px rgba(0,0,0,.05);
            padding: 30px 28px 24px;
        }
        .ac-system { font-size: 13px; font-weight: 800; color: #1e293b; text-align: center; letter-spacing: .02em; text-transform: uppercase; }
        .ac-school { font-size: 12.5px; font-weight: 600; color: #64748b; text-align: center; margin: 4px 0 18px; }

        .ac-icon {
            width: 52px; height: 52px; background: var(--accent-lt); border-radius: 14px;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; color: var(--accent);
        }
        .ac-icon svg { width: 24px; height: 24px; }
        .ac-heading { font-size: 19px; font-weight: 800; color: var(--text-1); text-align: center; margin-bottom: 6px; }
        .ac-desc { font-size: 13px; color: var(--text-2); text-align: center; margin-bottom: 20px; line-height: 1.6; }

        .ac-alert { border-radius: 10px; padding: 11px 14px; font-size: 13px; margin-bottom: 16px; line-height: 1.6; }
        .ac-alert ul { padding-left: 16px; }
        .ac-alert-ok   { background: #f0fdf4; border: 1.5px solid #86efac; color: var(--ok-dk); }
        .ac-alert-err  { background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c; }
        .ac-alert-info { background: var(--accent-lt); border: 1.5px solid #bfdbfe; color: #1e40af; }

        .ac-group { margin-bottom: 14px; }
        .ac-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-1); margin-bottom: 5px; }
        .ac-group .ac-opt { font-weight: 500; color: var(--text-3); }
        .ac-hint { font-size: 12px; color: var(--text-2); margin-top: 4px; line-height: 1.5; }
        .ac-err  { font-size: 12px; color: #dc2626; margin-top: 4px; }

        .ac-input {
            width: 100%; min-height: 44px; border: 1.5px solid var(--border); border-radius: var(--r-sm);
            padding: 11px 13px; font-size: 15px; font-weight: 500; color: var(--text-1);
            background: var(--field); outline: none; font-family: inherit;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .ac-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.14); background: #fff; }
        .ac-input.is-error { border-color: #f87171; }
        .ac-input::placeholder { color: #c4c9d4; font-size: 13.5px; }
        .ac-code {
            text-align: center; font-size: 24px; font-weight: 800; letter-spacing: .3em;
            text-transform: uppercase; font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace;
        }

        .ac-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; color: var(--text-2); margin: 4px 0 16px; cursor: pointer; line-height: 1.5; }
        .ac-check input { width: 18px; height: 18px; margin-top: 1px; accent-color: var(--accent); flex-shrink: 0; }

        .ac-btn {
            width: 100%; min-height: 46px; border: none; border-radius: var(--r-md); cursor: pointer;
            background: var(--ok); color: #fff; font-size: 15px; font-weight: 700; letter-spacing: .02em;
            box-shadow: 0 4px 16px rgba(22,163,74,.3); transition: background .2s, transform .1s;
            font-family: inherit;
        }
        .ac-btn:hover { background: var(--ok-dk); }
        .ac-btn:active { transform: scale(.985); }
        .ac-btn-link {
            background: none; border: none; color: var(--accent); font-weight: 700; font-size: 13px;
            cursor: pointer; padding: 6px 4px; min-height: 44px; font-family: inherit;
        }
        .ac-btn-link:hover { text-decoration: underline; }

        .ac-footer { text-align: center; margin-top: 18px; font-size: 13px; color: var(--text-2); border-top: 1px solid #f1f5f9; padding-top: 14px; line-height: 2; }
        .ac-footer a { color: var(--accent); font-weight: 700; text-decoration: none; }
        .ac-footer a:hover { text-decoration: underline; }

        /* Honeypot — nakatago sa tao, nakikita ng bot */
        .ac-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

        .ac-copy { text-align: center; font-size: 11px; color: #fff; margin-top: 16px; }

        :focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
        .ac-input:focus-visible { outline: none; }

        @media (max-width: 420px) { .ac-card { padding: 24px 18px 20px; } }
        @media (prefers-reduced-motion: reduce) {
            .ac-wrap { animation: none; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>
<main class="ac-wrap">
    <div class="ac-logo"><img src="{{ asset('images/logo.png') }}" alt="Sto. Domingo NHS logo"></div>

    <div class="ac-card">
        <p class="ac-system">Digital Platform Learning Management System</p>
        <p class="ac-school">Sto. Domingo National High School</p>

        <div class="ac-icon" aria-hidden="true">@yield('icon')</div>
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
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')

        <div class="ac-footer">@yield('footer')</div>
    </div>

    <p class="ac-copy">&copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.</p>
</main>
</body>
</html>
