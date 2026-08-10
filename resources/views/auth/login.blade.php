<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <title>Login — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts (same family as Register, for a consistent brand) -->
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
            --surface:   #f4f7f9;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            display: flex;                 /* form panel is FIRST → sits on the LEFT */
            background: var(--surface);
            overflow-x: hidden;
            opacity: 0;
        }

        /* ══════════════════════════════════════
           FORM PANEL (LEFT)  — distinct card look
        ══════════════════════════════════════ */
        .form-panel {
            flex: 1;
            height: 100vh;
            overflow-y: auto;
            background: linear-gradient(165deg, #e6f4ec 0%, #eef3f6 55%, #eaf2ee 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* top clearance so the fixed navbar never covers the card */
            padding: 96px 40px 40px 40px;
        }

        .login-card {
            width: 100%;
            max-width: 468px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.08);
            padding: 42px 44px 36px;
            position: relative;
            overflow: hidden;
        }

        /* Slim brand accent bar on top of the card */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--green-500), var(--green-700));
        }

        .form-header {
            margin-bottom: 24px;
            opacity: 0;
            transform: translateY(12px);
        }
        .form-header .eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--green-600);
            margin-bottom: 8px;
        }
        .form-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 33px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
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
            margin-bottom: 16px;
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

        /* Remember + Forgot row */
        .login-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: -2px 0 6px;
            opacity: 0;
        }
        .remember {
            display: flex; align-items: center; gap: 8px;
            cursor: pointer;
            margin: 0;
            font-size: 12.5px; font-weight: 500;
            color: var(--text-mid);
            text-transform: none; letter-spacing: 0;
        }
        .remember input {
            width: 15px; height: 15px;
            accent-color: var(--green-500);
            cursor: pointer;
        }
        .login-row a {
            font-size: 12.5px; color: var(--text-muted);
            text-decoration: none; font-weight: 500;
            transition: color 0.2s;
            white-space: nowrap;
        }
        .login-row a:hover { color: var(--green-600); text-decoration: underline; }

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
            margin-top: 18px;
            opacity: 0;
        }

        /* ══════════════════════════════════════
           IMAGE PANEL (RIGHT)
        ══════════════════════════════════════ */
        .image-panel {
            position: relative;
            width: 46%;
            height: 100vh;
            flex-shrink: 0;
            overflow: hidden;
        }
        @keyframes bgPan {
            0%   { transform: scale(1.08) translate(0px, 0px); }
            50%  { transform: scale(1.1) translate(15px, -8px); }
            100% { transform: scale(1.08) translate(-8px, 10px); }
        }
        /* gradient flipped (200deg) so it mirrors register's 160deg */
        .image-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                200deg,
                rgba(0,0,0,0.18) 0%,
                rgba(0,0,0,0.45) 50%,
                rgba(6,78,59,0.88) 100%
            );
            z-index: 1;
        }
        .image-panel::after {
            content: '';
            position: absolute;
            inset: -20px;
            background-image: url('{{ asset("images/bg.jpg") }}');
            background-size: cover;
            background-position: center;
            z-index: 0;
            animation: bgPan 22s ease-in-out infinite alternate;
            will-change: transform;
        }

        #particles-canvas {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        .ip-center {
            position: absolute;
            top: 50%;
            left: 0; right: 0;
            transform: translateY(-50%);
            z-index: 3;
            padding: 0 44px;
            text-align: center;
            opacity: 0;
        }
        .ip-logo {
            width: 140px;
            height: 140px;
            object-fit: contain;
            background: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        }
        .ip-verse {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            font-weight: 400;
            font-style: italic;
            color: #ffffff;
            line-height: 1.7;
            max-width: 360px;
            margin: 0 auto 10px;
        }
        .ip-verse-ref {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ffffff;
        }
        .ip-bottom {
            position: absolute;
            bottom: 48px;
            left: 0; right: 0;
            z-index: 3;
            padding: 0 44px;
            text-align: right;               /* mirrored: text aligned right */
            opacity: 0;
        }
        .ip-welcome {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--green-500);
            margin-bottom: 10px;
        }
        .ip-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 50px;
            font-weight: 700;
            line-height: 1.05;
            color: #fff;
            text-shadow: 0 4px 32px rgba(0,0,0,0.6);
            margin-bottom: 12px;
        }
        .ip-subtitle {
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: rgba(255,255,255,0.65);
            line-height: 1.6;
        }
        .ip-bar {
            width: 40px;
            height: 3px;
            background: var(--green-500);
            border-radius: 2px;
            margin: 0 0 18px auto;           /* mirrored: bar on the right */
        }

        /* Responsive — image hidden, form card full width */
        @media (max-width: 960px) {
            .image-panel { display: none; }
            .form-panel { width: 100%; padding: 84px 20px 28px; }
            .login-card {
                border: none;
                box-shadow: none;
                background: transparent;
                padding: 0;
                max-width: 468px;
            }
            .login-card::before { display: none; }
        }

        /* ══ Extra responsive polish (mobile) ══ */
        @media (max-width: 960px) {
            /* tamang taas sa mobile browsers (address-bar safe) */
            .form-panel { height: auto; min-height: 100vh; min-height: 100svh; }
        }
        @media (max-width: 480px) {
            .form-panel { padding: 78px 16px 24px; }
            .form-header h2 { font-size: 26px; }
            .form-header .eyebrow { font-size: 10px; letter-spacing: 2px; }
            /* 16px = hindi nagzu-zoom ang iOS pag nag-focus; 46px = touch-friendly */
            input[type="text"], input[type="email"], input[type="password"] {
                font-size: 16px;
                min-height: 46px;
            }
            .btn-submit { min-height: 48px; font-size: 15px; }
        }
        @media (max-width: 360px) {
            .form-panel { padding: 74px 12px 20px; }
            .form-header h2 { font-size: 23px; }
        }
        /* maiksing screen (landscape phone): huwag i-center para di maputol ang taas */
        @media (max-height: 640px) and (max-width: 960px) {
            .form-panel { justify-content: flex-start; }
        }

        @media (prefers-reduced-motion: reduce) {
            .image-panel::after { animation: none; }
        }
    </style>

    <noscript>
        <style>
            body { opacity: 1 !important; }
            .ip-center, .ip-bottom, .form-header, .form-group,
            .forgot-wrap, .btn-submit, .card-footer, .copyright,
            .server-alert { opacity: 1 !important; transform: none !important; }
        </style>
    </noscript>
