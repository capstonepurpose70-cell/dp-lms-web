<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <title>Login — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- GSAP for High-Performance Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- Distinctive type pairing (display + body), with strong system fallbacks -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Custom easing curves (Emil: built-ins too weak) ── */
        :root {
            --ease-out-strong:    cubic-bezier(0.23, 1, 0.32, 1);
            --ease-in-out-strong: cubic-bezier(0.77, 0, 0.175, 1);
            --ease-drawer:        cubic-bezier(0.32, 0.72, 0, 1);

            /* ── Brand system (unified emerald accent) ── */
            --brand-500: #22c55e;
            --brand-600: #16a34a;
            --brand-700: #15803d;
            --brand-800: #166534;
            --brand-ring: rgba(22, 163, 74, 0.16);

            /* ── Neutrals ── */
            --ink:        #111827;
            --ink-soft:   #374151;
            --ink-muted:  #6b7280;
            --line:       #e5e7eb;

            /* ── Type ── */
            --font-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
            --font-body:    'Plus Jakarta Sans', 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: var(--font-body);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: url('/images/bg.jpg') center/cover no-repeat;
            position: relative;
            overflow: hidden;
            opacity: 0;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        /* ── Blur overlay ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: inherit;
            transform: scale(1.05);
            filter: blur(12px);
            z-index: 0;
        }

        /* ── Dark overlay (subtle emerald-tinted vignette for depth) ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(120% 100% at 50% 0%, rgba(6, 78, 59, 0.30), transparent 55%),
                rgba(0, 0, 0, 0.62);
            z-index: 0;
        }

        /* ══ WRAPPER ══════════════════════════════════════════════ */
        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
        }

        /* ══ LOGO ═════════════════════════════════════════════════ */
        .logo-wrap {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 0;
            opacity: 0;
            transform: translateY(20px);
        }

        .logo-box {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 12px;
            background: #f0fdf4;
            box-shadow:
                0 0 0 3px rgba(255, 255, 255, 0.85),
                0 0 0 6px rgba(22, 163, 74, 0.18),
                0 10px 28px rgba(0, 0, 0, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 6px;
        }

        .logo-title {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.1;
            letter-spacing: 0.01em;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.45);
        }

        .logo-sub {
            font-size: 12.5px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.92);
            margin-top: 5px;
            letter-spacing: 0.01em;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.5);
        }

        .divider-card-top {
            margin: 0 0 18px;
        }

        .divider-card-top .divider-line {
            height: 1px;
            background: #f3f4f6;
        }

        /* ══ CARD ══════════════════════════════════════════════════ */
        .card {
            position: relative;
            overflow: hidden;
            background: rgba(248, 248, 255, 0.97);
            border-radius: 22px;
            box-shadow:
                0 4px 6px rgba(0, 0, 0, 0.06),
                0 24px 64px rgba(0, 0, 0, 0.28),
                0 0 0 1px rgba(255, 255, 255, 0.12);
            padding: 34px 30px 28px;
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateY(28px) scale(0.98);
            will-change: transform, opacity;
        }

        /* Brand accent bar — quiet institutional signature */
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-500), var(--brand-700));
        }

        .card-heading {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 700;
            color: var(--ink-soft);
            margin-bottom: 22px;
            text-align: center;
            letter-spacing: -0.01em;
        }

        /* ══ ALERTS ════════════════════════════════════════════════ */
        .alert {
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.6;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-8px);
        }

        .alert-success {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            color: #15803d;
        }

        .alert-error {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            color: #b91c1c;
        }

        /* ══ HONEYPOT (bot trap — never shown to humans) ═══════════ */
        .hp-field {
            position: absolute !important;
            left: -9999px !important;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        /* ══ FORM ══════════════════════════════════════════════════ */
        .form-group {
            margin-bottom: 16px;
            opacity: 0;
            transform: translateY(8px);
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--ink-soft);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Input Wrapper for Icons */
        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            /* Specific props only — never transition: all */
            transition: color 200ms var(--ease-out-strong);
        }

        .input-icon svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--brand-600);
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            padding: 13px 14px 13px 42px;
            font-size: 14px;
            font-weight: 500;
            color: var(--ink);
            background: #f9fafb;
            outline: none;
            font-family: inherit;
            /* Specific props — no 'all', no padding-left transition */
            transition:
                border-color 180ms var(--ease-out-strong),
                box-shadow   180ms var(--ease-out-strong),
                background   180ms var(--ease-out-strong);
        }

        input:focus {
            border-color: var(--brand-600);
            box-shadow: 0 0 0 3px var(--brand-ring);
            background: #ffffff;
        }

        input::placeholder {
            color: #c9cdd6;
            font-size: 13px;
            font-weight: 400;
        }

        .input-error-border {
            border-color: #fca5a5 !important;
            background: #fff5f5 !important;
        }

        .input-error-border:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.14) !important;
        }

        .error-msg {
            font-size: 12px;
            color: #ef4444;
            margin-top: 5px;
            font-weight: 500;
        }

        /* Client-side error placeholder (hidden until JS validation fires) */
        .js-error[hidden] { display: none; }

        /* ══ PASSWORD TOGGLE ═══════════════════════════════════════ */
        .password-wrap {
            position: relative;
        }

        .password-wrap .input-wrapper input {
            padding-left: 42px;
            padding-right: 46px;
        }

        .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            /* Specific props only */
            transition: color 180ms var(--ease-out-strong);
            z-index: 2;
        }

        /* Gate hover on pointer devices only (Emil: touch falsely triggers hover) */
        @media (hover: hover) and (pointer: fine) {
            .toggle-btn:hover {
                color: var(--brand-600);
            }
        }

        .toggle-btn svg {
            width: 18px;
            height: 18px;
        }

        .icon-eye-off { display: none; }

        /* ══ SUBMIT BUTTON ═════════════════════════════════════════ */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
            color: white;
            border: none;
            border-radius: 11px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            margin-top: 6px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            /* Specific transition — no 'all' */
            transition:
                background  220ms var(--ease-out-strong),
                box-shadow  220ms var(--ease-out-strong);
            box-shadow:
                0 1px 3px rgba(0, 0, 0, 0.12),
                0 6px 16px rgba(22, 163, 74, 0.28),
                0 1px 2px rgba(22, 163, 74, 0.08);
        }

        /* Shimmer on hover — CSS transition, no JS needed */
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.18),
                transparent
            );
            transition: left 480ms var(--ease-out-strong);
        }

        /* Gate hover on pointer devices */
        @media (hover: hover) and (pointer: fine) {
            .btn-submit:hover {
                background: linear-gradient(135deg, var(--brand-500), var(--brand-600));
            }

            .btn-submit:hover::before {
                left: 150%;
            }
        }

        .btn-submit:active {
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.10),
                0 2px 6px rgba(22, 163, 74, 0.15);
            transition:
                box-shadow 100ms var(--ease-out-strong),
                background 100ms var(--ease-out-strong);
        }

        /* ══ FORGOT PASSWORD ═══════════════════════════════════════ */
        .forgot-wrap {
            text-align: center;
            margin-top: 14px;
            opacity: 0;
        }

        .forgot-link {
            font-size: 13px;
            color: var(--ink-muted);
            font-weight: 500;
            text-decoration: none;
            position: relative;
            display: inline-block;
            transition:
                color     200ms var(--ease-out-strong),
                transform 200ms var(--ease-out-strong);
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 50%;
            width: 0;
            height: 1px;
            background: var(--brand-700);
            transition:
                width 260ms var(--ease-out-strong),
                left  260ms var(--ease-out-strong);
        }

        @media (hover: hover) and (pointer: fine) {
            .forgot-link:hover {
                color: var(--brand-700);
                transform: scale(1.06);
            }

            .forgot-link:hover::after {
                width: 100%;
                left: 0;
            }
        }

        /* ══ DIVIDER ═══════════════════════════════════════════════ */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 0;
            opacity: 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: #f3f4f6;
        }

        .divider-text {
            font-size: 11px;
            color: #d1d5db;
            font-weight: 500;
        }

        /* ══ CARD FOOTER ═══════════════════════════════════════════ */
        .card-footer {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: var(--ink-muted);
            font-weight: 500;
            opacity: 0;
        }

        .card-footer a {
            color: var(--brand-700);
            font-weight: 700;
            text-decoration: none;
            transition: color 180ms var(--ease-out-strong);
        }

        @media (hover: hover) and (pointer: fine) {
            .card-footer a:hover {
                color: var(--brand-800);
                text-decoration: underline;
            }
        }

        /* ══ COPYRIGHT ═════════════════════════════════════════════ */
        .copyright {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.55);
            margin-top: 18px;
            opacity: 0;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.4);
        }

        /* ══ ACCESSIBILITY: keyboard focus rings ═══════════════════ */
        .btn-submit:focus-visible,
        .forgot-link:focus-visible,
        .card-footer a:focus-visible,
        .toggle-btn:focus-visible {
            outline: 2px solid #ffffff;
            outline-offset: 2px;
            border-radius: 6px;
        }

        /* ══ SPINNER ═══════════════════════════════════════════════ */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, 0.35);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.65s linear infinite;
            flex-shrink: 0;
        }

        /* ══ INVALID SHAKE (applied to inputs only — no GSAP conflict) ══ */
        @keyframes field-shake {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }

        .shake {
            animation: field-shake 0.4s var(--ease-out-strong);
        }

        /* ══ RESPONSIVE ════════════════════════════════════════════ */
        @media (max-width: 480px) {
            .card {
                padding: 28px 22px 24px;
                border-radius: 18px;
            }

            .logo-box {
                width: 86px;
                height: 86px;
            }

            .logo-title { font-size: 21px; }
            .logo-sub   { font-size: 12px; }
        }

        /* ══ REDUCED MOTION (Emil: accessibility required) ═════════ */
        @media (prefers-reduced-motion: reduce) {
            .logo-box {
                animation: none;
            }

            .btn-submit {
                animation: none;
                box-shadow:
                    0 0 8px  rgba(22, 163, 74, 0.5),
                    0 4px 16px rgba(22, 163, 74, 0.3);
            }

            .shake {
                animation: none;
            }

            /* Keep opacity transitions — remove position/scale motion */
            input, .btn-submit, .toggle-btn, .forgot-link {
                transition-property: opacity, color, background, border-color, box-shadow;
                transition-duration: 150ms;
            }
        }
    </style>

    <noscript>
        <style>
            body { opacity: 1 !important; }
            .card, .logo-wrap, .form-group,
            .forgot-wrap, .divider, .card-footer,
            .copyright { opacity: 1 !important; transform: none !important; }
        </style>
    </noscript>
