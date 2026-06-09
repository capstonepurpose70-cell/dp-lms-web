<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    protected $fillable = [
        'subject_id',
        'user_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'quarter',
        'week',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Interaction relationships (additive) ─────────────────────────────────
    public function views()
    {
        return $this->hasMany(MaterialView::class);
    }

    public function likes()
    {
        return $this->hasMany(MaterialLike::class);
    }

    public function comments()
    {
        return $this->hasMany(MaterialComment::class);
    }
}