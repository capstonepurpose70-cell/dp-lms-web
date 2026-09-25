<?php

namespace App\Support;

/**
 * Paghahambing ng pangalan na hindi apektado ng laki ng letra, kuwit, tuldok,
 * gitnang pangalan, pagkakasunod ("Dela Cruz, Juan" == "Juan Dela Cruz"),
 * accent (Peña == Pena), at suffix (Jr., III).
 *
 * Ginagamit ng LRN gate (OfficialLrn::matchesName) at ng parent linking.
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

    /** Hatiin ang pangalan sa malilinis na tokens para maikumpara. */
    public static function tokens(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return [];
        }

        // Alisin ang accents. Tahasang mapa muna: sa Windows (XAMPP), ang iconv
        // TRANSLIT ay gumagawa ng "Pe~na" imbes na "Pena", kaya nahahati ang token.
        $name = strtr($name, self::ACCENTS);

        // Fallback para sa ibang karakter na wala sa mapa
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
        return array_values(array_unique(array_filter(
            $tokens,
            fn ($t) => mb_strlen($t) > 1 && !in_array($t, self::IGNORED, true)
        )));
    }

    /**
     * TRUE kapag hindi bababa sa 2 token ang magkatugma (karaniwan: pangalan +
     * apelyido). Kung isang token lang ang opisyal, sapat na ang 1 tugma.
     * FALSE kapag walang opisyal na pangalan — walang maikukumpara.
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

    /** Tugma ba ang $given sa KAHIT ISA sa mga pangalan sa $candidates? */
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
