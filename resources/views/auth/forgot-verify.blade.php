<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Reset Code — DP-LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts (same pairing as Login / Register) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --green-500: #16a34a;
            --green-600: #15803d;
            --green-700: #166534;
            --green-50:  #f0fdf4;
            --ink:       #0f172a;
            --ink-muted: #64748b;
            --border:    #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
            background: url('/images/bg.jpg') center/cover no-repeat fixed;
            position: relative;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: ''; position: fixed; inset: 0;
            background: inherit; filter: blur(6px);
            transform: scale(1.06); z-index: 0;
        }
        body::after {
            content: ''; position: fixed; inset: 0;
            background:
                radial-gradient(120% 100% at 50% 0%, rgba(6,78,59,0.30), transparent 55%),
                rgba(0,0,0,0.55);
            z-index: 0;
        }

        .wrapper {
            position: relative; z-index: 1;
            width: 100%; max-width: 430px;
            animation: fadeSlideUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes fadeSlideUp {
            from { opacity:0; transform:translateY(18px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .logo-wrap { text-align:center; margin-bottom:20px; }
        .logo-box {
            width:88px; height:88px; border-radius:50%; overflow:hidden;
            margin:0 auto 14px; background:#f0fdf4;
            box-shadow:0 0 0 3px rgba(255,255,255,0.85),
                       0 0 0 6px rgba(22,163,74,0.18),
                       0 10px 28px rgba(0,0,0,0.28);
            display:flex; align-items:center; justify-content:center;
            animation:popIn 0.55s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
        }
        @keyframes popIn {
            from { opacity:0; transform:scale(0.65); }
            to   { opacity:1; transform:scale(1); }
        }
        .logo-title {
            font-family:'Cormorant Garamond', serif;
            font-size:22px; font-weight:800; color:#fff; line-height:1.15;
            text-shadow:0 2px 10px rgba(0,0,0,0.45); letter-spacing:0.3px;
        }
        .logo-sub { font-size:12.5px; font-weight:600; color:rgba(255,255,255,0.9); margin-top:4px; text-shadow:0 1px 6px rgba(0,0,0,0.4); }

        .card {
            position:relative; overflow:hidden;
            background:#fff; border-radius:22px;
            box-shadow:0 4px 6px rgba(0,0,0,0.05), 0 24px 64px rgba(0,0,0,0.26);
            padding:32px 30px 26px;
        }
        /* brand accent bar */
        .card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:4px;
            background:linear-gradient(90deg, var(--green-500), var(--green-700));
        }

        .card-icon {
            width:54px; height:54px; background:var(--green-50); border-radius:15px;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 16px;
        }

        .card-heading { font-family:'Cormorant Garamond', serif; font-size:24px; font-weight:700; color:var(--ink); text-align:center; margin-bottom:6px; }
        .card-desc    { font-size:13px; color:var(--ink-muted); text-align:center; margin-bottom:22px; line-height:1.6; }

        .alert { border-radius:10px; padding:11px 14px; font-size:13px; margin-bottom:16px; line-height:1.6; font-weight:500; }
        .alert-success { background:#f0fdf4; border:1.5px solid #86efac; color:#15803d; }
        .alert-error   { background:#fef2f2; border:1.5px solid #fecaca; color:#b91c1c; }

        .form-group { margin-bottom:16px; }
        label { display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:8px; letter-spacing:0.4px; text-transform:uppercase; }

        input[type="text"] {
            width:100%; border:2px solid var(--border); border-radius:12px;
            padding:14px 16px; font-size:28px; font-weight:800;
            color:var(--ink); background:#f9fafb; outline:none;
            text-align:center; letter-spacing:14px; font-family:'DM Sans', monospace;
            transition:border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        input[type="text"]:focus {
            border-color:var(--green-500);
            box-shadow:0 0 0 3px rgba(22,163,74,0.14);
            background:#fff;
        }
        input::placeholder { color:#d1d5db; font-size:22px; letter-spacing:10px; }

        .timer { text-align:center; font-size:12px; color:#9ca3af; margin-top:8px; font-weight:500; }
        .timer span { color:var(--green-600); font-weight:700; }

        .btn-submit {
            width:100%; background:linear-gradient(135deg, var(--green-500), var(--green-700));
            color:white; border:none; border-radius:12px; padding:13px;
            font-size:15px; font-weight:700; cursor:pointer; margin-top:6px;
            font-family:'DM Sans', sans-serif; letter-spacing:0.3px;
            transition:filter 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow:0 6px 18px rgba(22,163,74,0.32);
        }
        .btn-submit:hover { filter:brightness(1.05); box-shadow:0 8px 24px rgba(22,163,74,0.42); }
        .btn-submit:active { transform:scale(0.985); }

        .card-footer { text-align:center; margin-top:18px; font-size:13px; color:var(--ink-muted); font-weight:500; }
        .card-footer a { color:var(--green-600); font-weight:700; text-decoration:none; }
        .card-footer a:hover { text-decoration:underline; }

        .copyright { text-align:center; font-size:11px; color:rgba(255,255,255,0.6); margin-top:16px; text-shadow:0 1px 5px rgba(0,0,0,0.4); }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="logo-wrap">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="Logo"
                 style="width:100%;height:100%;object-fit:contain;padding:6px;">
        </div>
        <p class="logo-title">DP-LMS</p>
        <p class="logo-sub">Sto. Domingo National High School</p>
    </div>

    <div class="card">

        <div class="card-icon">
            <svg width="24" height="24" fill="none" stroke="#16a34a"
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
                <label style="text-align:center;">6-Digit Code</label>
                <input type="text" name="code" id="code-input"
                    maxlength="6" placeholder="• • • • • •"
                    inputmode="numeric" pattern="[0-9]*"
                    autocomplete="one-time-code" autofocus required>
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