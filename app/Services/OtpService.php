<?php

namespace App\Services;

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
        $key = 'otp:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw new \Exception('Too many OTP requests. Please wait before requesting again.');
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

        Mail::to($user->email)->send(new OtpMail($code, $user->name));
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