<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\FacultyRecord;
use App\Models\Section;
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
 * Teacher self-activation from the Faculty Master List (same steps as students with the LRN list).
 *
 *   1. start()  — employee ID + name (must match the list) + email + password → 6-digit code by email. No
 *                 account yet and the record is not claimed; the data waits encrypted in the cache.
 *   2. verify() — correct code → approved teacher account, record claimed, advisory section assigned if free.
 *
 * Protection: one generic error (an employee ID is never confirmed), per-ID lockout (5/hour, 10/day — also for
 * IDs not on the list), email code before the account exists, a row lock on the claim (no double activation).
 */
class TeacherActivationService
{
    public const OTP_MINUTES = 10;
    private const MAX_CODE_TRIES = 5;
    private const MAX_RESENDS    = 3;
    private const PER_HOUR       = 5;
    private const PER_DAY        = 10;

    private const NO_MATCH = 'The employee ID and name do not match the faculty list. '
        . 'Use your full name as the school has it, or ask the school admin.';
    private const ALREADY_ACTIVE = 'An account already exists for this employee ID. If you did not make it, '
        . 'tell the school admin right away.';
    private const EXPIRED = 'This activation has expired. Please start again.';

    public static function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-]+$/'],
            'name'        => ['required', 'string', 'max:255', 'regex:/^[\pL .,\'\-]+$/u'],
            'email'       => ['required', 'email', 'max:254', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    public static function messages(): array
    {
        return [
            'employee_id.regex' => 'The employee ID has letters, numbers and dashes only.',
            'name.regex'        => 'Names may contain letters only.',
            'email.unique'      => 'This email is already registered. Please use a different email.',
        ];
    }

    /** @return array{token: string, email_masked: string} */
    public function start(array $data): array
    {
        $id = Str::upper(trim($data['employee_id']));

        if ($this->isLocked($id)) {
            throw ValidationException::withMessages(['employee_id' => 'Too many attempts for this employee ID. Please try again later or ask the school admin.']);
        }

        $record = FacultyRecord::where('employee_id', $id)->first();
        if (!$record || !NameMatcher::matches($record->name, $data['name'])) {
            $this->recordFailure($id);
            throw ValidationException::withMessages(['employee_id' => self::NO_MATCH]);
        }
        // Only said once the ID and name are right, so it does not reveal which IDs exist
        if ($record->claimed_by !== null || User::where('employee_id', $id)->exists()) {
            AuditLogService::log('Teacher activation blocked — already active', 'Auth', "Employee ID: {$id}");
            throw ValidationException::withMessages(['employee_id' => self::ALREADY_ACTIVE]);
        }

        $token = Str::random(48);
        $code  = $this->newCode();
        $payload = [
            'employee_id'   => $id,
            'name'          => trim(preg_replace('/\s+/u', ' ', $data['name'])),
            'email'         => Str::lower(trim($data['email'])),
            'password_hash' => Hash::make($data['password']),
            'code_hash'     => Hash::make($code),
            'tries'         => 0,
            'resends'       => 0,
            'expires_at'    => now()->addMinutes(self::OTP_MINUTES)->timestamp,
        ];
        $this->save($token, $payload);
        $this->sendCode($token, $payload, $code);
        AuditLogService::log('Teacher activation code sent', 'Auth', "Employee ID: {$id}");

        return ['token' => $token, 'email_masked' => StudentActivationService::maskEmail($payload['email'])];
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
            // Row lock: two people verifying the same ID at once — only one wins
            $record = FacultyRecord::where('employee_id', $payload['employee_id'])->lockForUpdate()->first();
            if (!$record || $record->claimed_by !== null || User::where('employee_id', $payload['employee_id'])->exists()) {
                throw ValidationException::withMessages(['code' => self::ALREADY_ACTIVE]);
            }
            if (User::where('email', $payload['email'])->exists()) {
                throw ValidationException::withMessages(['code' => 'This email is already registered. Please start again with a different email.']);
            }

            $user = User::create([
                'name'        => $record->name,           // the school's spelling, not what was typed
                'email'       => $payload['email'],
                'password'    => $payload['password_hash'],   // already hashed; the cast must not hash it again
                'role'        => 'teacher',
                'status'      => 'approved',
                'employee_id' => $payload['employee_id'],
            ]);
            $user->forceFill(['email_verified_at' => now(), 'must_change_password' => false])->save();
            $record->update(['claimed_by' => $user->id]);

            // Advisory from the list, only when the section still has no adviser
            if ($record->section_id) {
                Section::where('id', $record->section_id)->whereNull('adviser_id')->update(['adviser_id' => $user->id]);
            }

            return $user;
        });

        $this->forget($token);
        RateLimiter::clear('activate:emp:h:' . $payload['employee_id']);
        RateLimiter::clear('activate:emp:d:' . $payload['employee_id']);
        AuditLogService::log('Teacher account activated', 'Auth', "Employee ID: {$payload['employee_id']}", $user);

        return $user;
    }

    private function isLocked(string $id): bool
    {
        return RateLimiter::tooManyAttempts('activate:emp:h:' . $id, self::PER_HOUR)
            || RateLimiter::tooManyAttempts('activate:emp:d:' . $id, self::PER_DAY);
    }

    private function recordFailure(string $id): void
    {
        RateLimiter::hit('activate:emp:h:' . $id, 3600);
        RateLimiter::hit('activate:emp:d:' . $id, 86400);
        AuditLogService::log('Teacher activation failed — ID/name mismatch', 'Auth', "Employee ID: {$id}");
        if ($this->isLocked($id)) {
            AuditLogService::log('Teacher activation LOCKED — too many failures', 'Auth', "Employee ID: {$id}");
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
            throw ValidationException::withMessages(['email' => 'We could not send a code to this email. Please check the address or try again later.']);
        }
    }

    private static function cacheKey(string $token): string
    {
        return 'teacher-activation:' . hash('sha256', $token);
    }

    // Encrypted: the payload holds a password hash and personal details
    private function save(string $token, array $payload): void
    {
        Cache::put(static::cacheKey($token), Crypt::encrypt($payload), Carbon::createFromTimestamp($payload['expires_at']));
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