</head>
<body>
<div class="wrapper">

    <div class="logo-wrap gsap-logo">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}"
                 alt="DP-LMS Logo"
                 style="width:100%; height:100%; object-fit:contain; padding:6px;">
        </div>
        <p class="logo-title">DP-LMS</p>
        <p class="logo-sub">Sto. Domingo National High School</p>
    </div>

    <div class="card gsap-card">

        <h2 class="card-heading">Sign in to your account</h2>

        @if(session('success'))
            <div class="alert alert-success gsap-alert">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error gsap-alert">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" novalidate>
            @csrf

            {{-- Honeypot bot trap. Real users never see or fill this.
                 IMPORTANT: also reject server-side when this field is non-empty. --}}
            <div class="hp-field" aria-hidden="true">
                <label for="company">Company</label>
                <input type="text" name="company" id="company"
                       tabindex="-1" autocomplete="off">
            </div>

            {{-- Email --}}
            <div class="form-group gsap-form-item">
                <label for="email-field">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <input type="email" name="email"
                           id="email-field"
                           value="{{ old('email') }}"
                           placeholder="you@gmail.com"
                           autocomplete="email"
                           inputmode="email"
                           autocapitalize="none"
                           spellcheck="false"
                           maxlength="254"
                           required
                           class="{{ $errors->has('email') ? 'input-error-border' : '' }}">
                </div>
                @error('email')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
                <p class="error-msg js-error" data-error-for="email" hidden></p>
            </div>

            {{-- Password --}}
            <div class="form-group gsap-form-item">
                <label for="password-field">Password</label>
                <div class="password-wrap">
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <input type="password"
                               name="password"
                               id="password-field"
                               placeholder="Enter your password"
                               autocomplete="current-password"
                               maxlength="100"
                               required
                               class="{{ $errors->has('password') ? 'input-error-border' : '' }}">
                    </div>

                    <button type="button"
                            class="toggle-btn gsap-hover-icon"
                            onclick="togglePassword()"
                            tabindex="-1"
                            aria-label="Toggle password visibility">
                        <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478
                                  0 8.268 2.943 9.542 7-1.274 4.057-5.064
                                  7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478
                                  0-8.268-2.943-9.543-7a9.97 9.97 0
                                  011.563-3.029m5.858.908a3 3 0 114.243
                                  4.243M9.878 9.878l4.242 4.242M9.88
                                  9.88l-3.29-3.29m7.532 7.532l3.29
                                  3.29M3 3l3.59 3.59m0 0A9.953 9.953
                                  0 0112 5c4.478 0 8.268 2.943 9.543
                                  7a10.025 10.025 0 01-4.132
                                  5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
                <p class="error-msg js-error" data-error-for="password" hidden></p>
            </div>

            {{-- Submit --}}
            <button type="submit" id="login-btn" class="btn-submit gsap-btn-submit">
                <svg id="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span id="btn-text">Sign in</span>
            </button>

            {{-- Forgot password --}}
            <div class="forgot-wrap gsap-footer-item">
                <a href="{{ route('password.request') }}" class="forgot-link">
                    Forgot your password?
                </a>
            </div>
        </form>

        <div class="divider gsap-footer-item">
            <div class="divider-line"></div>
            <span class="divider-text">or</span>
            <div class="divider-line"></div>
        </div>

        <div class="card-footer gsap-footer-item">
            Don't have an account?
            <a href="{{ route('register') }}" onclick="slideTo(this, event)">Register here</a>
        </div>
    </div>

    <p class="copyright gsap-copyright">
        &copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.
    </p>
