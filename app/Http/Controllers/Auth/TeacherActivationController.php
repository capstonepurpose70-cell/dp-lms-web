<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\TeacherActivationService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;

/** Web: a teacher activates their own account with the employee ID on the Faculty Master List. */
class TeacherActivationController extends Controller
{
    public function show()
    {
        return view('auth.teacher-activate');
    }

    public function start(Request $request, TeacherActivationService $activation)
    {
        if (filled($request->input('website'))) {   // honeypot
            AuditLogService::log('Teacher activation blocked — honeypot', 'Auth', $request->ip());
            return back()->with('error', 'We could not process your request. Please try again.');
        }

        $data = $request->validate(TeacherActivationService::rules() + ['terms' => 'accepted'],
            TeacherActivationService::messages() + ['terms.accepted' => 'Please agree to the Terms of Use and Privacy Policy to activate your account.']);
        $result = $activation->start($data);

        session(['teacher_activation_token' => $result['token'], 'teacher_activation_email' => $result['email_masked']]);

        return redirect()->route('teacher.activate.verify');
    }

    public function showVerify()
    {
        if (!session('teacher_activation_token')) {
            return redirect()->route('teacher.activate');
        }

        return view('auth.teacher-activate-verify', [
            'email'   => session('teacher_activation_email'),
            'minutes' => TeacherActivationService::OTP_MINUTES,
        ]);
    }

    public function verify(Request $request, TeacherActivationService $activation, TrustedDeviceService $devices)
    {
        $request->validate(['code' => 'required|digits:6'], ['code.digits' => 'The code must be 6 digits.']);

        $user = $activation->verify((string) session('teacher_activation_token'), $request->code);
        session()->forget(['teacher_activation_token', 'teacher_activation_email']);
        // Agreed on step 1 (the code is only sent after the Terms box is ticked)
        $user->forceFill(['terms_accepted_at' => now()])->save();

        auth()->login($user);
        $request->session()->regenerate();
        if ($request->boolean('trust_device')) {
            $devices->trust($user, $request);
        }
        AuditLogService::log('Login successful (activation)', 'Auth', 'Role: teacher');

        return redirect()->route('teacher.dashboard')->with('success', 'Your teacher account is active. Welcome to DP-LMS!');
    }

    public function resend(TeacherActivationService $activation)
    {
        $activation->resend((string) session('teacher_activation_token'));

        return back()->with('success', 'A new code was sent.');
    }
}
