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
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background: url('/images/bg.jpg') center/cover no-repeat fixed;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: inherit;
            filter: blur(2px);
            transform: scale(1.06);
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.42);
            z-index: 0;
        }

        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            animation: fadeSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo-box {
            width: 80px; height: 80px;
            border-radius: 18px; overflow: hidden;
            margin: 0 auto 16px; /* Increased margin slightly */
            background: #fff;
            box-shadow: 0 8px 28px rgba(29,78,216,0.22);
            display: flex; align-items: center; justify-content: center;
            animation: popIn 0.5s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.65); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* Removed logo-title and logo-sub styles from here since they are moved */

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04),
                        0 16px 48px rgba(0,0,0,0.09),
                        0 0 0 1px rgba(0,0,0,0.05);
            padding: 32px 30px 26px; /* Increased top padding for the new title */
            position: relative;
        }

        /* New Style for the Title INSIDE the card */
        .card-system-title {
            font-size: 14px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.4;
            letter-spacing: 0.02em;
            text-align: center;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .card-school-name {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            text-align: center;
            margin-bottom: 20px;
        }

        .card-icon {
            width: 52px; height: 52px;
            background: #eff6ff; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }

        .card-heading {
            font-size: 18px; font-weight: 800;
            color: #111827; text-align: center; margin-bottom: 8px;
        }

        .card-desc {
            font-size: 13px; color: #6b7280;
            text-align: center; margin-bottom: 24px; line-height: 1.6;
        }

        .alert {
            border-radius: 10px; padding: 11px 14px;
            font-size: 13px; margin-bottom: 16px; line-height: 1.6;
        }

        .alert-success {
            background: #f0fdf4; border: 1.5px solid #86efac; color: #15803d;
        }

        .alert-error {
            background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block; font-size: 13px;
            font-weight: 700; color: #111827; margin-bottom: 5px;
        }

        input[type="email"] {
            width: 100%; border: 1.5px solid #e5e7eb;
            border-radius: 9px; padding: 11px 13px;
            font-size: 14px; font-weight: 500;
            color: #111827; background: #fafafa; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input[type="email"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.13);
            background: #fff;
        }

        input::placeholder { color: #c4c9d4; font-size: 13px; }

        .btn-submit {
            width: 100%;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 11px;
            padding: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(22,163,74,0.35);
            letter-spacing: 0.02em;
        }

        .btn-submit:hover {
            background: #15803d;
            box-shadow: 0 6px 22px rgba(22,163,74,0.45);
        }

        .btn-submit:active { transform: scale(0.985); }

        /* Updated Footer */
        .card-footer { 
            text-align: center; 
            margin-top: 20px; 
            font-size: 13px; 
            color: #6b7280; 
            border-top: 1px solid #f1f5f9;
            padding-top: 16px;
        }
        
        .card-footer a { 
            color: #2563eb; 
            font-weight: 700; 
            text-decoration: none; 
        }
        
        .card-footer a:hover { 
            text-decoration: underline; 
        }

        .copyright { text-align: center; font-size: 11px; color: #ffffff; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Logo Box (Kept above card) -->
    <div class="logo-wrap">
        <div class="logo-box">
            {{-- Replace with your logo --}}
            <img src="{{ asset('images/logo.png') }}" alt="Logo"
                 style="width:100%;height:100%;object-fit:contain;padding:8px;">
        </div>
        <!-- Removed the title/sub text from here -->
    </div>

    <div class="card">
        
        <!-- MOVED TITLE INSIDE CARD -->
        <h1 class="card-system-title">Digital Platform Learning Management System</h1>
        <p class="card-school-name">Sto. Domingo National High School</p>
        <!-- END MOVED TITLE -->

        <div class="card-icon">
            <svg width="24" height="24" fill="none" stroke="#3b82f6"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2
                       2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h2 class="card-heading">Reset Password</h2>
        <p class="card-desc">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="padding-left:14px; line-height:1.9;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email address"
                    required>
            </div>

            <button type="submit" class="btn-submit">
                Send Reset Link
            </button>
        </form>

        <div class="card-footer">
            Remember your password?
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>

    <p class="copyright">
        &copy; {{ date('Y') }} Sto. Domingo National High School. All rights reserved.
    </p>
</div>
</body>
</html>