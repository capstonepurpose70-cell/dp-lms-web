<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Teacher account activation via a one-time link.
 *
 * Two token formats:
 *  - SHA-256 (bulk "Import Teachers") — direct indexed lookup.
 *  - bcrypt (single "Create Teacher") — legacy; checked by scanning open invites.
 * A teacher imported WITHOUT an email enters their own email here; their first
 * login then sends an OTP to it, which proves they own it.
 */
class InviteController extends Controller
{
    private function findTeacher(string $token): ?User
    {
        if (strlen($token) < 32 || strlen($token) > 128) {
            return null;
        }

        $open = User::where('role', 'teacher')
            ->whereNotNull('invite_token')
            ->where('invite_expires_at', '>', now());

        // Bulk-import tokens: SHA-256 hex
        $hit = (clone $open)->where('invite_token', hash('sha256', $token))->first();
        if ($hit) {
            return $hit;
        }

        // Legacy single-create tokens: bcrypt (60 chars, "$2y$…")
        return (clone $open)->where('invite_token', 'like', '$2y$%')
            ->get()
            ->first(fn ($u) => Hash::check($token, $u->invite_token));
    }

    /** Show the accept-invite / set-password page. */
    public function show(string $token)
    {
        $teacher = $this->findTeacher($token);
        abort_unless($teacher, 404);

        return view('auth.accept-invite', compact('teacher', 'token'));
    }

    /** Set password (and email if missing), clear the token. */
    public function accept(Request $request, string $token)
    {
        $teacher = $this->findTeacher($token);
        abort_unless($teacher, 404);

        $needsEmail = empty($teacher->email);

        $data = $request->validate([
            'email'    => $needsEmail
                ? ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')]
                : ['nullable'],
            // Same rule as ChangePasswordController
            'password' => ['required', 'string', 'min:8', 'confirmed',
                           'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/'],
        ], [
            'email.unique'   => 'This email is already used by another account.',
            'password.regex' => 'Password must have uppercase, lowercase, number, and special character (@$!%*?&).',
        ]);

        $update = [
            'password'             => Hash::make($data['password']),
            'invite_token'         => null,
            'invite_expires_at'    => null,
            'must_change_password' => false,
            'status'               => 'approved',
        ];
        if ($needsEmail) {
            $update['email'] = strtolower(trim($data['email']));
        }
        $teacher->update($update);

        AuditLogService::log('Teacher account activated (invite link)', 'Auth', "Teacher: {$teacher->name}", $teacher);

        return redirect()->route('login')
            ->with('success', 'Account activated! Log in with your email and new password.');
    }
}
