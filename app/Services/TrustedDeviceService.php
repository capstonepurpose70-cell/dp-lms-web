<?php

namespace App\Services;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * "Remember this device" — skips the OTP at login.
 *
 * Random token in an (encrypted) cookie; only its SHA-256 is in the database.
 * One cookie per user (dp_td_{id}) because a parent and child often share
 * one phone.
 */
class TrustedDeviceService
{
    public const DAYS = 30;

    public static function cookieName(User $user): string
    {
        return 'dp_td_' . $user->id;
    }

    public function isTrusted(User $user, Request $request): bool
    {
        return $this->isTrustedToken($user, $request->cookie(static::cookieName($user)), $request);
    }

    /** The app has no cookies, so it keeps the token itself and sends it at login. */
    public function isTrustedToken(User $user, mixed $token, Request $request): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $device = TrustedDevice::where('token_hash', hash('sha256', $token))
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$device) {
            return false;
        }

        $device->update(['last_used_at' => now(), 'ip_address' => $request->ip()]);

        return true;
    }

    /** A new remembered device. Returns the token, shown only this once. */
    public function issueToken(User $user, Request $request): string
    {
        $token = Str::random(64);

        $user->trustedDevices()->create([
            'token_hash'   => hash('sha256', $token),
            'user_agent'   => Str::limit((string) $request->userAgent(), 250, ''),
            'ip_address'   => $request->ip(),
            'last_used_at' => now(),
            'expires_at'   => now()->addDays(self::DAYS),
        ]);

        return $token;
    }

    public function trust(User $user, Request $request): void
    {
        $token = $this->issueToken($user, $request);

        Cookie::queue(Cookie::make(
            static::cookieName($user),
            $token,
            self::DAYS * 24 * 60,
            null,
            null,
            config('session.secure'),
            true,
            false,
            'lax'
        ));
    }

    /** Forget every device — used when the password changes. */
    public function forgetAll(User $user): void
    {
        $user->trustedDevices()->delete();
        Cookie::queue(Cookie::forget(static::cookieName($user)));
    }
}
