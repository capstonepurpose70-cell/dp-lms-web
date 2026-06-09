<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'age',
        'birthdate',
        'gender',
        'address',
        'mother_name',
        'father_name',
        'guardian_name',
        'guardian_contact',
        'student_type',
        'last_school',
        'last_grade_completed',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}