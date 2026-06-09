<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:sans-serif;background:#f3f4f6;padding:40px;">
    <div style="max-width:480px;margin:0 auto;background:#fff;
                border-radius:12px;padding:32px;border:1px solid #e5e7eb;">
        <h2 style="color:#16a34a;margin-bottom:4px;">Enrollment Approved!</h2>
        <p style="color:#6b7280;font-size:14px;margin-bottom:24px;">
            DP-LMS — Sto. Domingo National High School
        </p>
        <p style="color:#374151;">Hello, <strong>{{ $student->name }}</strong>!</p>
        <p style="color:#6b7280;font-size:14px;margin-top:8px;">
            Your enrollment has been approved. Here are your details:
        </p>
        <div style="background:#f0fdf4;border-radius:10px;padding:16px;margin:20px 0;">
            <table style="width:100%;font-size:14px;">
                <tr>
                    <td style="color:#6b7280;padding:4px 0;">Grade Level</td>
                    <td style="color:#111827;font-weight:700;text-align:right;">
                        Grade {{ $request->grade_level }}
                    </td>
                </tr>
                <tr>
                    <td style="color:#6b7280;padding:4px 0;">Section</td>
                    <td style="color:#111827;font-weight:700;text-align:right;">
                        {{ $section->name }}
                    </td>
                </tr>
                <tr>
                    <td style="color:#6b7280;padding:4px 0;">Subjects</td>
                    <td style="color:#111827;font-weight:700;text-align:right;">
                        {{ $subjectCount }} subjects
                    </td>
                </tr>
                <tr>
                    <td style="color:#6b7280;padding:4px 0;">School Year</td>
                    <td style="color:#111827;font-weight:700;text-align:right;">
                        {{ $request->school_year }}
                    </td>
                </tr>
            </table>
        </div>
        <div style="text-align:center;margin:24px 0;">
            <a href="{{ url('/login') }}"
                style="background:#2563eb;color:white;padding:13px 32px;
                       border-radius:10px;text-decoration:none;
                       font-weight:700;font-size:15px;display:inline-block;">
                View My Dashboard
            </a>
        </div>
        <hr style="border:none;border-top:1px solid #f3f4f6;margin:24px 0;">
        <p style="color:#d1d5db;font-size:11px;text-align:center;">
            &copy; {{ date('Y') }} Sto. Domingo National High School — DP-LMS
        </p>
    </div>
</body>
</html>