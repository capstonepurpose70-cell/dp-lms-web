<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <title>Login — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts (same pairing as Register) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <style>
        :root {
            --green-500: #16a34a;
            --green-600: #15803d;
            --green-700: #166534;
            --green-50:  #f0fdf4;
            --green-100: #dcfce7;
            --red-50:    #fef2f2;
            --red-500:   #ef4444;
            --text-dark: #0f172a;
            --text-mid:  #334155;
            --text-muted:#64748b;
            --border:    #e2e8f0;
            --surface:   #f8fafc;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            display: flex;
            background: #f8fafc;
            overflow-x: hidden;
            opacity: 0;
        }

        /* ══════════════════════════════════════
           LEFT PANEL
        ══════════════════════════════════════ */
        .left-panel {
            position: relative;
            width: 48%;
            height: 100vh;
            flex-shrink: 0;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            will-change: transform;
            transform: translateZ(0);
        }
        @keyframes bgPan {
            0%   { transform: scale(1.08) translate(0px, 0px); }
            50%  { transform: scale(1.1) translate(-15px, -8px); }
            100% { transform: scale(1.08) translate(8px, 10px); }
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(0,0,0,0.18) 0%,
                rgba(0,0,0,0.45) 50%,
                rgba(0,0,0,0.88) 100%
            );
            z-index: 1;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            inset: -20px;
            background-image: url('{{ asset("images/bg.jpg") }}');
            background-size: cover;
            background-position: center;
            z-index: 0;
            animation: bgPan 20s ease-in-out infinite alternate;
            will-change: transform;
        }

        #particles-canvas {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        .lp-center {
            position: absolute;
            top: 50%;
            left: 0; right: 0;
            transform: translateY(-50%);
            z-index: 3;
            padding: 0 44px;
            text-align: center;
            opacity: 0;
        }

        .lp-logo {
            width: 150px;
            height: 150px;
            object-fit: contain;
            background: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        }

        .lp-verse {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-weight: 400;
            font-style: italic;
            color: #ffffff;
            line-height: 1.7;
            max-width: 360px;
            margin: 0 auto 10px;
        }

        .lp-verse-ref {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ffffff;
        }

        .lp-bottom {
            position: absolute;
            bottom: 48px;
            left: 0; right: 0;
            z-index: 3;
            padding: 0 44px;
            opacity: 0;
        }

        .lp-welcome {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--green-500);
            margin-bottom: 10px;
        }

        .lp-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 52px;
            font-weight: 700;
            line-height: 1.05;
            color: #fff;
            text-shadow: 0 4px 32px rgba(0,0,0,0.6);
            margin-bottom: 12px;
        }

        .lp-subtitle {
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: rgba(255,255,255,0.65);
            line-height: 1.6;
        }

        .lp-bar {
            width: 40px;
            height: 3px;
            background: var(--green-500);
            border-radius: 2px;
            margin-bottom: 18px;
        }

        /* ══════════════════════════════════════
           RIGHT PANEL
        ══════════════════════════════════════ */
        .right-panel {
            flex: 1;
            height: 100vh;
            overflow-y: auto;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* top clearance so the fixed navbar never covers the form */
            padding: 92px 52px 36px 52px;
        }

        .form-header {
            margin-bottom: 28px;
            opacity: 0;
            transform: translateY(12px);
        }

        .form-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
            line-height: 1.1;
        }

        .form-header p {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Alerts */
        .server-alert {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
        }
        .server-alert.success {
            background: var(--green-50);
            color: var(--green-700);
            border-left: 3px solid var(--green-500);
        }
        .server-alert.error {
            background: var(--red-50);
            color: #991b1b;
            border-left: 3px solid var(--red-500);
        }

        /* Honeypot (bot trap — never shown to humans) */
        .hp-field {
            position: absolute !important;
            left: -9999px !important;
            top: auto;
            width: 1px; height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
        }

        .form-group {
            margin-bottom: 16px;
            opacity: 0;
            transform: translateY(8px);
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-mid);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-dark);
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--green-500);
            box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
        }

        input.is-error {
            border-color: var(--red-500);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.08);
        }
        input.is-valid { border-color: var(--green-500); }

        .error-msg {
            font-size: 11px;
            color: var(--red-500);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .email-status {
            font-size: 11px;
            margin-top: 4px;
            height: 14px;
            transition: color 0.2s;
        }

        /* Password */
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 42px; }

        .toggle-pw {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 0;
            display: flex;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: var(--green-600); }
        .toggle-pw svg { width: 17px; height: 17px; }
        .pw-hide { display: none; }

        /* Forgot */
        .forgot-wrap {
            text-align: right;
            margin: -6px 0 6px;
            opacity: 0;
        }
        .forgot-wrap a {
            font-size: 12.5px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .forgot-wrap a:hover { color: var(--green-600); text-decoration: underline; }

        /* Submit */
        .btn-submit {
            width: 100%;
            background: var(--green-500);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.4px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background 0.2s, transform 0.15s, opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
            opacity: 0;
        }
        .btn-submit:hover:not(.loading) { background: var(--green-600); }
        .btn-submit:active:not(.loading) { transform: scale(0.985); }

        .btn-spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
            position: absolute;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-submit.loading .btn-text,
        .btn-submit.loading .btn-ico { opacity: 0; }
        .btn-submit.loading .btn-spinner { display: block; }

        /* Footer */
        .card-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
            opacity: 0;
        }
        .card-footer a {
            color: var(--green-600);
            font-weight: 600;
            text-decoration: none;
        }
        .card-footer a:hover { text-decoration: underline; }

        .copyright {
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 16px;
            opacity: 0;
        }

        /* Responsive */
        @media (max-width: 960px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; padding: 84px 24px 28px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .left-panel::after { animation: none; }
        }
    </style>

    <noscript>
        <style>
            body { opacity: 1 !important; }
            .lp-center, .lp-bottom, .form-header, .form-group,
            .forgot-wrap, .btn-submit, .card-footer, .copyright,
            .server-alert { opacity: 1 !important; transform: none !important; }
        </style>
    </noscript>
