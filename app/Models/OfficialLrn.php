<?php

namespace App\Models;

use App\Support\NameMatcher;
use Illuminate\Database\Eloquent\Model;

class OfficialLrn extends Model
{
    protected $fillable = [
        'lrn',
        'student_name',
        'grade_level',
        'claimed_by',
    ];

    /** The user account that claimed this LRN (null = not used yet). */
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    // ─── Name verification ────────────────────────────────────────────────────

    /** @see NameMatcher::tokens() */
    public static function nameTokens(?string $name): array
    {
        return NameMatcher::tokens($name);
    }

    /**
     * Does the registering student's name match the masterlist?
     *
     * TRUE when: (a) the masterlist has no student_name — nothing to compare,
     * so it is allowed (admin approval is still the last check); or
     * (b) at least 2 tokens match (usually first name + surname).
     * When the masterlist has a single token, 1 match is enough.
     */
    public function matchesName(?string $registrantName): bool
    {
        // No name in the masterlist — this cannot be verified.
        if (count(NameMatcher::tokens($this->student_name)) === 0) {
            return true;
        }

        return NameMatcher::matches($this->student_name, $registrantName);
    }
}