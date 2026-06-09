<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/',
            ],
        ], [
            'password.regex' => 'Password must have uppercase, lowercase, number, and special character.',
        ]);

        $user = auth()->user();

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuditLogService::log('Teacher changed password', 'Auth');

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Password changed successfully.');
    }
}