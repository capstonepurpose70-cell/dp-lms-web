<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Raspberry Pi / IoT device authentication — header X-Device-Key must equal ATTENDANCE_DEVICE_KEY.
 * No key configured: allowed ONLY when APP_ENV=local (XAMPP testing); closed everywhere else.
 * Single source of truth for every device endpoint (gate attendance, approved faces, face kiosk).
 */
final class DeviceKey
{
    public static function valid(Request $request): bool
    {
        $deviceKey = (string) config('attendance.device_key');

        if ($deviceKey === '') {
            return app()->environment('local');
        }

        return hash_equals($deviceKey, (string) $request->header('X-Device-Key', ''));
    }
}
