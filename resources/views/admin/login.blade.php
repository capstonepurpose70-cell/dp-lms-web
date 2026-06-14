<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <style>
        :root {
            --primary: #16a34a;
            --primary-hover: #15803d;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            /* Dark overlay to make text readable over any image */
            background-color: rgba(2, 6, 23, 0.7); 
        }

        /* ── Background Image Layer (Blurred Only) ── */
        .bg-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            
            /* YOUR BACKGROUND IMAGE HERE */
            background-image: url('{{ asset("images/background.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            /* This blurs ONLY the background */
            filter: blur(5px);
            transform: scale(1.1); /* Scale up to hide blurry edges */
            animation: kenburns 22s ease-in-out infinite alternate;
        }
        @keyframes kenburns {
            from { transform: scale(1.1); }
            to   { transform: scale(1.2) translateY(-8px); }
        }

        /* ── Content Wrapper (Sharp & Clear) ── */
        .wrapper {
            position: relative;
            z-index: 10; /* Sits on top of the blurred background */
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Logo Section ── */
        .logo-wrap {
            text-align: center;
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(-20px);
        }

.logo-box {
    width: 88px;
    height: 88px;
    border-radius: 22px;
    background: #f0fdf4;
    margin: 0 auto 14px;
    box-shadow:
        0 0 0 3px rgba(22, 163, 74, 0.12),
        0 6px 16px rgba(0, 0, 0, 0.10);
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-logo-section {
    text-align: center;
    margin-bottom: 22px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}


        .logo-box img {
            width: 70%;
            height: 70%;
            object-fit: contain;
        }

.logo-title {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #166534;
    margin-bottom: 3px;
}

.logo-sub {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    text-shadow: none;
}

        /* ── Card with Glass Effect ── */
/* BAGO */
.card {
    width: 100%;
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 32px;
    box-shadow:
        0 2px 4px rgba(0, 0, 0, 0.04),
        0 20px 48px rgba(0, 0, 0, 0.28),
        0 0 0 1px rgba(255, 255, 255, 0.6);
    opacity: 0;
    transform: translateY(30px);
}

.card-heading {
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 22px;
    color: #111827;
    letter-spacing: -0.01em;
}
        /* ── Form Elements ── */
        .form-group { margin-bottom: 20px; }

        
.field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 7px;
    margin-left: 2px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}


.field-input {
    width: 100%;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    color: #111827;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 14px;
    font-weight: 500;
    font-family: inherit;
    transition:
        border-color 180ms cubic-bezier(0.23, 1, 0.32, 1),
        box-shadow   180ms cubic-bezier(0.23, 1, 0.32, 1),
        background   180ms cubic-bezier(0.23, 1, 0.32, 1);
}


.field-input::placeholder {
    color: #c9cdd6;
    font-weight: 400;
}

.field-input:focus {
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
    outline: none;
}

.field-input.error {
    border-color: #fca5a5;
    background: #fff5f5;
}

.field-error {
    font-size: 11px;
    color: #ef4444;
    margin-top: 5px;
    margin-left: 2px;
    font-weight: 500;
}
        /* Password Wrap */
        .pw-wrap { position: relative; }
        .pw-wrap .field-input { padding-right: 45px; }

        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 4px;
            display: flex;
            transition: color 0.2s;
        }

        /* PALITAN */

        .pw-toggle svg { width: 18px; height: 18px; }
        .icon-eye-off { display: none; }

        /* ── Submit Button ── */
.btn-submit {
    width: 100%;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white;
    border: none;
    border-radius: 11px;
    padding: 13px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 4px;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.02em;
    transition:
        background  200ms cubic-bezier(0.23, 1, 0.32, 1),
        box-shadow  200ms cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.10),
        0 4px 12px rgba(22, 163, 74, 0.28);
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (hover: hover) and (pointer: fine) {
    .btn-submit:hover {
        background: linear-gradient(135deg, #22c55e, #16a34a);
    }
    .pw-toggle:hover {
        color: #3b82f6;
    }
}
.btn-submit:active {
    transform: scale(0.98);
    box-shadow:
        0 1px 2px rgba(0, 0, 0, 0.08),
        0 2px 6px rgba(22, 163, 74, 0.18);
}

        /* Spinner */
        .btn-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin { to { transform: translate(-50%, -50%) rotate(360deg); } }

        .btn-submit.loading .btn-text { visibility: hidden; opacity: 0; }
        .btn-submit.loading .btn-spinner { display: block; }

        /* ── Divider & Footer ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            opacity: 0;
                transform: translateY(8px); /* ← DAGDAG */
        }

  .divider-line {
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.divider-text {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
}

.restricted-note {
    text-align: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.65);
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    opacity: 0;
    transform: translateY(8px); /* ← may ; na */
}
        .copyright {
            text-align: center;
            font-size: 11px;
            color: #cbd5e1;
            margin-top: 12px;
            opacity: 0;
                transform: translateY(8px);
        }

        /* Alert */
