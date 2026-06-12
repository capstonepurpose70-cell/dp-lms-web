<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceRegistration;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceVerificationController extends Controller
{
    /** List pending + recently reviewed face registrations */
    public function index()
    {
        $pending = FaceRegistration::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn ($r) => $this->withImages($r));

        $reviewed = FaceRegistration::with('user')
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('reviewed_at')
            ->take(12)
            ->get()
            ->map(fn ($r) => $this->withImages($r));

        return view('admin.face-verification.index', compact('pending', 'reviewed'));
    }

    /** Approve a face set */
    public function approve(FaceRegistration $registration)
    {
        $registration->update([
            'status'      => 'approved',
            'reviewed_at' => now(),
        ]);

        AuditLogService::log(
            "Approved face registration: {$registration->user->name}",
            'Face Verification'
        );

        return back()->with('success', "Face approved for {$registration->user->name}.");
    }

    /** Reject (optionally flag as inappropriate -> warning + possible ban) */
    public function reject(Request $request, FaceRegistration $registration)
    {
        $data = $request->validate([
            'reason'        => 'nullable|string|max:255',
            'inappropriate' => 'nullable|boolean',
        ]);

        $inappropriate = (bool) ($data['inappropriate'] ?? false);
        $user = $registration->user;

        $registration->update([
            'status'        => 'rejected',
            'reject_reason' => $data['reason'] ?? 'Hindi malinaw o hindi wastong larawan.',
            'inappropriate' => $inappropriate,
            'reviewed_at'   => now(),
        ]);

        $message = "Face rejected for {$user->name}.";

        // Inappropriate image -> add a warning. 3 warnings -> ban the account.
        if ($inappropriate) {
            $user->increment('face_warnings');
            $user->refresh();

            if ($user->face_warnings >= 3) {
                $user->update(['status' => 'rejected']); // account banned
                $message = "{$user->name} reached 3 warnings — account has been BANNED.";
                AuditLogService::log(
                    "BANNED {$user->name} — 3 inappropriate face uploads",
                    'Face Verification'
                );
            } else {
                $message = "{$user->name} flagged for inappropriate image (warning {$user->face_warnings}/3).";
                AuditLogService::log(
                    "Warning {$user->face_warnings}/3 to {$user->name} — inappropriate face upload",
                    'Face Verification'
                );
            }

            // delete the offending images
            Storage::disk('public')->deleteDirectory("faces/{$user->id}");
        } else {
            AuditLogService::log("Rejected face registration: {$user->name}", 'Face Verification');
        }

        return back()->with('success', $message);
    }

    /** Attach public image URLs to a registration row */
    private function withImages(FaceRegistration $r): FaceRegistration
    {
        $dir   = "faces/{$r->user_id}";
        $files = Storage::disk('public')->exists($dir)
            ? Storage::disk('public')->files($dir)
            : [];

        $r->image_urls = collect($files)
            ->filter(fn ($f) => preg_match('/\.(jpe?g|png)$/i', $f))
            ->map(fn ($f) => Storage::url($f))
            ->values()
            ->all();

        return $r;
    }
}