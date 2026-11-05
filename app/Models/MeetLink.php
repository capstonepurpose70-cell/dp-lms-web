<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Google Meet link of one subject in one section. Read and written through MeetLinkService. */
class MeetLink extends Model
{
    protected $fillable = [
        'section_id',
        'subject_id',
        'school_year',
        'url',
        'updated_by',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