.alert-error {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #b91c1c;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    opacity: 0;
    transform: translateY(-10px);
}


/* ── Checkmark draw animation ── */

#btn-check path {
    stroke-dasharray: 28;
    stroke-dashoffset: 28;
}

@keyframes draw-check {
    from { stroke-dashoffset: 28; opacity: 0; }
    to   { stroke-dashoffset: 0;  opacity: 1; }
}
.btn-submit.success {
    background: linear-gradient(135deg, #16a34a, #15803d);
    pointer-events: none;
}

.btn-submit.success #btn-check {
    display: block !important;
    animation: draw-check 350ms cubic-bezier(0.23, 1, 0.32, 1) forwards;
}

.btn-submit.loading #btn-icon,
.btn-submit.loading #btn-check {
    opacity: 0 !important;
}

.lockout-box {
    display: none;
    text-align: center;
    padding: 24px 16px;
}

.lockout-icon {
    width: 48px;
    height: 48px;
    background: #fef2f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    border: 1.5px solid #fecaca;
}

.lockout-icon svg {
    width: 22px;
    height: 22px;
    color: #ef4444;
    stroke: #ef4444;
}

.lockout-title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 6px;
}

.lockout-sub {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 20px;
    line-height: 1.6;
}

.countdown-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 20px;
}

