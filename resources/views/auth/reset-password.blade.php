<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
            background: url('/images/bg.jpg') center/cover no-repeat fixed;
            position: relative;
        }

        body::before {
            content: ''; position: fixed; inset: 0;
            background: inherit; filter: blur(2px);
            transform: scale(1.06); z-index: 0;
        }

        body::after {
            content: ''; position: fixed; inset: 0;
            background: rgba(0,0,0,0.42); z-index: 0;
        }

        .wrapper {
            position: relative; z-index: 1;
            width: 100%; max-width: 420px;
            animation: fadeSlideUp 0.45s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes fadeSlideUp {
            from { opacity:0; transform:translateY(18px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .logo-wrap { text-align:center; margin-bottom:20px; }

        .logo-box {
            width:80px; height:80px; border-radius:18px; overflow:hidden;
            margin:0 auto 14px; background:#fff;
            box-shadow:0 8px 32px rgba(0,0,0,0.25), 0 0 0 3px rgba(255,255,255,0.15);
            display:flex; align-items:center; justify-content:center;
            animation:popIn 0.55s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
        }

        @keyframes popIn {
            from { opacity:0; transform:scale(0.65); }
            to   { opacity:1; transform:scale(1); }
        }

        .logo-title { font-size:14px; font-weight:800; color:#fff; line-height:1.45; text-shadow:0 1px 6px rgba(0,0,0,0.35); }
        .logo-sub   { font-size:13px; font-weight:600; color:rgba(255,255,255,0.88); margin-top:3px; text-shadow:0 1px 6px rgba(0,0,0,0.35); }

        .card {
            background:#fff; border-radius:20px;
            box-shadow:0 4px 6px rgba(0,0,0,0.05), 0 20px 60px rgba(0,0,0,0.20);
            padding:30px 30px 26px;
        }

        .card-icon {
            width:52px; height:52px; background:#f0fdf4; border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 16px;
        }

        .card-heading { font-size:18px; font-weight:800; color:#111827; text-align:center; margin-bottom:6px; }
        .card-desc    { font-size:13px; color:#6b7280; text-align:center; margin-bottom:22px; line-height:1.6; }

        .alert { border-radius:10px; padding:11px 14px; font-size:13px; margin-bottom:16px; line-height:1.6; }
        .alert-error { background:#fef2f2; border:1.5px solid #fecaca; color:#b91c1c; }

        .form-group { margin-bottom:14px; }

        label { display:block; font-size:13px; font-weight:700; color:#111827; margin-bottom:5px; }

        .pw-wrap { position:relative; }
        .pw-wrap input { padding-right:44px; }

        input[type="password"],
        input[type="text"] {
            width:100%; border:1.5px solid #e5e7eb; border-radius:9px;
            padding:11px 13px; font-size:14px; font-weight:500;
            color:#111827; background:#fafafa; outline:none;
            transition:border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input:focus {
            border-color:#3b82f6;
            box-shadow:0 0 0 3px rgba(59,130,246,0.13);
            background:#fff;
        }

        input::placeholder { color:#c4c9d4; font-size:13px; }

        .toggle-btn {
            position:absolute; right:12px; top:50%;
            transform:translateY(-50%);
            background:none; border:none; cursor:pointer;
            padding:4px; color:#9ca3af;
            display:flex; align-items:center;
            transition:color 0.2s;
        }

        .toggle-btn:hover { color:#3b82f6; }

        .hint { font-size:11px; color:#9ca3af; margin-top:4px; }
        .error-msg { font-size:11px; color:#ef4444; margin-top:4px; font-weight:500; }

        .btn-submit {
            width:100%; background:#16a34a; color:white;
            border:none; border-radius:11px; padding:12px;
            font-size:15px; font-weight:700; cursor:pointer; margin-top:6px;
            transition:background 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow:0 4px 16px rgba(22,163,74,0.32);
        }

        .btn-submit:hover { background:#15803d; box-shadow:0 6px 22px rgba(22,163,74,0.42); }
        .btn-submit:active { transform:scale(0.985); }

        .card-footer { text-align:center; margin-top:16px; font-size:13px; color:#6b7280; }
        .card-footer a { color:#2563eb; font-weight:700; text-decoration:none; }
        .card-footer a:hover { text-decoration:underline; }

        .copyright { text-align:center; font-size:11px; color:rgba(255,255,255,0.55); margin-top:16px; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="logo-wrap">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="Logo"
                 style="width:100%;height:100%;object-fit:contain;padding:6px;">
        </div>
        <p class="logo-title">DIGITAL PLATFORM LEARNING MANAGEMENT SYSTEM</p>
        <p class="logo-sub">Sto. Domingo National High School</p>
    </div>

    <div class="card">

        <div class="card-icon">
            <svg width="24" height="24" fill="none" stroke="#22c55e"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0
                       0112 2.944a11.955 11.955 0 01-8.618
                       3.04A12.02 12.02 0 003 9c0 5.591
                       3.824 10.29 9 11.622 5.176-1.332
                       9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>

        <h2 class="card-heading">Set New Password</h2>
        <p class="card-desc">
            Create a new password for<br>
            <strong style="color:#111827;">{{ session('reset_email') }}</strong>
        </p>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- New Password --}}
            <div class="form-group">
                <label>New Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password" id="pw1"
                        placeholder="Enter new password" required>
                    <button type="button" class="toggle-btn"
                        onclick="togglePw('pw1','e1on','e1off')" tabindex="-1">
                        <svg id="e1on" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0
                                   8.268 2.943 9.542 7-1.274 4.057-5.064
                                   7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="e1off" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478
                                   0-8.268-2.943-9.543-7a9.97 9.97 0
                                   011.563-3.029m5.858.908a3 3 0 114.243
                                   4.243M9.878 9.878l4.242 4.242M9.88
                                   9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3
                                   3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478
                                   0 8.268 2.943 9.543 7a10.025 10.025 0
                                   01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p class="hint">Minimum 8 characters.</p>
                @error('password')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="pw-wrap">
                    <input type="password" name="password_confirmation"
                        id="pw2" placeholder="Re-enter new password" required>
                    <button type="button" class="toggle-btn"
                        onclick="togglePw('pw2','e2on','e2off')" tabindex="-1">
                        <svg id="e2on" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0
                                   8.268 2.943 9.542 7-1.274 4.057-5.064
                                   7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="e2off" width="18" height="18" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478
                                   0-8.268-2.943-9.543-7a9.97 9.97 0
                                   011.563-3.029m5.858.908a3 3 0 114.243
                                   4.243M9.878 9.878l4.242 4.242M9.88
                                   9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3
                                   3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478
                                   0 8.268 2.943 9.543 7a10.025 10.025 0
                                   01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Reset Password
            </button>
        </form>

        <div class="card-footer">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>

    <p class="copyright">
        &copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.
    </p>
</div>

<script>
    function togglePw(fieldId, onId, offId) {
        const f   = document.getElementById(fieldId);
        const on  = document.getElementById(onId);
        const off = document.getElementById(offId);
        const hidden = f.type === 'password';
        f.type           = hidden ? 'text'  : 'password';
        on.style.display  = hidden ? 'none'  : 'block';
        off.style.display = hidden ? 'block' : 'none';
    }
</script>

</body>
</html>