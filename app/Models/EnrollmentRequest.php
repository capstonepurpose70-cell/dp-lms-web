<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'grade_level',
        'school_year',
        'student_type',
        'full_name',
        'age',
        'birthdate',
        'gender',
        'address',
        'mother_name',
        'father_name',
        'guardian_name',
        'guardian_contact',
        'last_school',
        'last_grade_completed',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
    ];

    protected $casts = [
        'birthdate'   => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}