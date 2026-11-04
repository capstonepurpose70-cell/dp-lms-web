<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row of the Faculty Master List (official teacher list); claimed_by = the teacher account made from it. */
class FacultyRecord extends Model
{
    protected $fillable = ['employee_id', 'name', 'section_id', 'claimed_by'];

    protected $casts = ['left_at' => 'datetime'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
