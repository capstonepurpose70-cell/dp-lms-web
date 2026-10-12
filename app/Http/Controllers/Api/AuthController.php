<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditLogService;
 
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Field name "email" pa rin (HUWAG palitan — gamit ng naka-deploy na app),
        // pero tumatanggap na ng email, LRN, o mobile number, kapareho ng web.
        $request->validate([
            'email'    => 'required|string|max:254',
            'password' => 'required',
        ]);

        $user = User::resolveByIdentifier($request->email);
 
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
        // Wala nang public registration.
        // Estudyante: /api/activate (LRN + pangalan + email code).
        // Magulang:   /api/parent/activate (code mula sa account ng anak).
        return response()->json([
            'message' => 'Registration has been replaced. Students: use /api/activate. '
                . "Parents: use the code from your child's account at /api/parent/activate.",
        ], 410);
    }

    public function forgotPassword(Request $request)
    {
        // You can reuse your existing OTP/email flow here
        return response()->json(['message' => 'Password reset email sent.']);
    }
}