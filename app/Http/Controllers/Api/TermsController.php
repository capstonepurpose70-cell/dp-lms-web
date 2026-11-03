<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

/** App: the same one-time Terms of Use / Privacy Policy agreement as the web ("Before you continue"). */
class TermsController extends Controller
{
    /** GET /api/terms — has this account agreed, and where the texts are */
    public function show(Request $request)
    {
        return response()->json([
            'accepted'    => $request->user()->terms_accepted_at !== null,
            'accepted_at' => optional($request->user()->terms_accepted_at)->toIso8601String(),
            'terms_url'   => route('terms'),
            'privacy_url' => route('privacy'),
        ]);
    }

    /** POST /api/terms/accept  { "terms": true } */
    public function accept(Request $request)
    {
        $request->validate(['terms' => 'accepted'], ['terms.accepted' => 'Please agree to the Terms of Use and Privacy Policy to continue.']);

        $user = $request->user();
        if ($user->terms_accepted_at === null) {
            $user->forceFill(['terms_accepted_at' => now()])->save();
            AuditLogService::log('Accepted the Terms of Use and Privacy Policy (app)', 'Auth', "Role: {$user->role}", $user);
        }

        return response()->json(['accepted' => true, 'accepted_at' => $user->terms_accepted_at->toIso8601String()]);
    }
}
