<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

/**
 * Forgot password gamit ang 6-digit code sa email — iisa para sa web at mobile.
 *
 * Inayos:
 *  - Hindi nag-e-expire ang code (Carbon 3: negatibo ang diffInMinutes).
 *  - Walang limit sa paghula ng code (brute force ng 1,000,000).
 *  - Sinasabi kung may account ang email (enumeration).
 *  - Mobile: stub na nagsasabing "sent" pero walang ginagawa.
 */
class PasswordResetService
{
    public const CODE_MINUTES = 10;
    private const MAX_WRONG   = 5;

    public static function passwordRules(): array
    {
        return ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()];
    }

    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /** @return bool false kapag naka-rate-limit (3 kada 5 minuto kada email) */
    public function sendCode(string $email): bool
    {
        $email = static::normalize($email);
        $key   = 'reset:' . $email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return false;
        }
        RateLimiter::hit($key, 300);

        // Tahimik kapag walang account — parehong sagot sa caller (walang enumeration).
        if (!User::where('email', $email)->exists()) {
            return true;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => Hash::make($code),
            'created_at' => now(),
        ]);
        RateLimiter::clear(static::wrongKey($email));

        try {
            Mail::send('emails.reset-code', ['code' => $code, 'email' => $email],
                fn ($m) => $m->to($email)->subject('Your DP-LMS Password Reset Code'));
        } catch (\Throwable $e) {
            report($e);
        }

        AuditLogService::log('Password reset code sent', 'Auth', $email);

        return true;
    }

    /**
     * @return string|null null = tama; kung hindi, ang error message
     */
    public function checkCode(string $email, string $code): ?string
    {
        $email = static::normalize($email);

        if (RateLimiter::tooManyAttempts(static::wrongKey($email), self::MAX_WRONG)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return 'Too many incorrect codes. Please request a new one.';
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($record && Carbon::parse($record->created_at)->addMinutes(self::CODE_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return 'Code has expired. Please request a new one.';
        }

        if (!$record || !Hash::check($code, $record->token)) {
            RateLimiter::hit(static::wrongKey($email), 900);
            return 'Invalid code. Please check your email and try again.';
        }

        RateLimiter::clear(static::wrongKey($email));

        return null;
    }

    /** Palitan ang password + patayin ang lahat ng dating session/device. */
    public function reset(string $email, string $password): ?User
    {
        $email = static::normalize($email);
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        $user->update(['password' => Hash::make($password)]);
        app(TrustedDeviceService::class)->forgetAll($user);
        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        AuditLogService::log('Password reset successful', 'Auth', $email);

        return $user;
    }

    private static function wrongKey(string $email): string
    {
        return 'reset-verify:' . $email;
    }
}
