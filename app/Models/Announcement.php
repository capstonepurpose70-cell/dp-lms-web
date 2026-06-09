<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'audience',
        'section_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForAudience($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('audience', 'all')
              ->orWhere('audience', $role . 's');
        });
    }
}