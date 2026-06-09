<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>You've been added to DP-LMS</title>
<style>
  body { margin:0; padding:0; background:#f3f4f6; font-family:'Segoe UI',system-ui,sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden;
          box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#e63946,#c1121f); padding:36px 40px; text-align:center; }
  .header h1 { color:#fff; font-size:22px; font-weight:800; margin:0; letter-spacing:-.02em; }
  .header p  { color:rgba(255,255,255,.8); font-size:13px; margin:6px 0 0; }
  .body  { padding:36px 40px; }
  .body p { color:#374151; font-size:14px; line-height:1.7; margin:0 0 16px; }
  .name  { font-weight:700; color:#111827; }
  .btn-wrap { text-align:center; margin:28px 0; }
  .btn  { display:inline-block; background:#e63946; color:#fff !important; text-decoration:none;
          padding:14px 36px; border-radius:10px; font-size:15px; font-weight:700;
          letter-spacing:.02em; box-shadow:0 4px 18px rgba(230,57,70,.35); }
  .note { background:#fef2f2; border:1px solid #fecaca; border-radius:8px;
          padding:12px 16px; font-size:12px; color:#b91c1c; margin-top:8px; }
  .footer { border-top:1px solid #f3f4f6; padding:20px 40px; text-align:center;
            font-size:11px; color:#9ca3af; }
  .url-fallback { word-break:break-all; font-size:11px; color:#6b7280;
                  background:#f9fafb; border-radius:6px; padding:8px 12px;
                  margin-top:12px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>DP-LMS — Teacher Account</h1>
    <p>Sto. Domingo National High School</p>
  </div>
  <div class="body">
    <p>Hello, <span class="name">{{ $teacher->name }}</span>!</p>
    <p>
      Your teacher account on <strong>DP-LMS</strong> has been created by the administrator.
      Click the button below to set your password and activate your account.
    </p>

    <div class="btn-wrap">
      <a href="{{ $inviteUrl }}" class="btn">Activate My Account &rarr;</a>
    </div>

    <div class="note">
      ⏳ This link expires in <strong>{{ $expiresIn }}</strong>. If you did not expect this email,
      you can safely ignore it.
    </div>

    <p style="margin-top:20px;font-size:13px;color:#6b7280;">
      If the button doesn't work, copy and paste this link into your browser:
    </p>
    <div class="url-fallback">{{ $inviteUrl }}</div>
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} Sto. Domingo National High School &middot; DP-LMS
  </div>
</div>
</body>
</html>