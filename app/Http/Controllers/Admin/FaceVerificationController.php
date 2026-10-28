<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceRegistration;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\FacePhotos;
use Illuminate\Http\Request;

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

        // For "Register a face at the kiosk" (one-time QR per student)
        $sections = \App\Models\Section::where('is_active', true)->get(['id', 'name', 'grade_level'])
            ->sortBy(fn ($s) => sprintf('%02d|%s', (int) $s->grade_level, mb_strtolower($s->name)))->values();

        // Same filter as the Pi sync (approvedFaces): approved student accounts only
        $studentCount    = User::where('role', 'student')->where('status', 'approved')->count();
        $registeredCount = FaceRegistration::where('status', 'approved')
            ->whereHas('user', fn ($q) => $q->where('role', 'student')->where('status', 'approved'))
            ->count();

        return view('admin.face-verification.index', compact('pending', 'reviewed', 'sections', 'studentCount', 'registeredCount'));
    }

    /** Approve a face set */
    public function approve(FaceRegistration $registration)
    {
        // Guard: never approve an empty image set — the Raspberry Pi would have
        // nothing to recognize. Ask the student to re-register instead.
        if (! FacePhotos::has($registration->user_id)) {
            return back()->with(
                'error',
                "No face images found for {$registration->user->name}. Please ask them to register again."
            );
        }

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
            'reject_reason' => $data['reason'] ?? 'The photo is unclear or invalid.',
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
            FacePhotos::delete($user->id);
        } else {
            AuditLogService::log("Rejected face registration: {$user->name}", 'Face Verification');
        }

        return back()->with('success', $message);
    }

    // ── Kiosk QR (Raspberry Pi face registration) ───────────────────────────

    /** GET /admin/face/kiosk/students?section_id= — students of a section with their face status. */
    public function kioskStudents(Request $request, \App\Services\FaceKioskService $kiosk)
    {
        $data = $request->validate(['section_id' => 'required|integer|exists:sections,id']);

        return response()->json($kiosk->studentsWithFaceStatus([$data['section_id']])->map(fn ($s) => [
            'id'          => $s->id,
            'name'        => $s->name,
            'lrn'         => $s->lrn,
            'face_status' => $s->face_status,
            'blocked'     => (int) $s->face_warnings >= 3,
        ])->values());
    }

    /** POST /admin/face/kiosk/{student} — one-time QR (10 min). */
    public function kioskIssue(User $student, \App\Services\FaceKioskService $kiosk)
    {
        try {
            $r = $kiosk->issue($student, 'admin', auth('admin')->id());
        } catch (\App\Exceptions\ActionNotAllowed $e) {
            return $e->toJson();
        }

        return response()->json([
            'ok'          => true,
            'code_id'     => $r['id'],
            'qr_svg'      => $r['qr_svg'],
            'student'     => $student->name,
            'expires_at'  => $r['expires_at']->toIso8601String(),
            'server_time' => now()->toIso8601String(),
            'status_url'  => route('admin.face.kiosk.status', $r['id']),
        ]);
    }

    /** GET /admin/face/kiosk/codes/{code} — waiting | registered | expired | cancelled */
    public function kioskStatus(int $code, \App\Services\FaceKioskService $kiosk)
    {
        $row = \App\Models\FaceKioskCode::find($code);

        return response()->json(['status' => $row ? $kiosk->status($row) : 'cancelled']);
    }

    /** GET /admin/face/photos/{user}/{file} — the photos are private, so the review page loads them here */
    public function photo(User $user, string $file)
    {
        return FacePhotos::response($user->id, $file) ?? abort(404);
    }

    /** Attach the admin-only image URLs to a registration row */
    private function withImages(FaceRegistration $r): FaceRegistration
    {
        $r->image_urls = collect(FacePhotos::names($r->user_id))
            ->map(fn ($name) => route('admin.face.photo', [$r->user_id, $name]))
            ->all();

        return $r;
    }
}