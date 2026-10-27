<?php

namespace App\Support;

/**
 * Name comparison that ignores case, commas, periods, middle names,
 * order ("Dela Cruz, Juan" == "Juan Dela Cruz"), accents (Peña == Pena)
 * and suffixes (Jr., III).
 *
 * Used by the LRN gate (OfficialLrn::matchesName) and parent linking.
 */
final class NameMatcher
{
    private const IGNORED = ['JR', 'SR', 'II', 'III', 'IV', 'V'];

    private const ACCENTS = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
        'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'ñ' => 'n', 'Ñ' => 'N', 'ç' => 'c', 'Ç' => 'C',
    ];

    /** Split a name into clean tokens for comparison. */
    public static function tokens(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return [];
        }

        // Strip accents. Explicit map first: on Windows (XAMPP) iconv's TRANSLIT
        // produces "Pe~na" instead of "Pena", which splits the token.
        $name = strtr($name, self::ACCENTS);

        // Fallback for characters that are not in the map
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);
            if ($converted !== false) {
                $name = $converted;
            }
        }

        $name = mb_strtoupper($name, 'UTF-8');
        // Punctuation -> space (including commas and initials' periods)
        $name = preg_replace('/[^A-Z0-9\s]/', ' ', $name);

        $tokens = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Drop suffixes and single letters (middle initials)
        return array_values(array_unique(array_filter(
            $tokens,
            fn ($t) => mb_strlen($t) > 1 && !in_array($t, self::IGNORED, true)
        )));
    }

    /**
     * TRUE when at least 2 tokens match (usually first name + surname).
     * When the official name has a single token, 1 match is enough.
     * FALSE when there is no official name — nothing to compare.
     */
    public static function matches(?string $official, ?string $given): bool
    {
        $officialTokens = self::tokens($official);

        if (count($officialTokens) === 0) {
            return false;
        }

        $overlap = count(array_intersect($officialTokens, self::tokens($given)));

        return $overlap >= min(2, count($officialTokens));
    }

    /** Does $given match ANY of the names in $candidates? */
    public static function matchesAny(?string $given, array $candidates): bool
    {
        foreach ($candidates as $candidate) {
            if (self::matches($candidate, $given)) {
                return true;
            }
        }

        return false;
    }
}
