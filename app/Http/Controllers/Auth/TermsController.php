<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

/** One-time agreement to the Terms of Use and Privacy Policy for accounts made before they existed. */
class TermsController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->terms_accepted_at !== null) {
            return redirect($this->home($request->user()->role));
        }

        return view('legal.accept');
    }

    public function accept(Request $request)
    {
        $request->validate(['terms' => 'accepted'], [
            'terms.accepted' => 'Please agree to the Terms of Use and Privacy Policy to continue.',
        ]);

        $user = $request->user();
        $user->forceFill(['terms_accepted_at' => now()])->save();
        AuditLogService::log('Accepted the Terms of Use and Privacy Policy', 'Auth', "Role: {$user->role}");

        return redirect()->intended($this->home($user->role));
    }

    private function home(?string $role): string
    {
        return match ($role) {
            'teacher' => route('teacher.dashboard'),
            'parent'  => route('parent.dashboard'),
            default   => route('student.dashboard'),
        };
    }
}
