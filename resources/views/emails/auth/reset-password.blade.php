<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Akun Koperasi</title>
</head>

<body style="margin:0;background-color:#f5f7eb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f5f7eb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background-color:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#f59e0b,#ea580c);padding:32px 24px;text-align:center;">
                            <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}" width="84" height="84" style="display:block;margin:0 auto 16px auto;width:84px;height:84px;border-radius:18px;background-color:#ffffff;padding:10px;box-sizing:border-box;">
                            <p style="margin:0;font-size:14px;letter-spacing:0.08em;text-transform:uppercase;color:#ffedd5;font-weight:700;">Sistem Koperasi</p>
                            <h1 style="margin:12px 0 0 0;font-size:28px;line-height:1.25;color:#ffffff;">Reset Password Akun</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 24px;">
                            <p style="margin:0 0 16px 0;font-size:16px;line-height:1.7;">Halo {{ $displayName }},</p>
                            <p style="margin:0 0 16px 0;font-size:16px;line-height:1.7;">Kami menerima permintaan untuk mengatur ulang password akun Anda di <strong>{{ $appName }}</strong>.</p>
                            <p style="margin:0 0 24px 0;font-size:16px;line-height:1.7;">Klik tombol di bawah ini untuk membuat password baru. Link ini berlaku selama {{ $expireMinutes }} menit.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px 0;">
                                <tr>
                                    <td align="center" bgcolor="#ea580c" style="border-radius:999px;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:1.7;color:#4b5563;">Jika tombol tidak bisa diklik, salin dan buka link berikut di browser Anda:</p>
                            <p style="margin:0 0 24px 0;font-size:14px;line-height:1.8;word-break:break-all;color:#c2410c;">
                                <a href="{{ $resetUrl }}" style="color:#c2410c;text-decoration:none;">{{ $resetUrl }}</a>
                            </p>
                            <p style="margin:0 0 12px 0;font-size:15px;line-height:1.7;color:#4b5563;">Jika Anda tidak meminta reset password, abaikan email ini. Password akun Anda tidak akan berubah.</p>
                            <p style="margin:0;font-size:15px;line-height:1.7;">Salam,<br><strong>{{ $appName }}</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>