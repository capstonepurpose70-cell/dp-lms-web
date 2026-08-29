<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OfficialLrn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditLogService;
 
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
 
        $user = User::where('email', $request->email)->first();
 
        if (!$user || !Hash::check($request->password, $user->password)) {
            AuditLogService::log('Failed login', 'Auth', $request->email);
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
 
        if ($user->status !== 'approved') {
            AuditLogService::log('Login blocked (not approved)', 'Auth', $user->email, $user);
            return response()->json(['message' => 'Account not yet approved.'], 403);
        }
 
        $token = $user->createToken('mobile-app')->plainTextToken;
 
        AuditLogService::log('Login', 'Auth', null, $user);
 
        return response()->json([
            'token' => $token,
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role,
                'grade_level' => $user->grade_level,
                'status'      => $user->status,
            ],
        ]);
    }
 
    public function logout(Request $request)
    {
        AuditLogService::log('Logout', 'Auth', null, $request->user());
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
 
    public function register(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'nullable|in:student,parent',
            'child_name' => 'required_if:role,parent|nullable|string|max:255',
            'lrn'        => 'required_if:role,student|nullable|digits:12',
        ], [
            'lrn.required_if' => 'LRN is required for students.',
            'lrn.digits'      => 'LRN must be exactly 12 digits.',
        ]);
 
        $role = $request->input('role', 'student');

        // 🎓 LRN gate — kapareho ng web registration. Kung wala ito, kayang
        // lampasan ang buong masterlist check sa pamamagitan ng API.
        $officialLrn = null;
        if ($role === 'student') {
            $officialLrn = OfficialLrn::where('lrn', $request->lrn)->first();

            if (!$officialLrn) {
                AuditLogService::log('Blocked registration — LRN not in masterlist', 'Auth', "LRN: {$request->lrn}");
                return response()->json([
                    'message' => 'Your LRN is not on the school\'s official list. Please contact the registrar.',
                    'errors'  => ['lrn' => ['LRN not found in the official list.']],
                ], 422);
            }

            if ($officialLrn->claimed_by !== null || User::where('lrn', $request->lrn)->exists()) {
                AuditLogService::log('Blocked registration — LRN already used', 'Auth', "LRN: {$request->lrn}");
                return response()->json([
                    'message' => 'This LRN is already registered.',
                    'errors'  => ['lrn' => ['This LRN is already registered.']],
                ], 422);
            }

            if (!$officialLrn->matchesName($request->name)) {
                AuditLogService::log(
                    'Blocked registration — name does not match LRN',
                    'Auth',
                    "LRN: {$request->lrn} | Entered: {$request->name}"
                );
                return response()->json([
                    'message' => 'The name you entered does not match the record for this LRN.',
                    'errors'  => ['lrn' => ['Name does not match the school record for this LRN.']],
                ], 422);
            }
        }
 
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $role,
            'status'      => 'pending',
            'child_name'  => $role === 'parent' ? $request->child_name : null,
            'lrn'         => $role === 'student' ? $request->lrn : null,
            'grade_level' => $role === 'student' ? ($officialLrn->grade_level ?? null) : null,
        ]);

        // I-claim ang LRN — hindi na magagamit ng iba
        if ($officialLrn) {
            $officialLrn->update(['claimed_by' => $user->id]);
        }
 
        AuditLogService::log('Registration', 'Auth', $user->email, $user);
 
        return response()->json(['message' => 'Registered! Please wait for admin approval.'], 201);
    }
 
    public function forgotPassword(Request $request)
    {
        // You can reuse your existing OTP/email flow here
        return response()->json(['message' => 'Password reset email sent.']);
    }
}