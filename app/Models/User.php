<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'otp_verified'      => 'boolean',
    ];

    // ─── Login helpers ────────────────────────────────────────────────────────

    /**
     * Hanapin ang user gamit ang EMAIL o LRN — kung ano ang inilagay sa
     * login form. Puro digits (12) = LRN; kung hindi, email ang ituturing.
     * Iisang lugar lang ang lohika para pareho ang web at mobile API.
     */
    public static function resolveByIdentifier(?string $identifier): ?self
    {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            return null;
        }

        // LRN: eksaktong 12 digits (tugma sa masterlist at register rules)
        if (preg_match('/^\d{12}$/', $identifier)) {
            return static::where('lrn', $identifier)->first();
        }

        return static::where('email', $identifier)->first();
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
        return $this->hasMany(AuditLog::class);
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