</head>
<body>
@include('partials.auth-navbar')

    {{-- ── LEFT PANEL ── --}}
    <div class="left-panel">
        <canvas id="particles-canvas"></canvas>

        {{-- Center: Logo + Verse --}}
        <div class="lp-center gsap-lp-center">
            <img src="{{ asset('images/logo.png') }}" alt="DP-LMS Logo" class="lp-logo" style="margin: 0 auto 20px; display: block;">
            <p class="lp-verse">
                "Apply your heart to instruction<br>
                and your ears to words of knowledge."
            </p>
            <p class="lp-verse-ref">— Proverbs 23:12</p>
        </div>

        {{-- Bottom: Welcome + School --}}
        <div class="lp-bottom gsap-lp-bottom">
            <div class="lp-bar"></div>
            <p class="lp-welcome">Welcome back to</p>
            <h1 class="lp-title">DP-LMS</h1>
            <p class="lp-subtitle">
                Sto. Domingo National High School<br>
                Digital Learning Management System
            </p>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="right-panel">

        <div class="form-header gsap-form-header">
            <h2>Sign in to your account</h2>
            <p>Welcome back! Please enter your details to continue.</p>
        </div>

        @if(session('success'))
            <div class="server-alert success gsap-alert">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="server-alert error gsap-alert">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" id="loginForm" novalidate>
            @csrf

            {{-- Honeypot bot trap. Real users never see or fill this.
                 IMPORTANT: also reject server-side when this field is non-empty. --}}
            <div class="hp-field" aria-hidden="true">
                <label for="company">Company</label>
                <input type="text" name="company" id="company" tabindex="-1" autocomplete="off">
            </div>

            {{-- Email --}}
            <div class="form-group gsap-field">
                <label>Email Address</label>
                <input type="email" name="email" id="emailInput"
                    value="{{ old('email') }}"
                    placeholder="you@gmail.com"
                    autocomplete="email"
                    inputmode="email"
                    autocapitalize="none"
                    spellcheck="false"
                    maxlength="254"
                    class="{{ $errors->has('email') ? 'is-error' : '' }}"
                    required>
                <p class="email-status" id="emailStatus"></p>
                @error('email')<p class="error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Password --}}
            <div class="form-group gsap-field">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="passwordInput"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        maxlength="100"
                        class="{{ $errors->has('password') ? 'is-error' : '' }}"
                        required>
                    <button type="button" class="toggle-pw" onclick="togglePw('passwordInput', this)" tabindex="-1" aria-label="Toggle password visibility">
                        <svg class="pw-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="pw-hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error('password')<p class="error-msg">{{ $message }}</p>@enderror
                <p class="error-msg" id="passwordError" style="display:none;"></p>
            </div>

            {{-- Forgot --}}
            <div class="forgot-wrap gsap-field">
                <a href="{{ route('password.request') }}">Forgot your password?</a>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-submit gsap-btn" id="submitBtn">
                <svg class="btn-ico" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span class="btn-text">Sign in</span>
                <div class="btn-spinner"></div>
            </button>
        </form>

        <div class="card-footer gsap-footer">
            Don't have an account? <a href="{{ route('register') }}" onclick="slideTo(this, event)">Register here</a>
        </div>

        <p class="copyright gsap-footer">
            &copy; {{ date('Y') }} Sto. Domingo National High School · All rights reserved.
        </p>
    </div>

