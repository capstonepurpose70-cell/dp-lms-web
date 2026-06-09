<?php

namespace App\Support;

class BadWordFilter
{
    public static function containsBadWord(string $text): bool
    {
        // Normalize: lowercase + common letter substitutions
        $normalized = strtolower($text);
        $normalized = strtr($normalized, [
            '@' => 'a', '4' => 'a', '3' => 'e', '1' => 'i',
            '0' => 'o', '$' => 's', '5' => 's', '7' => 't',
        ]);
        // Collapse repeated chars + strip non-letters so "p u t a" / "p.u.t.a" still match
        $normalized = preg_replace('/[^a-z]/', '', $normalized);

        foreach (config('badwords', []) as $word) {
            $clean = preg_replace('/[^a-z]/', '', strtolower($word));
            if ($clean !== '' && str_contains($normalized, $clean)) {
                return true;
            }
        }
        return false;
    }
}