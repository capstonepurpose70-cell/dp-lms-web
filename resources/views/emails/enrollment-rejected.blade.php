<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:sans-serif;background:#f3f4f6;padding:40px;">
    <div style="max-width:480px;margin:0 auto;background:#fff;
                border-radius:12px;padding:32px;border:1px solid #e5e7eb;">
        <h2 style="color:#dc2626;margin-bottom:4px;">Enrollment Update</h2>
        <p style="color:#6b7280;font-size:14px;margin-bottom:24px;">
            DP-LMS — Sto. Domingo National High School
        </p>
        <p style="color:#374151;">Hello, <strong>{{ $student->name }}</strong>!</p>
        <p style="color:#6b7280;font-size:14px;margin-top:8px;">
            We regret to inform you that your enrollment request needs revision.
        </p>
        @if($remarks)
        <div style="background:#fef2f2;border-radius:10px;padding:16px;margin:20px 0;
                    border:1.5px solid #fecaca;">
            <p style="color:#b91c1c;font-size:13px;margin:0;">
                <strong>Reason:</strong> {{ $remarks }}
            </p>
        </div>
        @endif
        <p style="color:#6b7280;font-size:13px;">
            Please log in and resubmit your enrollment form with correct information.
        </p>
        <div style="text-align:center;margin:24px 0;">
            <a href="{{ url('/login') }}"
                style="background:#dc2626;color:white;padding:13px 32px;
                       border-radius:10px;text-decoration:none;
                       font-weight:700;font-size:15px;display:inline-block;">
                Log In & Resubmit
            </a>
        </div>
        <hr style="border:none;border-top:1px solid #f3f4f6;margin:24px 0;">
        <p style="color:#d1d5db;font-size:11px;text-align:center;">
            &copy; {{ date('Y') }} Sto. Domingo National High School — DP-LMS
        </p>
    </div>
</body>
</html>