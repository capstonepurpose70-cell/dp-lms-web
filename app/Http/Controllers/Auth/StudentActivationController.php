<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\StudentActivationService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;

/**
 * Estudyante: unang beses na pag-activate ng account (pamalit sa /register).
 * Ang activation token ay nasa session — hindi sa URL.
 */
class StudentActivationController extends Controller
{
    public function show()
    {
        return view('auth.activate');
    }

    public function start(Request $request, StudentActivationService $activation)
    {
        // Honeypot — tao ay hindi makakakita sa field na ito
        if (filled($request->input('website'))) {
            AuditLogService::log('Activation blocked — honeypot', 'Auth', $request->ip());
            return back()->with('error', 'Hindi maproseso ang request. Subukan ulit.');
        }

        $data   = $request->validate(StudentActivationService::rules(), StudentActivationService::messages());
        $result = $activation->start($data);

        session([
            'activation_token' => $result['token'],
            'activation_email' => $result['email_masked'],
        ]);

        return redirect()->route('activate.verify');
    }

    public function showVerify()
    {
        if (!session('activation_token')) {
            return redirect()->route('activate');
        }

        return view('auth.activate-verify', [
            'email'   => session('activation_email'),
            'minutes' => StudentActivationService::OTP_MINUTES,
        ]);
    }

    public function verify(Request $request, StudentActivationService $activation, TrustedDeviceService $devices)
    {
        $request->validate(['code' => 'required|digits:6'], ['code.digits' => '6 digits ang code.']);

        $user = $activation->verify((string) session('activation_token'), $request->code);

        session()->forget(['activation_token', 'activation_email']);

        auth()->login($user);
        $request->session()->regenerate();

        if ($request->boolean('trust_device')) {
            $devices->trust($user, $request);
        }

        AuditLogService::log('Login successful (activation)', 'Auth', 'Role: student');

        return redirect()->route('student.enroll')
            ->with('success', 'Na-activate na ang account mo! Susunod: i-fill ang enrollment form para ma-assign ka sa section.');
    }

    public function resend(StudentActivationService $activation)
    {
        $activation->resend((string) session('activation_token'));

        return back()->with('success', 'Nagpadala ulit ng bagong code.');
    }
}
