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
        ]);
 
        $role = $request->input('role', 'student');
 
        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $role,
            'status'     => 'pending',
            'child_name' => $role === 'parent' ? $request->child_name : null,
        ]);
 
        AuditLogService::log('Registration', 'Auth', $user->email, $user);
 
        return response()->json(['message' => 'Registered! Please wait for admin approval.'], 201);
    }
 
    public function forgotPassword(Request $request)
    {
        // You can reuse your existing OTP/email flow here
        return response()->json(['message' => 'Password reset email sent.']);
    }
}