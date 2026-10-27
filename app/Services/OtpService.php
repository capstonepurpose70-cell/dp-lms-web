<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\OtpCode;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    public function send(User $user, string $purpose): void
    {
        // No email = nowhere to send the code. The caller (LoginController)
        // handles that case — trusted device or parent recovery code.
        if (empty($user->email)) {
            throw new \RuntimeException('User has no email address for OTP delivery.');
        }

        $key = 'otp:' . $user->id;

        // 5 codes per hour. ActionNotAllowed so the caller can catch it and show
        // a message instead of a 500.
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));
            throw new ActionNotAllowed(
                "Too many codes were sent to your email. Use the latest code we sent, or try again in {$minutes} "
                . ($minutes === 1 ? 'minute.' : 'minutes.'),
                429
            );
        }

        // Delete previous unused OTPs for this user and purpose
        OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->delete();

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'purpose'    => $purpose,
            'expires_at' => now()->addMinutes(5),
            'used'       => false,
        ]);

        RateLimiter::hit($key, 3600);

        // NEVER log the OTP — anyone with access to the server logs would see it.
        // For local dev use MAIL_MAILER=log: the whole email lands in storage/logs.

        // Try to email it, but don't crash if mail is unavailable (e.g. SMTP blocked on host)
        try {
            Mail::to($user->email)->send(new OtpMail($code, $user->name));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OTP email failed: ' . $e->getMessage());
        }
    }

    public function verify(User $user, string $inputCode, string $purpose): bool
    {
        $otp = OtpCode::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp || !Hash::check($inputCode, $otp->code)) {
            return false;
        }

        $otp->update(['used' => true]);
        return true;
    }
}