</head>
<body>
@include('partials.auth-navbar')

    {{-- ── FORM PANEL (LEFT) ── --}}
    <div class="form-panel">
        <div class="login-card gsap-card">

            <div class="form-header gsap-form-header">
                <p class="eyebrow">Sto. Domingo NHS</p>
                <h2>Welcome back</h2>
                <p>Sign in to continue to your portal.</p>
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

                {{-- Email or LRN --}}
                <div class="form-group gsap-field">
                    <label for="emailInput">Email Address or LRN</label>
                    <input type="text" name="email" id="emailInput"
                        value="{{ old('email') }}"
                        placeholder="you@gmail.com or 12-digit LRN"
                        autocomplete="username"
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

                {{-- Remember me + Forgot --}}
                <div class="login-row gsap-field">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
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
                &copy; {{ date('Y') }} Sto. Domingo National High School
            </p>
        </div>
    </div>

    {{-- ── IMAGE PANEL (RIGHT) ── --}}
    <div class="image-panel">
        <canvas id="particles-canvas"></canvas>

        <div class="ip-center gsap-ip-center">
            <img src="{{ asset('images/logo.png') }}" alt="DP-LMS Logo" class="ip-logo" style="margin: 0 auto 20px; display: block;">
            <p class="ip-verse">
                "The fear of the LORD is the beginning of knowledge."
            </p>
            <p class="ip-verse-ref">— Proverbs 1:7</p>
        </div>

        <div class="ip-bottom gsap-ip-bottom">
            <div class="ip-bar"></div>
            <p class="ip-welcome">Digital Portal</p>
            <h1 class="ip-title">DP-LMS</h1>
            <p class="ip-subtitle">
                Sto. Domingo National High School<br>
                Learning Management System
            </p>
        </div>
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
   EMAIL / LRN LIVE VALIDATION (UX only)
