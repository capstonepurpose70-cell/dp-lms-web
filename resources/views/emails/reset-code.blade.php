<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:sans-serif; background:#f3f4f6; padding:40px;">
    <div style="max-width:480px; margin:0 auto; background:white;
                border-radius:12px; padding:32px; border:1px solid #e5e7eb;">

        <h2 style="color:#1e40af; margin-bottom:4px;">DP-LMS Password Reset</h2>
        <p style="color:#6b7280; font-size:14px; margin-bottom:24px;">
            Sto. Domingo National High School
        </p>

        <p style="color:#374151; font-size:15px;">Hello,</p>
        <p style="color:#6b7280; font-size:14px; margin-top:8px;">
            Use the code below to reset your password for
            <strong>{{ $email }}</strong>.<br>
            This code expires in <strong>10 minutes</strong>.
        </p>

        <div style="background:#eff6ff; border:2px dashed #3b82f6;
                    border-radius:12px; padding:24px; text-align:center; margin:24px 0;">
            <p style="font-size:40px; font-weight:900; letter-spacing:14px;
                      color:#1d4ed8; margin:0; font-family:monospace;">
                {{ $code }}
            </p>
        </div>

        <p style="color:#9ca3af; font-size:12px;">
            If you did not request a password reset, please ignore this email.
            Do not share this code with anyone.
        </p>

        <hr style="border:none; border-top:1px solid #f3f4f6; margin:24px 0;">
        <p style="color:#d1d5db; font-size:11px; text-align:center;">
            &copy; {{ date('Y') }} Sto. Domingo National High School — DP-LMS
        </p>
    </div>
</body>
</html>