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

    /**
     * Announcements a student/parent in these sections should see:
     *  - posted to one of these sections, or
     *  - posted to "all my sections" (section_id = null) by a teacher of one of these sections.
     *
     * Dati: lahat ng walang section ay school-wide — at walang section selector ang form,
     * kaya bawat announcement ng bawat teacher ay lumalabas sa buong paaralan.
     * Teachers lang ang gumagawa ng announcement (walang school-wide na feature).
     */
    public function scopeVisibleToSections($query, iterable $sectionIds)
    {
        $ids = collect($sectionIds)->filter()->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');   // no section yet → no class announcements
        }

        $teacherIds = $ids->flatMap(fn ($id) => User::teacherIdsForSection($id))->unique()->values()->all();

        return $query->where(function ($q) use ($ids, $teacherIds) {
            $q->whereIn('section_id', $ids->all());
            if ($teacherIds) {
                $q->orWhere(fn ($w) => $w->whereNull('section_id')->whereIn('user_id', $teacherIds));
            }
        });
    }

    /** "Grade 7 – AQUINO" or "All my sections" — for the teacher's own list. */
    public function getTargetLabelAttribute(): string
    {
        return $this->section
            ? "Grade {$this->section->grade_level} – {$this->section->name}"
            : 'All my sections';
    }
}