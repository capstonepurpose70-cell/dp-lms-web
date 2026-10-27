<?php

namespace App\Models;

use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'status',
    'lrn',
    'employee_id',
    'grade_level',
    'birthdate',
    'section_id',
    'parent_id',
    'child_name',
    'contact_number',
    'phone_number',
    'otp_verified',
    'invite_token', 'invite_expires_at', 'must_change_password',
];

    protected $hidden = [
        'password',
        'remember_token',
        'invite_token',   // would otherwise show up in GET /api/user
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate'         => 'date',
        'password'          => 'hashed',
        'otp_verified'      => 'boolean',
    ];

    // ─── Login helpers ────────────────────────────────────────────────────────

    /**
     * Find a user by EMAIL, LRN or MOBILE NUMBER — whatever was typed in the
     * login form. 12 digits = LRN; PH mobile (09…, +63…) = contact_number;
     * anything else is treated as an email.
     * One place for the rule so the web and the mobile API agree.
     */
    public static function resolveByIdentifier(?string $identifier): ?self
    {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            return null;
        }

        // LRN: exactly 12 digits (same as the masterlist and register rules).
        // Checked before mobile, so "639…" without "+" counts as an LRN.
        if (preg_match('/^\d{12}$/', $identifier)) {
            return static::where('lrn', $identifier)->first();
        }

        if (($mobile = PhoneNumber::normalize($identifier)) !== null) {
            return static::where('contact_number', $mobile)->first();
        }

        return static::where('email', $identifier)->first();
    }

    /** Dashboard route name for the role. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'student' => 'student.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent'  => 'parent.dashboard',
            default   => 'login',
        };
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /** The student's active parent-link code (one per student). */
    public function parentLinkCode()
    {
        return $this->hasOne(ParentLinkCode::class, 'student_id');
    }

    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function quizAnswers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    public function auditLogs()
    {
        // audit_logs.user_id also stores ADMIN ids (separate `admins` table) — exclude
        // role=admin rows so an admin's actions never appear in a same-id user's history.
        return $this->hasMany(AuditLog::class)
            ->where(fn ($q) => $q->whereNull('role')->orWhere('role', '!=', 'admin'));
    }

    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // ─── Section scope (one source for every controller) ──────────────────────

    /** The student's section: active enrollment first, then users.section_id. */
    public function studentSectionId(): ?int
    {
        $id = $this->studentEnrollment?->section_id ?? $this->section_id;

        return $id ? (int) $id : null;
    }

    /**
     * Every section the teacher handles — as SUBJECT teacher
     * (teacher_subjects), as ADVISER (sections.adviser_id — used by the
     * Sections page and enrollment review), or via teacher_assignments.
     * One source so every page shows an adviser their own class.
     */
    public function teachingSectionIds(): \Illuminate\Support\Collection
    {
        return TeacherSubject::where('user_id', $this->id)->pluck('section_id')
            ->merge(TeacherAssignment::where('user_id', $this->id)->pluck('section_id'))
            ->merge(Section::where('adviser_id', $this->id)->pluck('id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /** Teacher ids of a section: subject teachers + adviser. */
    public static function teacherIdsForSection(?int $sectionId): \Illuminate\Support\Collection
    {
        if (!$sectionId) {
            return collect();
        }

        return TeacherSubject::where('section_id', $sectionId)->pluck('user_id')
            ->merge(TeacherAssignment::where('section_id', $sectionId)->pluck('user_id'))
            ->merge(Section::whereKey($sectionId)->pluck('adviser_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /** Ids of students in these sections (directly or through enrollment). */
    public static function studentIdsInSections(iterable $sectionIds): \Illuminate\Support\Collection
    {
        $ids = collect($sectionIds)->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return static::where('role', 'student')
            ->where(function ($q) use ($ids) {
                $q->whereIn('section_id', $ids)
                  ->orWhereHas('studentEnrollment', fn ($e) => $e->whereIn('section_id', $ids));
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }
    public function teacherSubjects()
{
    return $this->hasMany(TeacherSubject::class);
}

public function studentEnrollment()
{
    return $this->hasOne(StudentEnrollment::class)->where('status', 'enrolled')->latest();
}

public function studentEnrollments()
{
    return $this->hasMany(StudentEnrollment::class);
}

public function enrollmentRequest()
{
    return $this->hasOne(EnrollmentRequest::class)->latest();
}

public function enrollmentRequests()
{
    return $this->hasMany(EnrollmentRequest::class);
}

public function studentProfile()
{
    return $this->hasOne(StudentProfile::class);
}
// Get subjects count based on grade level
public function getSubjectCountAttribute(): int
{
    $grade = $this->grade_level;
    if (in_array($grade, ['11', '12'])) return 9;
    if (in_array($grade, ['7', '8', '9', '10'])) return 8;
    return 8;
}
}