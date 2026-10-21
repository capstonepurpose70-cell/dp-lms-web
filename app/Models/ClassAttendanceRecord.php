<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student's result for one class attendance session: present | late | absent. */
class ClassAttendanceRecord extends Model
{
    public const STATUSES = ['present', 'late', 'absent'];

    protected $fillable = ['session_id', 'student_id', 'status', 'marked_at', 'marked_by'];

    protected $casts = ['marked_at' => 'datetime'];

    public function session() { return $this->belongsTo(ClassAttendanceSession::class, 'session_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
