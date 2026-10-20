<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'student_name',
        'section_id',   // dati wala rito → tahimik na hindi nase-save ang section ng scan
        'status',
        'source',
        'attended_at',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}