<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

class InviteController extends Controller
{
    /**
     * Show the accept-invite / set-password page.
     */
    public function show(string $token)
    {
       $teacher = User::whereNotNull('invite_token')
    ->where('invite_expires_at', '>', now())
    ->get()
    ->first(fn($u) => Hash::check($token, $u->invite_token));

if (!$teacher) abort(404);

        return view('auth.accept-invite', compact('teacher', 'token'));
    }

    /**
     * Set password, clear token, log teacher in.
     */
    public function accept(Request $request, string $token)
    {
       $teacher = User::whereNotNull('invite_token')
    ->where('invite_expires_at', '>', now())
    ->get()
    ->first(fn($u) => Hash::check($token, $u->invite_token));

if (!$teacher) abort(404);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $teacher->update([
            'password'             => Hash::make($request->password),
            'invite_token'         => null,
            'invite_expires_at'    => null,
            'must_change_password' => false,
            'status'               => 'approved',
        ]);



        return redirect()->route('login')
        ->with('success', 'Account activated! Please log in with your credentials.');
}
}