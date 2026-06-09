<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>
<body style="font-family: sans-serif; background: #f3f4f6; padding: 40px;">
    <div style="max-width: 480px; margin: 0 auto; background: white;
                border-radius: 12px; padding: 32px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1e40af; margin-bottom: 4px;">DP-LMS Verification</h2>
        <p style="color: #6b7280; font-size: 14px; margin-bottom: 24px;">
            Sto. Domingo National High School
        </p>

        <p style="color: #374151; font-size: 15px;">Hello, {{ $userName }}!</p>
        <p style="color: #6b7280; font-size: 14px;">
            Use the code below to verify your identity. This code expires in 5 minutes.
        </p>

        <div style="background: #eff6ff; border: 2px dashed #3b82f6;
                    border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0;">
            <p style="font-size: 36px; font-weight: bold; letter-spacing: 12px;
                      color: #1d4ed8; margin: 0;">
                {{ $code }}
            </p>
        </div>

        <p style="color: #9ca3af; font-size: 12px;">
            If you did not request this code, please ignore this email.
            Do not share this code with anyone.
        </p>

        <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 24px 0;">
        <p style="color: #d1d5db; font-size: 11px; text-align: center;">
            &copy; {{ date('Y') }} Sto. Domingo National High School — DP-LMS
        </p>
    </div>
</body>
</html>