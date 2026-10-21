<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One attendance window opened by a subject teacher for a section + subject. */
class ClassAttendanceSession extends Model
{
    protected $fillable = [
        'teacher_id', 'section_id', 'subject_id', 'school_year', 'code',
        'on_time_minutes', 'late_minutes', 'opened_at', 'on_time_until', 'closes_at', 'closed_at',
    ];

    protected $casts = [
        'opened_at'       => 'datetime',
        'on_time_until'   => 'datetime',
        'closes_at'       => 'datetime',
        'closed_at'       => 'datetime',
        'on_time_minutes' => 'integer',
        'late_minutes'    => 'integer',
    ];

    protected $hidden = ['code']; // never leaks to students through JSON

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function records() { return $this->hasMany(ClassAttendanceRecord::class, 'session_id'); }

    /** Accepting marks right now (not finalized and before closes_at). */
    public function isOpen(): bool
    {
        return $this->closed_at === null && now()->lt($this->closes_at);
    }

    /** Status a student would get if they marked right now. */
    public function statusNow(): string
    {
        return now()->lte($this->on_time_until) ? 'present' : 'late';
    }

    public function scopeOpen($q)
    {
        return $q->whereNull('closed_at')->where('closes_at', '>', now());
    }
}
