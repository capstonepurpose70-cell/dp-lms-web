<?php

namespace App\Services;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * "Tandaan ang device na ito" — nilalaktawan ang OTP sa login.
 *
 * Random token sa (encrypted) cookie; SHA-256 lang ang nasa database.
 * Hiwalay ang cookie kada user (dp_td_{id}) dahil madalas iisang phone
 * ang gamit ng magulang at anak.
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
        $token = $request->cookie(static::cookieName($user));

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

    public function trust(User $user, Request $request): void
    {
        $token = Str::random(64);

        $user->trustedDevices()->create([
            'token_hash'   => hash('sha256', $token),
            'user_agent'   => Str::limit((string) $request->userAgent(), 250, ''),
            'ip_address'   => $request->ip(),
            'last_used_at' => now(),
            'expires_at'   => now()->addDays(self::DAYS),
        ]);

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

    /** Burahin lahat — gamitin kapag pinalitan ang password. */
    public function forgetAll(User $user): void
    {
        $user->trustedDevices()->delete();
        Cookie::queue(Cookie::forget(static::cookieName($user)));
    }
}
