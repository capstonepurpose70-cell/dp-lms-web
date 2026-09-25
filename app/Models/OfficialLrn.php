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

    /** Ang user account na naka-claim ng LRN na ito (null = hindi pa nagagamit). */
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
     * Tugma ba ang pangalan ng nagrerehistro sa nasa masterlist?
     *
     * TRUE kapag: (a) walang student_name sa masterlist — walang maikukumpara,
     * kaya pinapayagan (admin approval pa rin ang huling harang); o
     * (b) hindi bababa sa 2 token ang magkatugma (karaniwan: pangalan + apelyido).
     * Kung isang token lang ang nasa masterlist, sapat na ang 1 tugma.
     */
    public function matchesName(?string $registrantName): bool
    {
        // Walang pangalan sa masterlist — hindi ito maaaring i-verify.
        if (count(NameMatcher::tokens($this->student_name)) === 0) {
            return true;
        }

        return NameMatcher::matches($this->student_name, $registrantName);
    }
}