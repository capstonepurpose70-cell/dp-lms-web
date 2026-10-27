<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OfficialLrn;
use App\Models\User;
use App\Support\NameMatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Student self-activation (replaces /register).
 *
 *   1. start()  — LRN + name (matching the SF1 masterlist) + birthdate + email +
 *                 password → 6-digit code by email. NO account yet and the LRN is
 *                 NOT claimed; the data only sits in the (encrypted) cache.
 *   2. verify() — correct code → creates the account (approved) + claims the LRN.
 *
 * The real security gate is the adviser's enrollment approval: before it the
 * student has no section, so no grades or modules are visible.
 *
 * Protection: one generic error (no LRN enumeration), per-LRN lockout
 * (5/hour, 10/day — even for LRNs not on the list), email OTP before the
 * account exists, and a row lock on the claim so there is no double activation.
 */
class StudentActivationService
{
    public const OTP_MINUTES = 10;

    private const MAX_CODE_TRIES = 5;
    private const MAX_RESENDS    = 3;
    private const LRN_PER_HOUR   = 5;
    private const LRN_PER_DAY    = 10;

    private const NO_MATCH = 'The LRN and name do not match the school records. '
        . 'Use your full name exactly as written in the class list, or ask your adviser.';
    private const ALREADY_ACTIVE = 'An account already exists for this LRN. If you did not activate it, '
        . 'report it to your adviser or the school admin right away.';
    private const EXPIRED = 'This activation has expired. Please start again.';

    // ─── Validation rules (shared by web and API) ─────────────────────────────

