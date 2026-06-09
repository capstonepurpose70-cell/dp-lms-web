<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Reset Code — DP-LMS</title>
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
            width:52px; height:52px; background:#eff6ff; border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 16px;
        }

        .card-heading { font-size:18px; font-weight:800; color:#111827; text-align:center; margin-bottom:6px; }
        .card-desc    { font-size:13px; color:#6b7280; text-align:center; margin-bottom:22px; line-height:1.6; }

        .alert { border-radius:10px; padding:11px 14px; font-size:13px; margin-bottom:16px; line-height:1.6; font-weight:500; }
        .alert-success { background:#f0fdf4; border:1.5px solid #86efac; color:#15803d; }
        .alert-error   { background:#fef2f2; border:1.5px solid #fecaca; color:#b91c1c; }

        .form-group { margin-bottom:16px; }

        label { display:block; font-size:13px; font-weight:700; color:#111827; margin-bottom:5px; }

        input[type="text"] {
            width:100%; border:2px solid #e5e7eb; border-radius:10px;
            padding:14px 16px; font-size:28px; font-weight:800;
            color:#111827; background:#fafafa; outline:none;
            text-align:center; letter-spacing:14px; font-family:monospace;
            transition:border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input[type="text"]:focus {
            border-color:#3b82f6;
            box-shadow:0 0 0 3px rgba(59,130,246,0.13);
            background:#fff;
        }

        input::placeholder { color:#d1d5db; font-size:20px; letter-spacing:10px; }

        .timer {
            text-align:center; font-size:12px; color:#9ca3af;
            margin-top:6px; font-weight:500;
        }

        .timer span { color:#ef4444; font-weight:700; }

        .btn-submit {
            width:100%; background:#2563eb; color:white;
            border:none; border-radius:11px; padding:12px;
            font-size:15px; font-weight:700; cursor:pointer; margin-top:4px;
            transition:background 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow:0 4px 16px rgba(37,99,235,0.32);
        }

        .btn-submit:hover { background:#1d4ed8; box-shadow:0 6px 22px rgba(37,99,235,0.42); }
        .btn-submit:active { transform:scale(0.985); }

        .card-footer { text-align:center; margin-top:16px; font-size:13px; color:#6b7280; font-weight:500; }
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
            <svg width="24" height="24" fill="none" stroke="#3b82f6"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0
                       00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4
                       4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h2 class="card-heading">Enter Reset Code</h2>
        <p class="card-desc">
            We sent a 6-digit code to<br>
            <strong style="color:#111827;">{{ session('reset_email') }}</strong>
        </p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

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

        <form method="POST" action="{{ route('password.verify.submit') }}">
            @csrf
            <div class="form-group">
                <label style="text-align:center; display:block;">6-Digit Code</label>
                <input type="text" name="code" id="code-input"
                    maxlength="6" placeholder="• • • • • •"
                    inputmode="numeric" pattern="[0-9]*"
                    autocomplete="one-time-code" required>
                <p class="timer">
                    Code expires in <span id="countdown">10:00</span>
                </p>
            </div>

            <button type="submit" class="btn-submit">Verify Code</button>
        </form>

        <div class="card-footer">
            <a href="{{ route('password.email') }}">Resend code</a>
            &nbsp;·&nbsp;
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>

    <p class="copyright">
        &copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.
    </p>
</div>

<script>
    // Auto-format: only numbers
    document.getElementById('code-input').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });

    // Countdown timer — 10 minutes
    let seconds = 600;
    const el = document.getElementById('countdown');
    const timer = setInterval(() => {
        seconds--;
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        if (seconds <= 0) {
            clearInterval(timer);
            el.textContent = 'Expired';
            el.style.color = '#ef4444';
        }
    }, 1000);
</script>

</body>
</html>