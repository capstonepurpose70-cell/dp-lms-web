<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StudentEnrollment;

class Section extends Model
{
    protected $fillable = [
        'name',
        'grade_level',
        'adviser_id',
        'school_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    /** Ang adviser (teacher) na hawak ng section na ito. */
    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }
}