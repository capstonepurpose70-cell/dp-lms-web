<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ParentLinkService;
use Illuminate\Http\Request;

/**
 * Mobile (Flutter) version of parent linking.
 * The app renders the QR code from `activate_url`.
 */
class ParentLinkController extends Controller
{
    /** POST /api/student/parent-link — student */
    public function generate(Request $request, ParentLinkService $links)
    {
        $result = $links->generate($request->user());

        return response()->json([
            'code'       => $result['code'],
            'purpose'    => $result['purpose'],
            'expires_at' => $result['expires_at']->toIso8601String(),
            'url'        => $result['purpose'] === 'link'
                ? route('parent.activate', ['code' => $result['code']])
                : route('parent.recover', ['code' => $result['code']]),
        ], 201);
    }

    /** POST /api/parent/activate — public */
    public function activate(Request $request, ParentLinkService $links)
    {
        $request->merge(ParentLinkService::prepareInput($request->only('contact_number', 'email')));
        $data   = $request->validate(ParentLinkService::activationRules(), ParentLinkService::messages());
        $parent = $links->activate($data);

        return $this->respond($parent, 'Account created.');
    }

    /** POST /api/parent/recover — public */
    public function recover(Request $request, ParentLinkService $links)
    {
        $request->merge(ParentLinkService::prepareInput($request->only('contact_number')));
        $data   = $request->validate(ParentLinkService::recoveryRules(), ParentLinkService::messages());
        $parent = $links->recover($data);   // also signs the account out everywhere

        return $this->respond($parent, 'Password updated.');
    }

    /** Same response shape as /api/login when approved. */
    private function respond(User $parent, string $message)
    {
        if ($parent->status !== 'approved') {
            return response()->json([
                'message' => 'Name did not match the school records. Waiting for admin verification.',
                'status'  => $parent->status,
            ], 202);
        }

        $token = $parent->createToken('mobile-app')->plainTextToken;

        AuditLogService::log('Login (parent link)', 'Auth', null, $parent);

        return response()->json([
            'message' => $message,
            'token'   => $token,
            'user'    => [
                'id'          => $parent->id,
                'name'        => $parent->name,
                'email'       => $parent->email,
                'role'        => $parent->role,
                'grade_level' => $parent->grade_level,
                'status'      => $parent->status,
            ],
        ]);
    }
}