</div>

<script>
(function() {

    // ── Toggle Password ──────────────────────────────────────────
    function togglePassword() {
        const field  = document.getElementById('password-field');
        const eyeOn  = document.querySelector('.icon-eye');
        const eyeOff = document.querySelector('.icon-eye-off');
        const hidden = field.type === 'password';
        field.type           = hidden ? 'text'     : 'password';
        eyeOn.style.display  = hidden ? 'none'     : 'block';
        eyeOff.style.display = hidden ? 'block'    : 'none';
    }

    // ── Slide-out nav ────────────────────────────────────────────
    function slideTo(anchor, e) {
        e.preventDefault();
        const url = anchor.href;
        gsap.to('.wrapper', {
            x: -50,
            opacity: 0,
            duration: 0.3,
            ease: 'power2.in',
            onComplete: () => { window.location.href = url; }
        });
    }

    // Expose sa global para magamit ng HTML onclick
    window.togglePassword = togglePassword;
    window.slideTo = slideTo;

    // ── GSAP Animations ──────────────────────────────────────────
    document.addEventListener("DOMContentLoaded", () => {

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reducedMotion) {
            gsap.set('body', { opacity: 1 });
            gsap.to(['.gsap-logo', '.gsap-card', '.gsap-alert', '.gsap-form-item', '.gsap-footer-item', '.gsap-copyright'], {
                opacity: 1, duration: 0.3, stagger: 0.04
            });
        } else {

            gsap.set('.wrapper', { x: 50, opacity: 0 });

            const tl = gsap.timeline({
                defaults: { ease: "power3.out" }
            });

            tl
            .to("body", { opacity: 1, duration: 0.35 })
            .to(".wrapper", {
                x: 0, opacity: 1,
                duration: 0.45,
                ease: "power3.out"
            }, "-=0.2")
            .to(".gsap-logo", {
                opacity: 1, y: 0,
                duration: 0.5,
                ease: "back.out(1.4)"
            }, "-=0.2")
            .from(".logo-box", {
                scale: 0.82,
                duration: 0.55,
                ease: "elastic.out(1, 0.55)"
            }, "-=0.45")
            .to(".gsap-card", {
                opacity: 1, y: 0, scale: 1,
                duration: 0.5,
                ease: "power3.out"
            }, "-=0.3")
            .to(".gsap-alert", {
                opacity: 1, y: 0,
                duration: 0.3
            }, "-=0.25")
            .to(".gsap-form-item", {
                opacity: 1, y: 0,
                stagger: 0.07,
                duration: 0.35
            }, "-=0.2")
            .to(".gsap-footer-item", {
                opacity: 1,
                stagger: 0.06,
                duration: 0.3
            }, "-=0.15")
            .to(".gsap-copyright", {
                opacity: 1,
                duration: 0.5
            }, "-=0.1");

        }

        // ── Client-side validation (UX layer — the server is the real gate) ──
        const loginForm = document.querySelector('form');
        const loginBtn  = document.getElementById('login-btn');
        const btnIcon   = document.getElementById('btn-icon');
        const btnText   = document.getElementById('btn-text');

        const emailField = document.getElementById('email-field');
        const passField  = document.getElementById('password-field');
        const honeypot   = document.getElementById('company');

        const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function errorEl(name) {
            return document.querySelector('.js-error[data-error-for="' + name + '"]');
        }

        function showError(field, name, message) {
            field.classList.add('input-error-border');
            field.setAttribute('aria-invalid', 'true');
            const el = errorEl(name);
            if (el) { el.textContent = message; el.hidden = false; }
            if (!reducedMotion) {
                field.classList.remove('shake');
                // reflow so the animation can replay
                void field.offsetWidth;
                field.classList.add('shake');
            }
        }

        function clearError(field, name) {
            field.classList.remove('input-error-border');
            field.removeAttribute('aria-invalid');
            const el = errorEl(name);
            if (el) { el.hidden = true; el.textContent = ''; }
        }

        function validateForm() {
            let valid = true;
            let firstInvalid = null;

            const emailVal = emailField.value.trim();
            if (emailVal === '') {
                showError(emailField, 'email', 'Email address is required.');
                valid = false; firstInvalid = firstInvalid || emailField;
            } else if (!EMAIL_RE.test(emailVal)) {
                showError(emailField, 'email', 'Please enter a valid email address.');
                valid = false; firstInvalid = firstInvalid || emailField;
            } else {
                clearError(emailField, 'email');
            }

            if (passField.value === '') {
                showError(passField, 'password', 'Password is required.');
                valid = false; firstInvalid = firstInvalid || passField;
            } else {
                clearError(passField, 'password');
            }

            if (firstInvalid) firstInvalid.focus();
            return valid;
        }

        // Clear a field's error as soon as the user starts correcting it
        emailField.addEventListener('input', () => clearError(emailField, 'email'));
        passField.addEventListener('input', () => clearError(passField, 'password'));
        [emailField, passField].forEach(f => {
            f.addEventListener('animationend', () => f.classList.remove('shake'));
        });

        // ── Submit: validate + honeypot first, THEN the loading state ──
        loginForm.addEventListener('submit', function(e) {
            // Bot trap: silently block if the hidden field was filled
            if (honeypot && honeypot.value.trim() !== '') {
                e.preventDefault();
                return;
            }
            // Stop the submit (and the spinner) when input is invalid
            if (!validateForm()) {
                e.preventDefault();
                return;
            }

            // Valid → original loading behaviour, unchanged
            loginBtn.disabled      = true;
            loginBtn.style.opacity = '0.85';
            loginBtn.style.cursor  = 'not-allowed';
            btnIcon.style.display = 'none';
            const spinner = document.createElement('div');
            spinner.className = 'spinner';
            loginBtn.insertBefore(spinner, btnText);
            btnText.textContent = 'Signing in...';
        });

        // ── Button hover ─────────────────────────────────────────
        const btn = document.querySelector(".gsap-btn-submit");
        const isPointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

        if (isPointer && !reducedMotion) {
            btn.addEventListener("mouseenter", () => {
                gsap.to(btn, { y: -2, duration: 0.25, ease: "power2.out" });
            });
            btn.addEventListener("mouseleave", () => {
                gsap.to(btn, { y: 0, duration: 0.25, ease: "power2.out" });
            });
        }

        btn.addEventListener("mousedown",  () => gsap.to(btn, { scale: 0.97, duration: 0.1 }));
        btn.addEventListener("mouseup",    () => gsap.to(btn, { scale: 1,    duration: 0.12 }));
        btn.addEventListener("mouseleave", () => gsap.to(btn, { scale: 1,    duration: 0.12 }));

        // ── Toggle icon ──────────────────────────────────────────
        const toggleBtn = document.querySelector(".gsap-hover-icon");
        if (isPointer && !reducedMotion) {
            toggleBtn.addEventListener("mouseenter", () => {
                gsap.to(toggleBtn, { scale: 1.15, duration: 0.18, ease: "back.out(2)" });
            });
            toggleBtn.addEventListener("mouseleave", () => {
                gsap.to(toggleBtn, { scale: 1, duration: 0.18, ease: "power2.out" });
            });
        }

    });

})();
</script>

</body>
</html>