    public static function rules(): array
    {
        return [
            'lrn'       => ['required', 'digits:12'],
            'name'      => ['required', 'string', 'max:255', 'regex:/^[\pL .\'\-]+$/u'],
            'birthdate' => ['required', 'date', 'after:1990-01-01', 'before:-8 years'],
            'email'     => ['required', 'email', 'max:254', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    public static function messages(): array
    {
        return [
            'lrn.digits'       => 'The LRN must be exactly 12 digits.',
            'name.regex'       => 'Names may contain letters only.',
            'birthdate.before' => 'Please check your birthdate.',
            'birthdate.after'  => 'Please check your birthdate.',
            'email.unique'     => 'This email is already registered. Please use a different email.',
        ];
    }

    // ─── Step 1 ───────────────────────────────────────────────────────────────

    /**
     * @param array $data validated with rules()
     * @return array{token: string, email_masked: string, expires_at: Carbon}
     */
    public function start(array $data): array
    {
        $lrn = $data['lrn'];

        // Lockout per LRN — LRNs that are not on the list count too, so this
        // never reveals whether an LRN exists.
        if ($this->isLocked($lrn)) {
            throw ValidationException::withMessages([
                'lrn' => 'Too many attempts for this LRN. Please try again later or ask your adviser.',
            ]);
        }

        $official = OfficialLrn::where('lrn', $lrn)->first();

        // Strict: a name is required in the masterlist. If it is missing, the admin
        // fixes the data — there is no LRN-only activation.
        if (!$official || !NameMatcher::matches($official->student_name, $data['name'])) {
            $this->recordFailure($lrn);
            throw ValidationException::withMessages(['lrn' => self::NO_MATCH]);
        }

        // Only shown once the LRN + name are correct, so it is not an oracle.
        if ($official->claimed_by !== null || User::where('lrn', $lrn)->exists()) {
            AuditLogService::log('Activation blocked — LRN already activated', 'Auth', "LRN: {$lrn}");
            throw ValidationException::withMessages(['lrn' => self::ALREADY_ACTIVE]);
        }

        $token   = Str::random(48);
        $code    = $this->newCode();
        $payload = [
            'lrn'           => $lrn,
            'name'          => trim($data['name']),
            'birthdate'     => Carbon::parse($data['birthdate'])->toDateString(),
            'email'         => Str::lower(trim($data['email'])),
            'password_hash' => Hash::make($data['password']),
            'code_hash'     => Hash::make($code),
            'tries'         => 0,
            'resends'       => 0,
            'expires_at'    => now()->addMinutes(self::OTP_MINUTES)->timestamp,
        ];

        $this->save($token, $payload);
        $this->sendCode($token, $payload, $code);

        AuditLogService::log('Activation code sent', 'Auth', "LRN: {$lrn}");

        return [
            'token'        => $token,
            'email_masked' => static::maskEmail($payload['email']),
            'expires_at'   => Carbon::createFromTimestamp($payload['expires_at']),
        ];
    }

    public function resend(string $token): void
    {
        $payload = $this->load($token);

        if ($payload['resends'] >= self::MAX_RESENDS) {
            $this->forget($token);
            throw ValidationException::withMessages(['code' => 'You have reached the resend limit. Please start again.']);
        }

        $code = $this->newCode();
        $payload['code_hash']  = Hash::make($code);
        $payload['tries']      = 0;
        $payload['resends']++;
        $payload['expires_at'] = now()->addMinutes(self::OTP_MINUTES)->timestamp;

        $this->save($token, $payload);
        $this->sendCode($token, $payload, $code);
    }

    // ─── Step 2 ───────────────────────────────────────────────────────────────

    public function verify(string $token, string $code): User
    {
        $payload = $this->load($token);

        if ($payload['tries'] >= self::MAX_CODE_TRIES) {
            $this->forget($token);
            throw ValidationException::withMessages(['code' => 'Too many incorrect codes. Please start again.']);
        }

        if (!Hash::check($code, $payload['code_hash'])) {
            $payload['tries']++;
            $this->save($token, $payload);
            throw ValidationException::withMessages(['code' => 'Incorrect code. Please try again.']);
        }

        $user = DB::transaction(function () use ($payload) {
            // Row lock: when two people verify the same LRN at the same time,
            // only one wins.
            $official = OfficialLrn::where('lrn', $payload['lrn'])->lockForUpdate()->first();

            if (!$official || $official->claimed_by !== null || User::where('lrn', $payload['lrn'])->exists()) {
                throw ValidationException::withMessages(['code' => self::ALREADY_ACTIVE]);
            }

            if (User::where('email', $payload['email'])->exists()) {
                throw ValidationException::withMessages(['code' => 'This email is already registered. Please start again with a different email.']);
            }

            $user = User::create([
                'name'        => $payload['name'],
                'email'       => $payload['email'],
                'password'    => $payload['password_hash'],   // already hashed; the cast must not hash it again
                'role'        => 'student',
                'status'      => 'approved',
                'lrn'         => $payload['lrn'],
                'grade_level' => $official->grade_level,
                'birthdate'   => $payload['birthdate'],
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            $official->update(['claimed_by' => $user->id]);

            return $user;
        });

        $this->forget($token);
        RateLimiter::clear('activate:lrn:h:' . $payload['lrn']);
        RateLimiter::clear('activate:lrn:d:' . $payload['lrn']);

        AuditLogService::log('Student account activated', 'Auth', "LRN: {$payload['lrn']}", $user);

        return $user;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** "juan.delacruz@gmail.com" → "ju•••••••••••@gmail.com" */
    public static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 2) . str_repeat('•', max(1, mb_strlen($local) - 2)) . '@' . $domain;
    }

    private function isLocked(string $lrn): bool
    {
        return RateLimiter::tooManyAttempts('activate:lrn:h:' . $lrn, self::LRN_PER_HOUR)
            || RateLimiter::tooManyAttempts('activate:lrn:d:' . $lrn, self::LRN_PER_DAY);
    }

    private function recordFailure(string $lrn): void
    {
        RateLimiter::hit('activate:lrn:h:' . $lrn, 3600);
        RateLimiter::hit('activate:lrn:d:' . $lrn, 86400);

        AuditLogService::log('Activation failed — LRN/name mismatch', 'Auth', "LRN: {$lrn}");

        if ($this->isLocked($lrn)) {
            AuditLogService::log('Activation LOCKED — too many failures for LRN', 'Auth', "LRN: {$lrn}");
        }
    }

    private function newCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendCode(string $token, array $payload, string $code): void
    {
        try {
            Mail::to($payload['email'])->send(new OtpMail($code, $payload['name']));
        } catch (\Throwable $e) {
            $this->forget($token);
            report($e);
            throw ValidationException::withMessages([
                'email' => 'We could not send a code to this email. Please check the address or try again later.',
            ]);
        }
    }

    private static function cacheKey(string $token): string
    {
        return 'activation:' . hash('sha256', $token);
    }

    /** Encrypted sa cache — may password hash at personal na datos. */
    private function save(string $token, array $payload): void
    {
        Cache::put(
            static::cacheKey($token),
            Crypt::encrypt($payload),
            Carbon::createFromTimestamp($payload['expires_at'])
        );
    }

    private function load(string $token): array
    {
        $raw = $token !== '' ? Cache::get(static::cacheKey($token)) : null;

        if (!$raw) {
            throw ValidationException::withMessages(['code' => self::EXPIRED]);
        }

        $payload = Crypt::decrypt($raw);

        if ($payload['expires_at'] < now()->timestamp) {
            $this->forget($token);
            throw ValidationException::withMessages(['code' => self::EXPIRED]);
        }

        return $payload;
    }

    private function forget(string $token): void
    {
        Cache::forget(static::cacheKey($token));
    }
}
