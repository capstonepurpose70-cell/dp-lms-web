<?php

namespace App\Services;

use App\Models\ParentLinkCode;
use App\Models\User;
use App\Support\NameMatcher;
use App\Support\PhoneNumber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Links a parent to a student with a one-time code that the STUDENT creates
 * (no adviser/admin hands out codes).
 *
 *   link    → creates the parent account; approved at once when the name matches
 *             the StudentProfile's mother/father/guardian, otherwise pending
 *   recover → resets the password of an already linked parent (new device /
 *             forgotten password) — used instead of an SMS OTP
 *
 * Errors are ValidationExceptions so the web (redirect back + errors) and the
 * API (422 JSON) handle them the same way.
 */
class ParentLinkService
{
    public const TTL_HOURS = 24;

    // No 0/O, 1/I/L — easy to read and type
    private const ALPHABET    = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 8;

    public function __construct(private SessionRevoker $sessions)
    {
    }

    // ─── Validation rules (shared by web and API) ─────────────────────────────

    public static function activationRules(): array
    {
        return [
            'code'           => ['required', 'string', 'max:20'],
            'name'           => ['required', 'string', 'max:255', 'regex:/^[\pL .\'\-]+$/u', function ($attr, $value, $fail) {
                if (count(NameMatcher::tokens($value)) < 2) {
                    $fail('Please enter your full name (first and last name).');
                }
            }],
            'contact_number' => ['required', 'regex:/^09\d{9}$/', 'unique:users,contact_number'],
            'email'          => ['nullable', 'email', 'max:254', 'unique:users,email'],
            'password'       => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    public static function recoveryRules(): array
    {
        return [
            'code'           => ['required', 'string', 'max:20'],
            'contact_number' => ['required', 'regex:/^09\d{9}$/'],
            'password'       => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ];
    }

    public static function messages(): array
    {
        return [
            'name.regex'           => 'Names may contain letters only.',
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. 09171234567).',
            'contact_number.unique'=> 'This mobile number is already registered.',
            'email.unique'         => 'This email is already registered.',
        ];
    }

    /** Normalize the mobile number BEFORE validation (accepts +63 and spaces). */
    public static function prepareInput(array $input): array
    {
        if (isset($input['contact_number'])) {
            $input['contact_number'] = PhoneNumber::normalize($input['contact_number']) ?? $input['contact_number'];
        }
        if (isset($input['email']) && trim((string) $input['email']) === '') {
            $input['email'] = null;
        }

        return $input;
    }

    // ─── Code helpers ─────────────────────────────────────────────────────────

    /** "abcd-2345 " → "ABCD2345" */
    public static function normalizeCode(string $input): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($input));
    }

    /** "ABCD2345" → "ABCD-2345" */
    public static function formatCode(string $code): string
    {
        return substr($code, 0, 4) . '-' . substr($code, 4);
    }

    private static function hashCode(string $input): string
    {
        return hash('sha256', static::normalizeCode($input));
    }

    // ─── Generate (student) ───────────────────────────────────────────────────

    /**
     * Creates a new code for the student and deletes the previous one.
     * The plaintext code is returned ONLY now — it is never stored.
     *
     * @return array{code: string, purpose: string, expires_at: Carbon}
     */
    public function generate(User $student): array
    {
        if ($student->role !== 'student' || $student->status !== 'approved') {
            throw ValidationException::withMessages(['code' => 'Only approved students can create a parent code.']);
        }

        if (!$student->studentProfile) {
            throw ValidationException::withMessages(['code' => 'Your enrollment must be approved before you can link a parent.']);
        }

        $parent = $student->parent;

        if ($parent && $parent->status === 'pending') {
            throw ValidationException::withMessages(['code' => 'A linked parent is still waiting for admin approval.']);
        }

        $purpose = $parent ? ParentLinkCode::PURPOSE_RECOVER : ParentLinkCode::PURPOSE_LINK;

        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        $expiresAt = now()->addHours(self::TTL_HOURS);

        DB::transaction(function () use ($student, $code, $purpose, $expiresAt) {
            ParentLinkCode::where('student_id', $student->id)->delete();

            ParentLinkCode::create([
                'student_id' => $student->id,
                'code_hash'  => static::hashCode($code),
                'purpose'    => $purpose,
                'expires_at' => $expiresAt,
            ]);
        });

        AuditLogService::log("Generated parent {$purpose} code", 'Parent Link', "Student: {$student->name}");

        return [
            'code'       => static::formatCode($code),
            'purpose'    => $purpose,
            'expires_at' => $expiresAt,
        ];
    }

    // ─── Activate (parent, new account) ───────────────────────────────────────

    /** @param array $data validated with activationRules() */
    public function activate(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $link    = $this->lockUsableCode($data['code'], ParentLinkCode::PURPOSE_LINK);
            $student = User::whereKey($link->student_id)->lockForUpdate()->first();

            if (!$student || $student->parent_id !== null) {
                throw ValidationException::withMessages(['code' => 'This student already has a linked parent.']);
            }

            $profile = $student->studentProfile;
            $matched = $profile && NameMatcher::matchesAny($data['name'], [
                $profile->mother_name,
                $profile->father_name,
                $profile->guardian_name,
            ]);

            $parent = User::create([
                'name'           => trim($data['name']),
                'email'          => $data['email'] ?? null,
                'contact_number' => $data['contact_number'],
                'password'       => Hash::make($data['password']),
                'role'           => 'parent',
                'status'         => $matched ? 'approved' : 'pending',
            ]);

            $student->update(['parent_id' => $parent->id]);
            $link->update(['used_at' => now()]);

            AuditLogService::log(
                $matched ? 'Parent linked (name matched)' : 'Parent linked — pending (name did not match records)',
                'Parent Link',
                "Parent: {$parent->name} | Student: {$student->name}"
            );

            return $parent;
        });
    }

    // ─── Recover (parent, already linked) ─────────────────────────────────────

    /** @param array $data validated with recoveryRules() */
    public function recover(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $link    = $this->lockUsableCode($data['code'], ParentLinkCode::PURPOSE_RECOVER);
            $student = User::find($link->student_id);
            $parent  = $student?->parent;

            // One message for a wrong code and a wrong number — never reveal
            // which of the two was wrong.
            if (!$parent || $parent->contact_number !== $data['contact_number']) {
                throw ValidationException::withMessages(['code' => 'The code is invalid or expired, or the mobile number does not match.']);
            }

            $parent->update(['password' => Hash::make($data['password'])]);
            $link->update(['used_at' => now()]);
            $this->sessions->revokeAll($parent);

            AuditLogService::log('Parent password reset via child code', 'Parent Link', "Parent: {$parent->name}");

            return $parent;
        });
    }

    private function lockUsableCode(string $input, string $purpose): ParentLinkCode
    {
        $link = ParentLinkCode::where('code_hash', static::hashCode($input))
            ->where('purpose', $purpose)
            ->lockForUpdate()
            ->first();

        if (!$link || !$link->isUsable()) {
            throw ValidationException::withMessages(['code' => 'The code is invalid or expired. Ask your child for a new one.']);
        }

        return $link;
    }
}