<script>
/* ─────────────────────────────────────────────
   PARTICLES  (lightweight canvas dots)
───────────────────────────────────────────── */
(function() {
    const canvas = document.getElementById('particles-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W = canvas.width = canvas.offsetWidth;
    const H = canvas.height = canvas.offsetHeight;
    const dots = Array.from({ length: 38 }, () => ({
        x: Math.random() * W,
        y: Math.random() * H,
        r: Math.random() * 1.6 + 0.4,
        vx: (Math.random() - 0.5) * 0.25,
        vy: (Math.random() - 0.5) * 0.25,
        a: Math.random() * 0.4 + 0.15
    }));
    function draw() {
        ctx.clearRect(0, 0, W, H);
        dots.forEach(d => {
            d.x += d.vx; d.y += d.vy;
            if (d.x < 0) d.x = W; if (d.x > W) d.x = 0;
            if (d.y < 0) d.y = H; if (d.y > H) d.y = 0;
            ctx.beginPath();
            ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${d.a})`;
            ctx.fill();
        });
        requestAnimationFrame(draw);
    }
    draw();
})();

/* ─────────────────────────────────────────────
   PASSWORD TOGGLE
───────────────────────────────────────────── */
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const show = btn.querySelector('.pw-show');
    const hide = btn.querySelector('.pw-hide');
    if (input.type === 'password') {
        input.type = 'text';
        show.style.display = 'none';
        hide.style.display = 'block';
    } else {
        input.type = 'password';
        show.style.display = 'block';
        hide.style.display = 'none';
    }
}

/* ─────────────────────────────────────────────
   EMAIL LIVE VALIDATION (UX only)
───────────────────────────────────────────── */
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
document.getElementById('emailInput').addEventListener('input', function() {
    const val = this.value.trim();
    const status = document.getElementById('emailStatus');
    if (!val) { status.textContent = ''; this.classList.remove('is-valid','is-error'); return; }
    if (EMAIL_RE.test(val)) {
        status.textContent = '✓ Valid email format';
        status.style.color = '#16a34a';
        this.classList.remove('is-error');
        this.classList.add('is-valid');
    } else {
        status.textContent = 'Please enter a valid email address';
        status.style.color = '#ef4444';
        this.classList.remove('is-valid');
    }
});

/* ─────────────────────────────────────────────
   SUBMIT: honeypot + validation + loading
   (Server is the real gate — this is UX only)
───────────────────────────────────────────── */
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email    = document.getElementById('emailInput');
    const password = document.getElementById('passwordInput');
    const honeypot = document.getElementById('company');
    const emailStatus = document.getElementById('emailStatus');
    const passError   = document.getElementById('passwordError');

    // Bot trap: silently block if the hidden field was filled
    if (honeypot && honeypot.value.trim() !== '') {
        e.preventDefault();
        return;
    }

    let valid = true;
    let firstInvalid = null;

    // Email
    const emailVal = email.value.trim();
    if (emailVal === '') {
        email.classList.add('is-error'); email.classList.remove('is-valid');
        emailStatus.textContent = 'Email address is required.';
        emailStatus.style.color = '#ef4444';
        valid = false; firstInvalid = firstInvalid || email;
    } else if (!EMAIL_RE.test(emailVal)) {
        email.classList.add('is-error'); email.classList.remove('is-valid');
        emailStatus.textContent = 'Please enter a valid email address.';
        emailStatus.style.color = '#ef4444';
        valid = false; firstInvalid = firstInvalid || email;
    }

    // Password
    if (password.value === '') {
        password.classList.add('is-error');
        passError.textContent = 'Password is required.';
        passError.style.display = 'flex';
        valid = false; firstInvalid = firstInvalid || password;
    } else {
        passError.style.display = 'none';
        password.classList.remove('is-error');
    }

    if (!valid) {
        e.preventDefault();
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    // Valid → loading state, allow submit
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;
});

// Clear password error as the user types
document.getElementById('passwordInput').addEventListener('input', function() {
    const passError = document.getElementById('passwordError');
    this.classList.remove('is-error');
    passError.style.display = 'none';
});

/* ─────────────────────────────────────────────
   GSAP PAGE ENTRANCE ANIMATION
───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    tl.to('body', { opacity: 1, duration: 0.01 })
      .fromTo('.left-panel',
        { x: -60, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.9 }, 0)
      .to('.gsap-lp-center',
        { opacity: 1, duration: 0.8 }, 0.5)
      .fromTo('.gsap-lp-bottom',
        { opacity: 0, y: 30 },
        { opacity: 1, y: 0, duration: 0.8 }, 0.6)
      .fromTo('.right-panel',
        { x: 40, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.8 }, 0.2)
      .to('.gsap-form-header',
        { opacity: 1, y: 0, duration: 0.5 }, 0.7)
      .to('.gsap-alert',
        { opacity: 1, duration: 0.4 }, 0.8)
      .to('.gsap-field',
        { opacity: 1, y: 0, stagger: 0.08, duration: 0.4 }, 0.85)
      .to('.gsap-btn',
        { opacity: 1, duration: 0.4 }, 1.15)
      .to('.gsap-footer',
        { opacity: 1, stagger: 0.08, duration: 0.4 }, 1.25);

    // Button hover
    const btn = document.getElementById('submitBtn');
    btn.addEventListener('mouseenter', () => {
        if (!btn.classList.contains('loading')) gsap.to(btn, { y: -2, scale: 1.01, duration: 0.2 });
    });
    btn.addEventListener('mouseleave', () => {
        if (!btn.classList.contains('loading')) gsap.to(btn, { y: 0, scale: 1, duration: 0.2 });
    });
});

/* ─────────────────────────────────────────────
   SLIDE-OUT NAV (to Register)
───────────────────────────────────────────── */
function slideTo(anchor, e) {
    e.preventDefault();
    const url = anchor.href;
    gsap.to(['.left-panel', '.right-panel'], {
        x: 60,
        opacity: 0,
        duration: 0.4,
        ease: 'power3.in',
        stagger: 0.05,
        onComplete: function() { window.location.href = url; }
    });
}
</script>
</body>
</html>