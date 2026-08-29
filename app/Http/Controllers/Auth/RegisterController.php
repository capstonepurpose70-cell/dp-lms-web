<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'name'     => [
                'required', 'string', 'max:255',
                // Full name: at least two words, letters only (no numbers/symbols)
                'regex:/^[\pL]+(?:[ \'\-][\pL]+)+$/u',
            ],
            'email'    => 'required|email|unique:users,email',
            'role'     => ['required', Rule::in(['student', 'parent'])],
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/',
            ],
            'contact_number' => [
                'required',
                'regex:/^09\d{9}$/',                 // PH mobile: 11 digits starting 09
                'unique:users,contact_number',        // cannot reuse a registered number
            ],
            // ✅ Must tick the truthfulness agreement — server-side safety net
            'agree'          => 'accepted',
            'child_name'     => 'required_if:role,parent|nullable|string|max:255',
            // 🎓 Student LRN — dapat 12 digits at nasa OPISYAL na masterlist
            'lrn'            => 'required_if:role,student|nullable|digits:12',
        ], [
            'name.regex'              => 'Please enter your full name (first and last) — letters only, no numbers.',
            'password.regex'          => 'Password must have uppercase, lowercase, number, and special character.',
            'role.in'                 => 'Invalid role selected.',
            'contact_number.required' => 'Mobile number is required.',
            'contact_number.regex'    => 'Enter a valid 11-digit PH mobile number (e.g., 09171234567).',
            'contact_number.unique'   => 'This mobile number is already registered.',
            'agree.accepted'          => 'You must confirm that your information is accurate and truthful before registering.',
            'child_name.required_if'  => "Please enter your child's full name.",
            'lrn.required_if'         => 'Your LRN (Learner Reference Number) is required.',
            'lrn.digits'              => 'LRN must be exactly 12 digits.',
        ]);

        // 🎓 LRN gate: dapat nasa OPISYAL na masterlist at HINDI pa nagagamit.
        $officialLrn = null;
        if ($request->role === 'student') {
            $officialLrn = \App\Models\OfficialLrn::where('lrn', $request->lrn)->first();

            if (!$officialLrn) {
                AuditLogService::log('Blocked registration — LRN not in masterlist', 'Auth', "LRN: {$request->lrn}");
                return back()->withInput()->withErrors([
                    'lrn' => 'Your LRN is not on the school\'s official list. Please contact the registrar or admin to have it added.',
                ]);
            }
            if ($officialLrn->claimed_by !== null || \App\Models\User::where('lrn', $request->lrn)->exists()) {
                AuditLogService::log('Blocked registration — LRN already used', 'Auth', "LRN: {$request->lrn}");
                return back()->withInput()->withErrors([
                    'lrn' => 'This LRN is already registered. If this is your LRN, please contact the admin.',
                ]);
            }

            // Dapat tugma ang pangalan sa nakatala sa masterlist — pumipigil sa
            // paggamit ng LRN ng kaklase (sinadya man o typo).
            if (!$officialLrn->matchesName($request->name)) {
                AuditLogService::log(
                    'Blocked registration — name does not match LRN',
                    'Auth',
                    "LRN: {$request->lrn} | Entered: {$request->name}"
                );
                return back()->withInput()->withErrors([
                    'lrn' => 'The name you entered does not match the record for this LRN. Please check your LRN and use your full name as written in your school records, or contact the registrar.',
                ]);
            }
        }

        // Extra backend guard — blocks teacher/admin even if form is manipulated
        if (in_array($request->role, ['teacher', 'admin'])) {
            AuditLogService::log(
                'Blocked role manipulation',
                'Auth',
                "Attempted to register as: {$request->role}"
            );
            abort(403, 'Unauthorized role selection.');
        }

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => $request->role,
            'status'         => 'pending',
            'contact_number' => $request->contact_number,
            'child_name'     => $request->role === 'parent' ? $request->child_name : null,
            'lrn'            => $request->role === 'student' ? $request->lrn : null,
            'grade_level'    => $request->role === 'student' ? ($officialLrn->grade_level ?? null) : null,
        ]);

        // I-claim ang LRN — hindi na magagamit ng iba
        if ($officialLrn) {
            $officialLrn->update(['claimed_by' => $user->id]);
        }

        AuditLogService::log(
            'New registration submitted',
            'Enrollment',
            "Name: {$user->name} | Role: {$user->role}"
        );

        return redirect()->route('login')
            ->with('success', 'Registration submitted successfully. Please wait for administrator approval before logging in.');
    }
}