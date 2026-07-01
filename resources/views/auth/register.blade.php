<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts -->
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
            --amber-50:  #fffbeb;
            --amber-100: #fef3c7;
            --amber-700: #92400e;
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
 
        /* gradient overlay */
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
            z-index:  1;
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

        /* Canvas for particle dots */
        #particles-canvas {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        /* Top logo (bare, no box) */
        .lp-top {
            position: absolute;
            top: 36px;
            left: 40px;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 14px;
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

        .lp-top-text {
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.85);
            line-height: 1.3;
        }

        .lp-top-text span {
            display: block;
            font-size: 10px;
            font-weight: 400;
            letter-spacing: 1.5px;
            color: #ffffff;
            margin-top: 2px;
        }

        /* Center subtitle (verse) */
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

        /* Bottom headline */
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

        /* green accent bar */
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
            /* top bumped from 36px → 92px so the fixed top navbar never covers the form */
            padding: 92px 52px 36px 52px;
        }

        /* Progress steps */
        .steps-bar {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 32px;
            opacity: 0;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .step-item.active { color: var(--green-600); }
        .step-item.done   { color: var(--text-muted); }

        .step-dot {
            width: 24px; height: 24px;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600;
            background: #fff;
            color: var(--text-muted);
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .step-item.active .step-dot {
            background: var(--green-500);
            border-color: var(--green-500);
            color: #fff;
        }

        .step-item.done .step-dot {
            background: var(--green-100);
            border-color: var(--green-500);
            color: var(--green-600);
        }

        .step-connector {
            flex: 1;
            height: 1px;
            background: var(--border);
            margin: 0 10px;
            min-width: 20px;
        }

        /* Form header */
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

        /* Alert */
        .server-alert {
            background: var(--red-50);
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 8px;
            border-left: 3px solid var(--red-500);
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form layout */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 14px;
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

        /* Inputs */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-dark);
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus, select:focus {
            border-color: var(--green-500);
            box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
        }

        input.is-error, select.is-error {
            border-color: var(--red-500);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.08);
        }

        input.is-valid {
            border-color: var(--green-500);
        }

        .error-msg {
            font-size: 11px;
            color: var(--red-500);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .input-hint {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
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

        /* Password strength */
        .pw-strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 7px;
        }

        .pw-seg {
            height: 3px;
            flex: 1;
            border-radius: 2px;
            background: var(--border);
            transition: background 0.3s;
        }

        .pw-label {
            font-size: 11px;
            margin-top: 4px;
            color: var(--text-muted);
            height: 14px;
        }

        /* Email check badge */
        .email-status {
            font-size: 11px;
            margin-top: 4px;
            height: 14px;
            transition: color 0.2s;
        }

        /* Info boxes */
        .info-box-container {
            height: 0;
            overflow: hidden;
            transition: height 0.4s cubic-bezier(0.4,0,0.2,1);
        }

        .info-box {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 12px;
            line-height: 1.6;
            opacity: 0;
            transform: translateY(-6px);
            margin-bottom: 4px;
        }

        .info-box.student {
            background: var(--green-50);
            border: 1px solid #bbf7d0;
            color: var(--green-700);
        }

        .info-box.parent {
            background: var(--amber-50);
            border: 1px solid #fde68a;
            color: var(--amber-700);
        }

        .info-box-title {
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-box ul {
            padding-left: 16px;
        }

        .info-box li { margin-bottom: 2px; }

        .child-input { margin-top: 10px; }
        .child-input label { color: #b45309; }
        .child-input input { border-color: #fcd34d; font-size: 13px; }
        .child-input input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }

        /* Agreement */
        .agree-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 18px 0 4px;
            opacity: 0;
        }

        .agree-row input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--green-500);
            margin-top: 3px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .agree-row label {
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            cursor: pointer;
        }

        /* Agreement required state (must read & check) */
        .agree-row.invalid {
            background: var(--red-50);
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px;
            margin-left: -10px;
            margin-right: -10px;
            animation: agreeShake 0.4s;
        }
        @keyframes agreeShake {
            0%, 100% { transform: translateX(0); }
            25%      { transform: translateX(-5px); }
            75%      { transform: translateX(5px); }
        }

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
            margin-top: 18px;
            opacity: 0;
        }

        .btn-submit:hover:not(.loading) { background: var(--green-600); }
        .btn-submit:active:not(.loading) { transform: scale(0.985); }

        /* Disabled look while agreement not yet accepted */
        .btn-submit.disabled { opacity: 0.55; cursor: not-allowed; }
        .btn-submit.disabled:hover { background: var(--green-500); }

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

        .btn-submit.loading .btn-text { opacity: 0; }
        .btn-submit.loading .btn-spinner { display: block; }

        /* Footer */
        .card-footer {
            text-align: center;
            margin-top: 18px;
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

        /* ══════════════════════════════════════
           ROLE CONFIRMATION MODAL
        ══════════════════════════════════════ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #fff;
            border-radius: 14px;
            padding: 32px 36px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 24px 64px rgba(0,0,0,0.18);
            transform: scale(0.92) translateY(16px);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.25s;
        }

        .modal-overlay.open .modal-box {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        .modal-icon {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: #fef9c3;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            font-size: 26px;
        }

        .modal-box h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .modal-box p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .modal-box p strong {
            color: var(--text-dark);
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        .modal-btn {
            flex: 1;
            padding: 11px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border: 1.5px solid var(--border);
            background: #fff;
            color: var(--text-mid);
            transition: all 0.2s;
        }

        .modal-btn:hover { background: var(--surface); }

        .modal-btn.confirm {
            background: var(--green-500);
            border-color: var(--green-500);
            color: #fff;
        }

        .modal-btn.confirm:hover { background: var(--green-600); }

        /* Student-as-parent warning */
        .role-warning {
            display: none;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #991b1b;
            margin-top: 6px;
            line-height: 1.6;
        }

        .role-warning.visible { display: flex; align-items: flex-start; gap: 8px; }

        /* Responsive */
        @media (max-width: 960px) {
            .left-panel { display: none; }
            /* top bumped so the fixed navbar clears the form on mobile too */
            .right-panel { width: 100%; padding: 84px 24px 28px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
        }


    </style>
</head>
<body>
@include('partials.auth-navbar')

    {{-- ── LEFT PANEL ── --}}
    <div class="left-panel">
        <canvas id="particles-canvas"></canvas>


        {{-- Center: Bible Verse --}}
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
            <p class="lp-welcome">Welcome to</p>
            <h1 class="lp-title">DP-LMS</h1>
            <p class="lp-subtitle">
                Sto. Domingo National High School<br>
                Digital Learning Management System
            </p>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="right-panel">

        {{-- Progress Steps --}}
        <div class="steps-bar gsap-steps">
            <div class="step-item active" id="step1-label">
                <div class="step-dot">1</div>
                <span>Account Info</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" id="step2-label">
                <div class="step-dot">2</div>
                <span>Role & Details</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" id="step3-label">
                <div class="step-dot">3</div>
                <span>Security</span>
            </div>
        </div>

        <div class="form-header gsap-form-header">
            <h2>Create your account</h2>
            <p>Fill in your information below to get started.</p>
        </div>

        @if(session('error'))
        <div class="server-alert">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" id="registerForm" novalidate>
            @csrf

            {{-- Row 1: Name + Contact --}}
            <div class="form-row">
                <div class="form-group gsap-field">
                    <label>Full Name</label>
                    <input type="text" name="name" id="nameInput"
                        value="{{ old('name') }}"
                        placeholder="Juan Dela Cruz"
                        autocomplete="name"
                        class="{{ $errors->has('name') ? 'is-error' : '' }}"
                        oninput="cleanName(this)"
                        required>
                    <p class="email-status" id="nameStatus"></p>
                    @error('name')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-group gsap-field">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" id="contactInput"
                        value="{{ old('contact_number') }}"
                        placeholder="09XXXXXXXXX"
                        maxlength="11"
                        inputmode="numeric"
                        autocomplete="tel"
                        class="{{ $errors->has('contact_number') ? 'is-error' : '' }}"
                        oninput="cleanPhone(this)"
                        required>
                    <p class="email-status" id="contactStatus"></p>
                    <p class="input-hint">11-digit PH mobile number (e.g., 09171234567)</p>
                    @error('contact_number')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Email --}}
            <div class="form-group gsap-field">
                <label>Email Address</label>
                <input type="email" name="email" id="emailInput"
                    value="{{ old('email') }}"
                    placeholder="you@email.com"
                    autocomplete="email"
                    class="{{ $errors->has('email') ? 'is-error' : '' }}"
                    required>
                <p class="email-status" id="emailStatus"></p>
                @error('email')<p class="error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Role --}}
            <div class="form-group gsap-field">
                <label>Registering as</label>
                <select name="role" id="roleSelect" required
                    class="{{ $errors->has('role') ? 'is-error' : '' }}"
                    onchange="handleRoleChange(this.value)">
                    <option value="">— Select your role —</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="parent"  {{ old('role') == 'parent'  ? 'selected' : '' }}>Parent / Guardian</option>
                </select>
                @error('role')<p class="error-msg">{{ $message }}</p>@enderror

                {{-- ⚠ Student-as-parent guard --}}
                <div class="role-warning" id="parentStudentWarn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" flex-shrink="0" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <span>
                        <strong>Are you sure you're a parent?</strong><br>
                        If you are a student, please select "Student" above. Registering as a parent when you are a student will result in rejection and delay of your account.
                    </span>
                </div>
            </div>

            {{-- Student Info Box --}}
            <div class="info-box-container" id="student-wrapper">
                <div class="info-box student" id="student-box">
                    <p class="info-box-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        For Students
                    </p>
                    <ul>
                        <li>Use your personal or school email address.</li>
                        <li>You will be assigned to a section by the admin.</li>
                        <li>Do not register as "Parent" if you are a student.</li>
                        <li>Your account requires admin approval before first login.</li>
                    </ul>
                </div>
            </div>

            {{-- Parent Info Box --}}
            <div class="info-box-container" id="parent-wrapper">
                <div class="info-box parent" id="parent-box">
                    <p class="info-box-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        For Parents / Guardians
                    </p>
                    <ul>
                        <li>Use your own email — not your child's.</li>
                        <li>You must be an actual parent or guardian.</li>
                        <li>Admin verifies your identity before account activation.</li>
                        <li>Students must not use this option.</li>
                    </ul>
                    <div class="child-input">
                        <label>Child's full name (enrolled student)</label>
                        <input type="text" name="child_name" value="{{ old('child_name') }}" placeholder="Enter your child's full name">
                        @error('child_name')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Passwords --}}
            <div class="form-row" style="margin-top: 6px;">
                <div class="form-group gsap-field">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="passwordInput"
                            placeholder="Create a strong password"
                            autocomplete="new-password"
                            class="{{ $errors->has('password') ? 'is-error' : '' }}"
                            required
                            oninput="updateStrength(this.value)">
                        <button type="button" class="toggle-pw" onclick="togglePw('passwordInput', this)" tabindex="-1">
                            <svg class="pw-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="pw-hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    {{-- Strength meter --}}
                    <div class="pw-strength-bar">
                        <div class="pw-seg" id="seg1"></div>
                        <div class="pw-seg" id="seg2"></div>
                        <div class="pw-seg" id="seg3"></div>
                        <div class="pw-seg" id="seg4"></div>
                    </div>
                    <p class="pw-label" id="pwLabel"></p>
                    @error('password')<p class="error-msg">{{ $message }}</p>@enderror
                </div>

                <div class="form-group gsap-field">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmation" id="confirmInput"
                            placeholder="Re-enter your password"
                            autocomplete="new-password"
                            required
                            oninput="checkMatch()">
                        <button type="button" class="toggle-pw" onclick="togglePw('confirmInput', this)" tabindex="-1">
                            <svg class="pw-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="pw-hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="pw-label" id="matchLabel"></p>
                </div>
            </div>

            {{-- Agreement --}}
            <div class="agree-row gsap-agree" id="agreeRow">
                <input type="checkbox" name="agree" id="agreeCheck" required>
                <label for="agreeCheck">I confirm that all information I provided is accurate and truthful. I understand that false registrations will be reviewed and rejected by the administrator.</label>
            </div>
            <p class="error-msg" id="agreeError" style="display:none; margin-top:6px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Please read and check the box to confirm before you can register.
            </p>

            <button type="submit" class="btn-submit gsap-btn disabled" id="submitBtn">
                <span class="btn-text">Submit Registration</span>
                <div class="btn-spinner"></div>
            </button>
        </form>

        <div class="card-footer gsap-footer">
            Already have an account? <a href="{{ route('login') }}" onclick="slideTo(this, event)">Sign in here</a>
        </div>

        <p class="copyright gsap-footer">
            &copy; {{ date('Y') }} Sto. Domingo National High School · All rights reserved.
        </p>
    </div>

    {{-- ── ROLE CONFIRMATION MODAL ── --}}
    <div class="modal-overlay" id="roleModal">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <h3 id="modalTitle">Confirm your role</h3>
            <p id="modalBody">Please confirm that you are registering with the correct role.</p>
            <div class="modal-actions">
                <button class="modal-btn" id="modalCancel" onclick="closeModal()">Go back & change</button>
                <button class="modal-btn confirm" id="modalConfirm" onclick="confirmAndSubmit()">Yes, I am a Parent</button>
            </div>
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
   PASSWORD STRENGTH
