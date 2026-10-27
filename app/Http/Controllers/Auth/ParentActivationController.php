<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\ParentLinkService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;

/**
 * Parents: create an account (activate) or reset the password / sign in on
 * a new device (recover) with a code from the child's account.
 */
class ParentActivationController extends Controller
{
    public function showActivate(Request $request)
    {
        return view('auth.parent-activate', ['code' => (string) $request->query('code', '')]);
    }

    public function activate(Request $request, ParentLinkService $links, TrustedDeviceService $devices)
    {
        $request->merge(ParentLinkService::prepareInput($request->only('contact_number', 'email')));
        $data   = $request->validate(ParentLinkService::activationRules(), ParentLinkService::messages());
        $parent = $links->activate($data);

        if ($parent->status !== 'approved') {
            return redirect()->route('login')->with('success',
                'Your account was created, but your name does not match your child\'s records. '
                . 'You can sign in once the school admin verifies it.');
        }

        auth()->login($parent);
        $request->session()->regenerate();
        $devices->trust($parent, $request);

        AuditLogService::log('Login successful (parent activation)', 'Auth', 'Role: parent');

        return redirect()->route('parent.dashboard')->with('success', 'Welcome! Your account is now linked to your child.');
    }

    public function showRecover(Request $request)
    {
        return view('auth.parent-recover', ['code' => (string) $request->query('code', '')]);
    }

    public function recover(Request $request, ParentLinkService $links, TrustedDeviceService $devices)
    {
        $request->merge(ParentLinkService::prepareInput($request->only('contact_number')));
        $data   = $request->validate(ParentLinkService::recoveryRules(), ParentLinkService::messages());
        $parent = $links->recover($data);   // also signs the account out everywhere

        if ($parent->status !== 'approved') {
            return redirect()->route('login')->with('success',
                'Your password was changed. You can sign in once the school admin verifies your account.');
        }

        auth()->login($parent);
        $request->session()->regenerate();
        $devices->trust($parent, $request);

        AuditLogService::log('Login successful (parent recovery)', 'Auth', 'Role: parent');

        return redirect()->route('parent.dashboard')->with('success', 'Your password has been changed.');
    }
}
