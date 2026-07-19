<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialLrn extends Model
{
    protected $fillable = [
        'lrn',
        'student_name',
        'grade_level',
        'claimed_by',
    ];

    /** Ang user account na naka-claim ng LRN na ito (null = hindi pa nagagamit). */
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}