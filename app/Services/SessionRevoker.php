<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Signs an account out everywhere after its password changes: mobile app tokens,
 * remembered browsers (OTP skip) and open web sessions. Callers that log the user
 * back in on this device must do it after calling revokeAll().
 */
class SessionRevoker
{
    public function __construct(private TrustedDeviceService $devices)
    {
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->delete();
        $this->devices->forgetAll($user);

        // Other drivers (file, cookie, redis) cannot be searched by user; production uses database.
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
