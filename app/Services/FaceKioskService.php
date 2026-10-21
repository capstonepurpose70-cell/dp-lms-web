<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\FaceKioskCode;
use App\Models\FaceRegistration;
use App\Models\Section;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Face registration at the Raspberry Pi kiosk.
 *
 *  1. Admin or the student's ADVISER clicks "Show QR" → one-time QR (10 min, single use).
 *     A new QR for the same student cancels the previous one. Only the SHA-256 hash is stored.
 *  2. The Pi scans the QR → lookup() shows "Hi Juan!" (does not use the code).
 *  3. The Pi captures photos → register() saves them to THAT student, already approved
 *     (a staff member issued the QR in person), and uses up the code.
 *  4. The Pi reloads GET /api/faces/approved → the gate recognizes the student.
 */
class FaceKioskService
{
    public const PREFIX      = 'DPLMS-FACE:';
    public const TTL_MINUTES = 10;
    public const MIN_IMAGES  = 3;
    public const MAX_IMAGES  = 15;

    public function __construct(private FaceRegistrationService $faces) {}

    // ── Staff side ──────────────────────────────────────────────────────────

    /** Advisory students of a teacher (only these can get a QR from that teacher). */
    public function adviserStudents(User $teacher): Collection
    {
        $sectionIds = Section::where('adviser_id', $teacher->id)->where('is_active', true)->pluck('id');

        return $this->studentsWithFaceStatus($sectionIds);
    }

    /** Approved students of the given sections + their latest face status. */
    public function studentsWithFaceStatus(iterable $sectionIds): Collection
    {
        $ids = User::studentIdsInSections($sectionIds);
        $students = User::with('section:id,name,grade_level')->whereIn('id', $ids)->where('status', 'approved')
            ->orderBy('name')->get(['id', 'name', 'lrn', 'section_id', 'face_warnings']);
        $faces = FaceRegistration::whereIn('user_id', $students->pluck('id'))->latest('id')->get()->unique('user_id')->keyBy('user_id');

        return $students->map(function ($s) use ($faces) {
            $s->face_status = $faces[$s->id]->status ?? null;    // approved | pending | rejected | null
            return $s;
        });
    }

    public function canTeacherIssue(User $teacher, User $student): bool
    {
        $sid = $student->studentSectionId();
        return $sid !== null && Section::where('id', $sid)->where('adviser_id', $teacher->id)->exists();
    }

    /** @return array{id:int, token:string, qr_svg:string, expires_at:\Carbon\Carbon, student:User} */
    public function issue(User $student, string $byRole, ?int $byId): array
    {
        if ($student->role !== 'student' || $student->status !== 'approved') {
            throw new ActionNotAllowed('Only approved student accounts can register a face.', 422);
        }
        $this->faces->assertCanRegister($student); // enrolled in a section, not blocked (3 warnings)

        $token = self::PREFIX . Str::random(40);

        $code = DB::transaction(function () use ($student, $byRole, $byId, $token) {
            FaceKioskCode::where('student_id', $student->id)->whereNull('used_at')->delete(); // cancel older QR
            return FaceKioskCode::create([
                'student_id'     => $student->id,
                'token_hash'     => hash('sha256', $token),
                'issued_by_role' => $byRole,
                'issued_by_id'   => $byId,
                'expires_at'     => now()->addMinutes(self::TTL_MINUTES),
            ]);
        });

        AuditLogService::log("Issued face kiosk QR: {$student->name}", 'Face Verification');

        $svg = (new Writer(new ImageRenderer(new RendererStyle(260, 1), new SvgImageBackEnd())))->writeString($token);

        return ['id' => $code->id, 'token' => $token, 'qr_svg' => $svg, 'expires_at' => $code->expires_at, 'student' => $student];
    }

    /** For the QR window: has the Pi finished? */
    public function status(FaceKioskCode $code): string
    {
        if ($code->used_at) {
            return 'registered';
        }
        if (now()->gte($code->expires_at)) {
            return 'expired';
        }
        return FaceKioskCode::whereKey($code->id)->exists() ? 'waiting' : 'cancelled';
    }

    // ── Device (Raspberry Pi) side ──────────────────────────────────────────

    public function lookup(string $scanned): FaceKioskCode
    {
        $code = $this->find($scanned);
        if (!$code || !$code->isUsable()) {
            throw new ActionNotAllowed('This QR code is invalid, already used, or expired. Ask your adviser for a new one.', 422);
        }

        return $code->load('student.section');
    }

    /**
     * Save the photos to the QR's student (approved) and use up the code.
     * The code row is locked for the whole operation → two uploads with one QR can never both succeed.
     * If saving fails, nothing is committed and the QR stays usable for a retry.
     *
     * @param \Illuminate\Http\UploadedFile[] $files
     */
    public function register(string $scanned, array $files, ?string $ip): User
    {
        $hash = $this->hashOf($scanned);
        if ($hash === null) {
            throw new ActionNotAllowed('This QR code is invalid, already used, or expired. Ask your adviser for a new one.', 422);
        }

        $student = DB::transaction(function () use ($hash, $files, $ip) {
            $code = FaceKioskCode::where('token_hash', $hash)->lockForUpdate()->first();
            if (!$code || !$code->isUsable()) {
                throw new ActionNotAllowed('This QR code is invalid, already used, or expired. Ask your adviser for a new one.', 422);
            }
            $student = $code->student;
            if (!$student || $student->status !== 'approved') {
                throw new ActionNotAllowed('This student account is not active.', 422);
            }

            $this->faces->replaceImages($student, $files, true);   // checks section + warnings again
            $code->update(['used_at' => now(), 'used_ip' => $ip]);

            return $student;
        });

        AuditLogService::log("Face registered at kiosk: {$student->name}", 'Face Verification', count($files) . ' photos', $student);

        return $student;
    }

    private function find(string $scanned): ?FaceKioskCode
    {
        $hash = $this->hashOf($scanned);
        return $hash ? FaceKioskCode::where('token_hash', $hash)->first() : null;
    }

    /** Accepts exactly what the QR contains ("DPLMS-FACE:" + 40 chars). */
    private function hashOf(string $scanned): ?string
    {
        $scanned = trim($scanned);
        if (!str_starts_with($scanned, self::PREFIX) || strlen($scanned) !== strlen(self::PREFIX) + 40) {
            return null;
        }
        return hash('sha256', $scanned);
    }
}