───────────────────────────────────────────── */
function updateStrength(val) {
    let score = 0;
    if (val.length >= 8)               score++;
    if (/[A-Z]/.test(val))             score++;
    if (/[0-9]/.test(val))             score++;
    if (/[^A-Za-z0-9]/.test(val))     score++;

    const colors = ['#ef4444','#f97316','#eab308','#16a34a'];
    const labels = ['Weak','Fair','Good','Strong'];
    const segs   = [
        document.getElementById('seg1'),
        document.getElementById('seg2'),
        document.getElementById('seg3'),
        document.getElementById('seg4'),
    ];
    const label  = document.getElementById('pwLabel');

    segs.forEach((s, i) => {
        s.style.background = i < score ? colors[score - 1] : 'var(--border)';
    });

    if (val.length === 0) {
        label.textContent = '';
        label.style.color = '';
    } else {
        label.textContent = labels[score - 1] || 'Too short';
        label.style.color = score > 0 ? colors[score - 1] : '#ef4444';
    }

    checkMatch();
}

/* ─────────────────────────────────────────────
   PASSWORD MATCH
───────────────────────────────────────────── */
function checkMatch() {
    const pw  = document.getElementById('passwordInput').value;
    const cpw = document.getElementById('confirmInput').value;
    const lbl = document.getElementById('matchLabel');
    const inp = document.getElementById('confirmInput');
    if (!cpw) { lbl.textContent = ''; inp.classList.remove('is-error','is-valid'); return; }
    if (pw === cpw) {
        lbl.textContent = '✓ Passwords match';
        lbl.style.color = '#16a34a';
        inp.classList.remove('is-error');
        inp.classList.add('is-valid');
    } else {
        lbl.textContent = 'Passwords do not match';
        lbl.style.color = '#ef4444';
        inp.classList.remove('is-valid');
        inp.classList.add('is-error');
    }
}

