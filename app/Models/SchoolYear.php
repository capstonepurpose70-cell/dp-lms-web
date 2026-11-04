<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    protected $fillable = [
        'label',
        'starts_at',
        'ends_at',
        'is_active',
        'current_semester',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'is_active' => 'boolean',
        'current_semester' => 'integer',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Relationships ───────────────────────────────────────────────────────────

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    // ── Static helper ───────────────────────────────────────────────────────────

    /**
     * Get the currently active school year.
     * Returns null gracefully if none is set — callers must handle null.
     */
    public static function current(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /** The semester Senior High is in now (1 or 2); 1 when no school year is active. */
    public static function currentSemester(): int
    {
        return (int) (static::current()?->current_semester ?: 1) === 2 ? 2 : 1;
    }
}