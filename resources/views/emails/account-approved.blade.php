<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:sans-serif;background:#f3f4f6;padding:40px;">
    <div style="max-width:480px;margin:0 auto;background:#fff;
                border-radius:12px;padding:32px;border:1px solid #e5e7eb;">
        <h2 style="color:#16a34a;margin-bottom:4px;">Account Approved!</h2>
        <p style="color:#6b7280;font-size:14px;margin-bottom:24px;">
            DP-LMS — Sto. Domingo National High School
        </p>
        <p style="color:#374151;">Hello, <strong>{{ $user->name }}</strong>!</p>
        <p style="color:#6b7280;font-size:14px;margin-top:8px;">
            Your DP-LMS account has been reviewed and approved by the administrator.
            You can now log in and submit your enrollment form.
        </p>
        <div style="text-align:center;margin:28px 0;">
            <a href="{{ url('/login') }}"
                style="background:#16a34a;color:white;padding:13px 32px;
                       border-radius:10px;text-decoration:none;
                       font-weight:700;font-size:15px;display:inline-block;">
                Log In Now
            </a>
        </div>
        <p style="color:#9ca3af;font-size:12px;">
            After logging in, please complete your enrollment form so
            the faculty can process your enrollment and assign your section.
        </p>
        <hr style="border:none;border-top:1px solid #f3f4f6;margin:24px 0;">
        <p style="color:#d1d5db;font-size:11px;text-align:center;">
            &copy; {{ date('Y') }} Sto. Domingo National High School — DP-LMS
        </p>
    </div>
</body>
</html>