.countdown-label {
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.countdown-timer {
    font-size: 22px;
    font-weight: 800;
    color: #ef4444;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
    min-width: 52px;
    text-align: center;
}

.countdown-progress {
    width: 100%;
    height: 3px;
    background: #f3f4f6;
    border-radius: 99px;
    margin-top: 16px;
    overflow: hidden;
}

.countdown-bar {
    height: 100%;
    background: linear-gradient(90deg, #ef4444, #f97316);
    border-radius: 99px;
    width: 100%;
    transition: width 1s linear;
}


    </style>
</head>
<body>

    <!-- Separate Layer for Background (Blurred) -->
    <div class="bg-layer"></div>

<div class="wrapper">

    {{-- Logo --}}


    {{-- Card --}}
<div class="card gsap-card">

    {{-- Logo (moved inside) --}}
    <div class="card-logo-section">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="DP-LMS Logo">
        </div>
        <p class="logo-title">DIGITAL PLATFORM LMS</p>
        <p class="logo-sub">Sto. Domingo National High School</p>
    </div>

    <h2 class="card-heading">Administrator Access</h2>

    
 @if(session('error'))
    <div class="alert-error gsap-alert" id="alert-box">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span id="alert-msg">{{ session('error') }}</span>
    </div>
@endif

        <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm">
            @csrf

            {{-- Email --}}
            <div class="form-group gsap-form-item">
                <label class="field-label">Admin Email</label>
                <input type="email" name="email"
                    value="{{ old('email') }}"
                    placeholder="name@school.edu.ph"
                    required
                    class="field-input {{ $errors->has('email') ? 'error' : '' }}">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group gsap-form-item">
                <label class="field-label">Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password"
                        id="admin-pw"
                        placeholder="••••••••"
                        required
                        class="field-input {{ $errors->has('password') ? 'error' : '' }}">
                    <button type="button" class="pw-toggle gsap-icon-toggle"
                        onclick="toggleAdminPw()" tabindex="-1">
                        <svg class="icon-eye" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="icon-eye-off" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

<button type="submit" class="btn-submit gsap-btn-submit">
    <!-- Lock icon (default state) -->
    <svg id="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;transition:opacity 200ms ease">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>

    <!-- Checkmark (success state, hidden initially) -->
    <svg id="btn-check" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2.5"
         style="width:20px;height:20px;flex-shrink:0;display:none;opacity:0">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>

    <span id="btn-text" style="margin-left:6px">Sign In</span>
    <div class="btn-spinner"></div>
</button>
        </form>

        {{-- Lockout Box --}}
<div class="lockout-box" id="lockout-box">
    <div class="lockout-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 
                  11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 
                  2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 
                  2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
        </svg>
    </div>
    <p class="lockout-title">Account Temporarily Locked</p>
    <p class="lockout-sub">Too many failed attempts.<br>Please wait before trying again.</p>
    <div class="countdown-wrap">
        <span class="countdown-label">Try again in</span>
        <span class="countdown-timer" id="countdown-display">30:00</span>
    </div>
    <div class="countdown-progress">
        <div class="countdown-bar" id="countdown-bar"></div>
    </div>
</div>

        <div class="divider gsap-divider">
            <div class="divider-line"></div>
            <span class="divider-text">Secure Gateway</span>
            <div class="divider-line"></div>
        </div>
    </div>

    <p class="restricted-note gsap-footer">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        Restricted to authorized personnel only
    </p>

    <p class="copyright gsap-footer">
        &copy; {{ date('Y') }} Sto. Domingo NHS
    </p>
</div>


<script>
function toggleAdminPw() {
    const field  = document.getElementById('admin-pw');
    const eyeOn  = document.querySelector('.icon-eye');
    const eyeOff = document.querySelector('.icon-eye-off');
    if (field.type === 'password') {
        field.type = 'text';
        eyeOn.style.display  = 'none';
        eyeOff.style.display = 'block';
    } else {
        field.type = 'password';
        eyeOn.style.display  = 'block';
        eyeOff.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", () => {

    // ── Entrance sequence ──────────────────────────────
    // initial states for a richer pop-in
    gsap.set(".gsap-card", { scale: 0.94 });
    gsap.set(".logo-box",  { scale: 0.45, rotation: -14, transformOrigin: "50% 50%" });

    const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

    tl.to("body",            { opacity: 1, duration: 0.3 })
      .to(".gsap-card",      { opacity: 1, y: 0, scale: 1, duration: 0.55, ease: "power4.out" }, "-=0.1")
      .to(".logo-box",       { scale: 1, rotation: 0, duration: 0.8, ease: "back.out(1.9)" }, "-=0.4")
      .to(".gsap-alert",     { opacity: 1, y: 0, duration: 0.3  }, "-=0.45")
      .to(".gsap-form-item", { opacity: 1, y: 0, stagger: 0.09, duration: 0.4 }, "-=0.3")
      .to(".gsap-btn-submit",{ opacity: 1, y: 0, duration: 0.4 }, "-=0.15")
      .to(".gsap-divider",   { opacity: 1, y: 0, duration: 0.3  }, "-=0.2")
      .to(".gsap-footer",    { opacity: 1, y: 0, stagger: 0.06, duration: 0.3  }, "-=0.15")
      // gentle continuous float on the logo
      .to(".logo-box",       { y: -6, duration: 2.2, ease: "sine.inOut", yoyo: true, repeat: -1 }, "+=0.05");

// ── Lockout detection ──────────────────────────────
const alertMsg  = document.getElementById('alert-msg');
const isLocked  = alertMsg && alertMsg.textContent.toLowerCase().includes('too many attempts');

// ── DAGDAG: check kung naka-lockout pa rin mula sa nakaraang session ──
const storedEnd = sessionStorage.getItem('lockout_end');
const stillLocked = storedEnd && (parseInt(storedEnd) - Date.now()) > 0;

if (isLocked || stillLocked) {
    activateLockout();
}
// PALITAN ITONG BUONG FUNCTION
function activateLockout() {
    const formItems  = document.querySelectorAll('.gsap-form-item');
    const submitBtn  = document.querySelector('.gsap-btn-submit');
    const lockoutBox = document.getElementById('lockout-box');
    const alertBox   = document.getElementById('alert-box');
    const TOTAL_SECS = 45; 

    // ── Check kung reload mid-lockout ──
    const storedEnd = sessionStorage.getItem('lockout_end');
    const isReload  = storedEnd && (parseInt(storedEnd) - Date.now()) > 0;

    function showLockout() {
        formItems.forEach(el => el.style.display = 'none');
        submitBtn.style.display = 'none';
        if (alertBox) alertBox.style.display = 'none';

        lockoutBox.style.display = 'block';
        gsap.fromTo(lockoutBox,
            { opacity: 0, y: 12 },
            { opacity: 1, y: 0, duration: 0.4, ease: 'power3.out' }
        );

        startCountdown(TOTAL_SECS);
    }

    if (isReload) {
        // Skip fade-out animation — show lockout agad
        showLockout();
    } else {
        gsap.to([...formItems, submitBtn], {
            opacity: 0, y: -8,
            duration: 0.3, ease: 'power2.in', stagger: 0.04,
            onComplete: showLockout
        });
    }
}

function startCountdown(totalSecs) {
    const display = document.getElementById('countdown-display');
    const bar     = document.getElementById('countdown-bar');
    let remaining = totalSecs;

    const stored = sessionStorage.getItem('lockout_end');
    // ── DAGDAG: i-store ang total para sa progress bar ──
    const storedTotal = sessionStorage.getItem('lockout_total');

    if (stored) {
        const diff = Math.floor((parseInt(stored) - Date.now()) / 1000);
        remaining = diff > 0 ? diff : 0;
    } else {
        sessionStorage.setItem('lockout_end',   Date.now() + totalSecs * 1000);
        sessionStorage.setItem('lockout_total', totalSecs); // ← DAGDAG
    }

    // ── Use stored total as denominator ──
    const total = storedTotal ? parseInt(storedTotal) : totalSecs; // ← DAGDAG

    function tick() {
        if (remaining <= 0) {
            sessionStorage.removeItem('lockout_end');
            sessionStorage.removeItem('lockout_total'); // ← DAGDAG
            unlockForm();
            return;
        }

        const mins = String(Math.floor(remaining / 60)).padStart(2, '0');
        const secs = String(remaining % 60).padStart(2, '0');
        display.textContent = `${mins}:${secs}`;

        const pct = (remaining / total) * 100; // ← BAGO: total, hindi totalSecs
        bar.style.width = pct + '%';

        remaining--;
        setTimeout(tick, 1000);
    }

    tick();
}

    function unlockForm() {
        const formItems  = document.querySelectorAll('.gsap-form-item');
        const submitBtn  = document.querySelector('.gsap-btn-submit');
        const lockoutBox = document.getElementById('lockout-box');

        // ── Hide lockout box ──
        gsap.to(lockoutBox, {
            opacity: 0, y: -8, duration: 0.3, ease: 'power2.in',
            onComplete: () => {
                lockoutBox.style.display = 'none';

                // ── Show form again ──
                formItems.forEach(el => {
                    el.style.display = '';
                    el.style.opacity = '0';
                });
                submitBtn.style.display = '';
                submitBtn.style.opacity = '0';

                gsap.to([...formItems, submitBtn], {
                    opacity: 1, y: 0,
                    duration: 0.35,
                    stagger: 0.06,
                    ease: 'power3.out'
                });
            }
        });
    }

    // ── Button interactions ────────────────────────────
    const btn       = document.querySelector(".gsap-btn-submit");
    const isPointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    if (isPointer) {
        btn.addEventListener("mouseenter", () => {
            if (!btn.classList.contains('loading') && !btn.classList.contains('success'))
                gsap.to(btn, { y: -2, duration: 0.2 });
        });
        btn.addEventListener("mouseleave", () => {
            if (!btn.classList.contains('loading') && !btn.classList.contains('success'))
                gsap.to(btn, { y: 0, duration: 0.2 });
        });
    }

    btn.addEventListener("mousedown",  () => gsap.to(btn, { scale: 0.97, duration: 0.1  }));
    btn.addEventListener("mouseup",    () => gsap.to(btn, { scale: 1,    duration: 0.12 }));
    btn.addEventListener("mouseleave", () => gsap.to(btn, { scale: 1,    duration: 0.12 }));

    // ── Form submit ────────────────────────────────────
    const form     = document.getElementById('adminLoginForm');
    const btnIcon  = document.getElementById('btn-icon');
    const btnCheck = document.getElementById('btn-check');
    const btnText  = document.getElementById('btn-text');

    form.addEventListener('submit', function (e) {
        if (btn.classList.contains('loading') || btn.classList.contains('success')) {
            e.preventDefault();
            return;
        }
        btn.classList.add('loading');
        btn.disabled = true;
        btnIcon.style.opacity = '0';
        btnText.textContent   = 'Signing in...';
    });

    // ── Success animation ──────────────────────────────
    window.triggerSuccess = function () {
        btn.classList.remove('loading');
        btn.classList.add('success');
        btn.disabled = true;

        const spinner = btn.querySelector('.btn-spinner');
        if (spinner) spinner.style.display = 'none';

        btnIcon.style.opacity = '1';
        btnIcon.style.display = 'none';
        btnCheck.style.display = 'block';
        btnText.textContent    = 'Access Granted';

        gsap.to(btnCheck, { opacity: 1, duration: 0.2 });

        gsap.delayedCall(0.65, () => {
            gsap.to('.gsap-card', {
                y: -24, opacity: 0, scale: 0.97,
                duration: 0.4, ease: 'power2.in'
            });
            gsap.to(['.gsap-footer', '.copyright'], {
                opacity: 0, duration: 0.25, ease: 'power2.in'
            });
        });
    };
});
</script>

    @if(session('login_success'))
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => window.triggerSuccess(), 800);
        });
    </script>
    @endif

</body>
</html>