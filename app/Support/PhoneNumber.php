<?php

namespace App\Support;

/**
 * PH mobile number — iisang format sa database: 09XXXXXXXXX.
 */
final class PhoneNumber
{
    /**
     * "+63 917-123-4567" / "639171234567" / "9171234567" / "0917 123 4567" → "09171234567".
     * NULL kapag hindi PH mobile, o may ibang karakter bukod sa digits, espasyo,
     * gitling, panaklong at "+" (para hindi mapagkamalang numero ang email).
     */
    public static function normalize(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '' || !preg_match('/^[\d\s\-+()]+$/', $input)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $input);

        if (str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return preg_match('/^09\d{9}$/', $digits) ? $digits : null;
    }
}
