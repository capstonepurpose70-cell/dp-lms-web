<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Admin profile + password.
 *  - Email change needs the current password (the email is the admin's login name —
 *    a hijacked session must not be able to lock the real admin out).
 *  - 5 wrong current-password attempts → wait 15 minutes (per admin).
 *  - After a password change, other devices are signed out (AuthenticateSession on admin routes).
 */
class AdminProfileController extends Controller
{
    private const MAX_ATTEMPTS  = 5;
    private const DECAY_SECONDS = 900;

    public function editProfile()
    {
        $admin = auth('admin')->user();
        return view('admin.profile.edit', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        /** @var Admin $admin */
        $admin = auth('admin')->user();
        $request->merge([
            'name'  => preg_replace('/\s+/', ' ', trim((string) $request->name)),
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
        ]);

        $emailChanged = $request->email !== strtolower((string) $admin->email);
        if ($emailChanged) {
            $request->validate(['current_password' => ['required', 'string']], [
                'current_password.required' => 'Enter your current password to change your email.',
            ]);
            $this->verifyCurrentPassword($request, $admin);
        }

        $oldEmail = $admin->email;
        $admin->update(['name' => $request->name, 'email' => $request->email]);

        AuditLogService::log('Updated admin profile', 'Auth', $emailChanged ? "Email changed from {$oldEmail} to {$admin->email}" : null);

        return back()->with('success', $emailChanged
            ? 'Profile updated. Use your new email the next time you sign in.'
            : 'Profile updated successfully.');
    }

    public function changePassword()
    {
        return view('admin.profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        /** @var Admin $admin */
        $admin = auth('admin')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            // Pinakamataas na access sa system — pinakamahigpit na password rule
            'password'         => ['required', 'confirmed', 'different:current_password',
                                   Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'password.different' => 'Your new password must be different from your current password.',
        ]);

        $this->verifyCurrentPassword($request, $admin);

        $admin->update(['password' => Hash::make($request->password)]);
        $request->session()->regenerate();   // fresh session id for this device

        AuditLogService::log('Changed admin password', 'Auth', 'Other signed-in devices were signed out');

        return back()->with('success', 'Password changed. Any other device signed in to this account has been signed out.');
    }

    /** Wrong current password → validation error; too many tries → wait. */
    private function verifyCurrentPassword(Request $request, Admin $admin): void
    {
        $key = 'admin-profile-pw:' . $admin->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages([
                'current_password' => "Too many wrong attempts. Try again in {$minutes} " . ($minutes === 1 ? 'minute' : 'minutes') . '.',
            ]);
        }

        if (!Hash::check((string) $request->current_password, $admin->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            AuditLogService::log('Failed admin password check (profile)', 'Auth');
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.']);
        }

        RateLimiter::clear($key);
    }
}
