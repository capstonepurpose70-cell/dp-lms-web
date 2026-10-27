<?php

namespace App\Support;

/**
 * Bad-word filter for chat and comments.
 *
 * Checks WHOLE words, not substrings — so "gagawin" (gaga), "computation" (puta)
 * and "tangan" (tanga) are not blocked — but still catches the usual tricks:
 *   leetspeak  g@g0 → gago        repeated     putaaaa → puta
 *   dotted     p.u.t.a → puta     spaced       p u t a → puta
 */
class BadWordFilter
{
    private const LEET = [
        '@' => 'a', '4' => 'a', '3' => 'e', '1' => 'i', '!' => 'i',
        '0' => 'o', '$' => 's', '5' => 's', '7' => 't',
    ];

    public static function containsBadWord(?string $text): bool
    {
        $text = (string) $text;
        if (trim($text) === '') {
            return false;
        }

        $config  = config('badwords', []);
        $words   = static::cleanList($config['words'] ?? []);
        $stems   = static::cleanList($config['stems'] ?? []);
        $phrases = array_map(fn ($p) => static::normalizeText($p), $config['phrases'] ?? []);
        $allow   = array_map(fn ($p) => static::normalizeText($p), $config['allow'] ?? []);

        $normalized = static::normalizeText($text);
        foreach ($allow as $ok) {
            if ($ok !== '') {
                $normalized = str_replace($ok, ' ', $normalized);
            }
        }

        // Parirala: "hayop ka"
        foreach ($phrases as $phrase) {
            if ($phrase !== '' && preg_match('/(^| )' . preg_quote($phrase, '/') . '( |$)/', $normalized)) {
                return true;
            }
        }

        foreach (static::candidateWords($normalized) as $word) {
            foreach ($words as $bad) {
                if ($word === $bad || $word === $bad . 'ng') {
                    return true;
                }
            }
            foreach ($stems as $stem) {
                if (str_starts_with($word, $stem)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** lowercase, no accents, leetspeak → letters, anything else → space */
    private static function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, ['ñ' => 'n', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
        $text = strtr($text, self::LEET);
        $text = preg_replace('/[^a-z.\s]/', ' ', $text);

        // "p.u.t.a" → "puta" (dots between letters)
        $text = preg_replace('/(?<=[a-z])\.(?=[a-z])/', '', $text);
        $text = str_replace('.', ' ', $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Words to check: every word (repeated letters collapsed), plus runs of
     * single letters ("p u t a") joined together.
     */
    private static function candidateWords(string $normalized): array
    {
        $tokens = $normalized === '' ? [] : explode(' ', $normalized);
        $out    = [];
        $run    = '';

        foreach ($tokens as $t) {
            $out[] = static::collapse($t);

            if (strlen($t) === 1) {
                $run .= $t;
            } else {
                if (strlen($run) >= 3) {
                    $out[] = static::collapse($run);
                }
                $run = '';
            }
        }
        if (strlen($run) >= 3) {
            $out[] = static::collapse($run);
        }

        return array_unique($out);
    }

    /**
     * "putaaaa" → "puta", "fuuuck" → "fuck" (repeated letters → 1).
     * Applied to BOTH sides (message and list), so "asshole" = "ashole".
     */
    private static function collapse(string $word): string
    {
        return preg_replace('/([a-z])\1+/', '$1', $word);
    }

    private static function cleanList(array $list): array
    {
        return array_values(array_filter(array_map(
            fn ($w) => static::collapse(preg_replace('/[^a-z]/', '', mb_strtolower((string) $w))),
            $list
        )));
    }
}
