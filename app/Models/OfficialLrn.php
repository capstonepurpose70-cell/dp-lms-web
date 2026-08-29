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

    // ─── Name verification ────────────────────────────────────────────────────

    /**
     * Hatiin ang pangalan sa malilinis na tokens para maikumpara.
     * Hindi apektado ng: laki ng letra, kuwit, tuldok, gitnang pangalan,
     * pagkakasunod ("Dela Cruz, Juan" == "Juan Dela Cruz"), at suffix (Jr., III).
     */
    public static function nameTokens(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return [];
        }

        // Alisin ang accents kung available ang intl/iconv
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);
            if ($converted !== false) {
                $name = $converted;
            }
        }

        $name = mb_strtoupper($name, 'UTF-8');
        // Bantas -> espasyo (kasama ang kuwit at tuldok ng initial)
        $name = preg_replace('/[^A-Z0-9\s]/', ' ', $name);

        $tokens = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Alisin ang suffix at nag-iisang letra (middle initial)
        $ignore = ['JR', 'SR', 'II', 'III', 'IV', 'V'];

        return array_values(array_unique(array_filter(
            $tokens,
            fn($t) => mb_strlen($t) > 1 && !in_array($t, $ignore, true)
        )));
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
        $official = static::nameTokens($this->student_name);

        // Walang pangalan sa masterlist — hindi ito maaaring i-verify.
        if (count($official) === 0) {
            return true;
        }

        $given   = static::nameTokens($registrantName);
        $overlap = count(array_intersect($official, $given));

        return $overlap >= min(2, count($official));
    }
}