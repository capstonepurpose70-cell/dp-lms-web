<?php

namespace App\Support;

/**
 * PH mobile number — one format in the database: 09XXXXXXXXX.
 */
final class PhoneNumber
{
    /**
     * "+63 917-123-4567" / "639171234567" / "9171234567" / "0917 123 4567" → "09171234567".
     * NULL when it is not a PH mobile number, or has characters other than digits, spaces,
     * dashes, parentheses and "+" (so an email is never mistaken for a number).
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