───────────────────────────────────────────── */
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const LRN_RE   = /^\d{12}$/;
/* Puro digits = sinusubukang mag-LRN; ipakita ang tulong para sa 12 digits. */
const DIGITS_ONLY_RE = /^\d+$/;

document.getElementById('emailInput').addEventListener('input', function() {
    const val = this.value.trim();
    const status = document.getElementById('emailStatus');
    if (!val) { status.textContent = ''; this.classList.remove('is-valid','is-error'); return; }

    if (EMAIL_RE.test(val) || LRN_RE.test(val)) {
        status.textContent = LRN_RE.test(val) ? '✓ Valid LRN format' : '✓ Valid email format';
        status.style.color = '#16a34a';
        this.classList.remove('is-error');
        this.classList.add('is-valid');
    } else {
        status.textContent = DIGITS_ONLY_RE.test(val)
            ? 'LRN must be exactly 12 digits'
            : 'Enter a valid email address or 12-digit LRN';
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

    const emailVal = email.value.trim();
    if (emailVal === '') {
        email.classList.add('is-error'); email.classList.remove('is-valid');
        emailStatus.textContent = 'Email address or LRN is required.';
        emailStatus.style.color = '#ef4444';
        valid = false; firstInvalid = firstInvalid || email;
    } else if (!EMAIL_RE.test(emailVal) && !LRN_RE.test(emailVal)) {
        email.classList.add('is-error'); email.classList.remove('is-valid');
        emailStatus.textContent = DIGITS_ONLY_RE.test(emailVal)
            ? 'LRN must be exactly 12 digits.'
            : 'Please enter a valid email address or 12-digit LRN.';
        emailStatus.style.color = '#ef4444';
        valid = false; firstInvalid = firstInvalid || email;
    }

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

    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;
});

document.getElementById('passwordInput').addEventListener('input', function() {
    const passError = document.getElementById('passwordError');
    this.classList.remove('is-error');
    passError.style.display = 'none';
});

/* ─────────────────────────────────────────────
   GSAP PAGE ENTRANCE (mirrored: form ← left, image → right)
───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    tl.to('body', { opacity: 1, duration: 0.01 })
      .fromTo('.image-panel',
        { x: 60, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.9 }, 0)
      .fromTo('.form-panel',
        { x: -40, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.8 }, 0.15)
      .fromTo('.gsap-card',
        { y: 24, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.6 }, 0.35)
      .to('.gsap-ip-center',
        { opacity: 1, duration: 0.8 }, 0.5)
      .fromTo('.gsap-ip-bottom',
        { opacity: 0, y: 30 },
        { opacity: 1, y: 0, duration: 0.8 }, 0.6)
      .to('.gsap-form-header',
        { opacity: 1, y: 0, duration: 0.5 }, 0.55)
      .to('.gsap-alert',
        { opacity: 1, duration: 0.4 }, 0.65)
      .to('.gsap-field',
        { opacity: 1, y: 0, stagger: 0.08, duration: 0.4 }, 0.7)
      .to('.gsap-btn',
        { opacity: 1, duration: 0.4 }, 1.0)
      .to('.gsap-footer',
        { opacity: 1, stagger: 0.08, duration: 0.4 }, 1.1);

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
    gsap.to(['.form-panel', '.image-panel'], {
        x: -60,
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