/* ─────────────────────────────────────────────
   EMAIL LIVE VALIDATION
───────────────────────────────────────────── */
document.getElementById('emailInput').addEventListener('input', function() {
    const val = this.value;
    const status = document.getElementById('emailStatus');
    if (!val) { status.textContent = ''; return; }
    const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    if (valid) {
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
   FULL NAME — letters only, must be a full name
───────────────────────────────────────────── */
function cleanName(el) {
    const before = el.value;
    // Allow letters (any language), spaces, hyphen, apostrophe, period. Strip the rest (numbers/symbols).
    el.value = el.value.replace(/[^\p{L}\s.'-]/gu, '');
    const status = document.getElementById('nameStatus');
    const v = el.value.trim();
    const words = v.split(/\s+/).filter(w => w.length > 0);
    const fullName = words.length >= 2 &&
        words.every(w => w.replace(/[.'-]/g, '').length >= 2);

    if (!v) {
        status.textContent = '';
        el.classList.remove('is-valid', 'is-error');
    } else if (before !== el.value) {
        status.textContent = 'Numbers and symbols are not allowed in a name';
        status.style.color = '#ef4444';
        el.classList.add('is-error'); el.classList.remove('is-valid');
    } else if (!fullName) {
        status.textContent = 'Please enter your full name (first and last)';
        status.style.color = '#ef4444';
        el.classList.add('is-error'); el.classList.remove('is-valid');
    } else {
        status.textContent = '✓ Looks good';
        status.style.color = '#16a34a';
        el.classList.remove('is-error'); el.classList.add('is-valid');
    }
}
function nameIsValid() {
    const v = document.getElementById('nameInput').value.trim();
    const words = v.split(/\s+/).filter(w => w.length > 0);
    return /^[\p{L}\s.'-]+$/u.test(v) &&
        words.length >= 2 &&
        words.every(w => w.replace(/[.'-]/g, '').length >= 2);
}

/* ─────────────────────────────────────────────
   CONTACT NUMBER — digits only, PH mobile 09XXXXXXXXX
───────────────────────────────────────────── */
function cleanPhone(el) {
    const before = el.value;
    el.value = el.value.replace(/\D/g, '').slice(0, 11);   // digits only, max 11
    const status = document.getElementById('contactStatus');
    const v = el.value;
    const valid = /^09\d{9}$/.test(v);

    if (!v) {
        status.textContent = '';
        el.classList.remove('is-valid', 'is-error');
    } else if (/\D/.test(before)) {
        status.textContent = 'Letters are not allowed — numbers only';
        status.style.color = '#ef4444';
        el.classList.add('is-error'); el.classList.remove('is-valid');
    } else if (!valid) {
        status.textContent = 'Must be 11 digits starting with 09';
        status.style.color = '#ef4444';
        el.classList.add('is-error'); el.classList.remove('is-valid');
    } else {
        status.textContent = '✓ Valid mobile number';
        status.style.color = '#16a34a';
        el.classList.remove('is-error'); el.classList.add('is-valid');
    }
}
function phoneIsValid() {
    return /^09\d{9}$/.test(document.getElementById('contactInput').value);
}

/* ─────────────────────────────────────────────
   ROLE CHANGE HANDLER
───────────────────────────────────────────── */
function handleRoleChange(role) {
    const sWrap = document.getElementById('student-wrapper');
    const pWrap = document.getElementById('parent-wrapper');
    const warn  = document.getElementById('parentStudentWarn');
    const step2 = document.getElementById('step2-label');

    // Mark step 2 active/done
    document.getElementById('step1-label').classList.add('done');
    document.getElementById('step1-label').classList.remove('active');
    step2.classList.add('active');

    // Collapse both
    sWrap.style.height = '0px';
    pWrap.style.height = '0px';
    gsap.set(['#student-box','#parent-box'], { opacity: 0, y: -6 });
    warn.classList.remove('visible');

    if (role === 'student') {
        expandBox(sWrap, '#student-box');
    } else if (role === 'parent') {
        expandBox(pWrap, '#parent-box');
        // Show warning after short delay
        setTimeout(() => { warn.classList.add('visible'); }, 400);
    }
}

function expandBox(wrapper, boxSel) {
    const box = wrapper.querySelector('.info-box');
    const h   = box.scrollHeight + 16;
    gsap.to(wrapper, { height: h + 'px', duration: 0.4, ease: 'power3.out' });
    gsap.fromTo(boxSel,
        { opacity: 0, y: -6 },
        { opacity: 1, y: 0, duration: 0.3, delay: 0.15 }
    );
}

/* ─────────────────────────────────────────────
   PROGRESS STEPS — watch password
───────────────────────────────────────────── */
document.getElementById('passwordInput').addEventListener('input', function() {
    if (this.value.length >= 8) {
        document.getElementById('step3-label').classList.add('active');
    }
});

/* ─────────────────────────────────────────────
   AGREEMENT REQUIRED (must read & check)
───────────────────────────────────────────── */
function showAgreeError() {
    const row = document.getElementById('agreeRow');
    const err = document.getElementById('agreeError');
    err.style.display = 'flex';
    row.classList.remove('invalid');
    void row.offsetWidth;            // restart the shake animation
    row.classList.add('invalid');
    document.getElementById('agreeCheck').focus();
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideAgreeError() {
    document.getElementById('agreeRow').classList.remove('invalid');
    document.getElementById('agreeError').style.display = 'none';
}

(function() {
    const agree = document.getElementById('agreeCheck');
    const btn   = document.getElementById('submitBtn');
    function syncBtn() { btn.classList.toggle('disabled', !agree.checked); }
    agree.addEventListener('change', function() {
        syncBtn();
        if (agree.checked) hideAgreeError();
    });
    syncBtn();
})();

/* ─────────────────────────────────────────────
   ROLE CONFIRMATION MODAL
───────────────────────────────────────────── */
let formSubmitAllowed = false;

document.getElementById('registerForm').addEventListener('submit', function(e) {
    // ✅ Validate full name (letters only, first + last)
    if (!nameIsValid()) {
        e.preventDefault();
        const el = document.getElementById('nameInput');
        cleanName(el);
        el.focus();
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    // ✅ Validate mobile number (digits only, PH format)
    if (!phoneIsValid()) {
        e.preventDefault();
        const el = document.getElementById('contactInput');
        cleanPhone(el);
        el.focus();
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    // ✅ Require the truthfulness agreement — cannot register without it
    const agree = document.getElementById('agreeCheck');
    if (!agree.checked) {
        e.preventDefault();
        showAgreeError();
        return;
    }
    hideAgreeError();

    const role = document.getElementById('roleSelect').value;
    if (role === 'parent' && !formSubmitAllowed) {
        e.preventDefault();
        openModal();
    } else if (!formSubmitAllowed) {
        // Animate button
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
    }
});

function openModal() {
    const modal = document.getElementById('roleModal');
    modal.classList.add('open');
}

function closeModal() {
    document.getElementById('roleModal').classList.remove('open');
    formSubmitAllowed = false;
}

function confirmAndSubmit() {
    formSubmitAllowed = true;
    closeModal();
    setTimeout(() => {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        document.getElementById('registerForm').submit();
    }, 150);
}

// Close modal on overlay click
document.getElementById('roleModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

/* ─────────────────────────────────────────────
   GSAP PAGE ENTRANCE ANIMATION
───────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {

    // Restore role if validation failed
    const savedRole = document.getElementById('roleSelect').value;
    if (savedRole) setTimeout(() => handleRoleChange(savedRole), 80);

    // Master timeline
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    // 1. Body fade in
    tl.to('body', { opacity: 1, duration: 0.01 })

    // 2. Left panel slides in from left
      .fromTo('.left-panel',
        { x: -60, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.9 }, 0)

    // 3. Left top fades down
      .to('.gsap-lp-top',
        { opacity: 1, y: 0, duration: 0.7 }, 0.3)

    // 4. Center verse fades in
      .to('.gsap-lp-center',
        { opacity: 1, duration: 0.8 }, 0.5)

    // 5. Bottom block slides up
      .fromTo('.gsap-lp-bottom',
        { opacity: 0, y: 30 },
        { opacity: 1, y: 0, duration: 0.8 }, 0.6)

    // 6. Right panel slides in from right
      .fromTo('.right-panel',
        { x: 40, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.8 }, 0.2)

    // 7. Steps bar
      .to('.gsap-steps',
        { opacity: 1, duration: 0.5 }, 0.6)

    // 8. Form header
      .to('.gsap-form-header',
        { opacity: 1, y: 0, duration: 0.5 }, 0.75)

    // 9. Fields stagger
      .to('.gsap-field',
        { opacity: 1, y: 0, stagger: 0.06, duration: 0.4 }, 0.85)

    // 10. Agreement + button + footer
      .to('.gsap-agree',
        { opacity: 1, duration: 0.4 }, 1.3)
      .to('.gsap-btn',
        { opacity: 1, duration: 0.4 }, 1.4)
      .to('.gsap-footer',
        { opacity: 1, stagger: 0.08, duration: 0.4 }, 1.5);

    // Button hover
    const btn = document.getElementById('submitBtn');
    btn.addEventListener('mouseenter', () => {
        if (!btn.classList.contains('loading')) {
            gsap.to(btn, { y: -2, scale: 1.01, duration: 0.2 });
        }
    });
    btn.addEventListener('mouseleave', () => {
        if (!btn.classList.contains('loading')) {
            gsap.to(btn, { y: 0, scale: 1, duration: 0.2 });
        }
    });
});


function slideTo(anchor, e) {
    e.preventDefault();
    const url = anchor.href;

    gsap.to(['.left-panel', '.right-panel'], {
        x: 60,
        opacity: 0,
        duration: 0.4,
        ease: 'power3.in',
        stagger: 0.05,
        onComplete: function() {
            window.location.href = url;
        }
    });
}
</script>
</body>
</html>