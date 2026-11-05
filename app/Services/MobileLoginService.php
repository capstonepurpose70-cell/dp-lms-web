<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The email code step of the app's login, with the same rules as the website.
 *
 * The API has no session, so a correct password returns a short-lived random token that stands
 * for "this person passed the password step". Only its hash is kept, and it works once.
 */
class MobileLoginService
{
    private const TOKEN_MINUTES = 15;
    private const MAX_TRIES = 10;

    public function __construct(private OtpService $otp) {}

    /** Remembers the password step and returns the token for it. */
    public function begin(User $user): string
    {
        $token = Str::random(64);
        Cache::put($this->key($token), $user->id, now()->addMinutes(self::TOKEN_MINUTES));

        return $token;
    }

    /** @throws ActionNotAllowed when too many codes were sent */
    public function sendCode(User $user): void
    {
        $this->otp->send($user, 'login');
    }

    public function resend(string $token): void
    {
        $this->sendCode($this->userFor($token));
    }

    /**
     * @throws ValidationException on a wrong or expired code
     * @throws ActionNotAllowed when the login expired or was guessed too often
     */
    public function verify(string $token, string $code): User
    {
        $user = $this->userFor($token);
        $key  = 'otp-verify:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_TRIES)) {
            Cache::forget($this->key($token));
            AuditLogService::log('OTP brute-force blocked', 'Auth', $user->email ?? '', $user);
            throw new ActionNotAllowed('Too many incorrect attempts. Please log in again.', 429);
        }

        RateLimiter::hit($key, 300);

        if (!$this->otp->verify($user, $code, 'login')) {
            AuditLogService::log('OTP verification failed', 'Auth', $user->email ?? '', $user);
            throw ValidationException::withMessages(['code' => 'That code is wrong or has expired.']);
        }

        Cache::forget($this->key($token));
        RateLimiter::clear($key);

        return $user;
    }

    private function userFor(string $token): User
    {
        $user = User::find(Cache::get($this->key($token)));

        if (!$user) {
            throw new ActionNotAllowed('Your login has expired. Please log in again.', 422);
        }

        return $user;
    }

    private function key(string $token): string
    {
        return 'mobile-login:' . hash('sha256', $token);
    }
}
