{{--
    Letterhead shared by every school email. Tables and inline styles only, because mail apps drop style blocks.
    The logo uses APP_URL so it loads from the live site in any mail app.
--}}
@php
    $font = 'Arial, Helvetica, sans-serif';
    $logo = rtrim((string) config('app.url'), '/') . '/images/logo-email.png';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title')</title>
</head>
<body style="margin:0; padding:0; background-color:#eef1f5;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:#eef1f5;">@yield('preview')</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f5;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; background-color:#ffffff; border:1px solid #dde2e9; border-radius:8px; border-collapse:separate;">
                    <tr>
                        <td style="padding:22px 32px 18px; border-bottom:3px solid #b3262b;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="64" valign="middle" style="padding-right:14px;">
                                        <img src="{{ $logo }}" width="64" height="64" alt="Sto. Domingo National High School logo" style="display:block; border:0;">
                                    </td>
                                    <td valign="middle">
                                        <p style="margin:0; font-family:Georgia, serif; font-size:18px; line-height:1.3; font-weight:bold; color:#17427a;">Sto. Domingo National High School</p>
                                        <p style="margin:3px 0 0; font-family:{{ $font }}; font-size:13px; line-height:1.4; color:#5b6675;">San Manuel, Pangasinan</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 30px; font-family:{{ $font }}; font-size:15px; line-height:1.6; color:#2b3442;">
                            <h1 style="margin:0 0 14px; font-family:{{ $font }}; font-size:21px; line-height:1.3; font-weight:bold; color:#17427a;">@yield('title')</h1>
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; background-color:#f6f7f9; border-top:1px solid #e6e9ee; border-radius:0 0 8px 8px; font-family:{{ $font }}; font-size:12.5px; line-height:1.5; color:#5b6675;">
                            This is an automated message from DP-LMS, the learning system of Sto. Domingo National High School. Please do not reply to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
