<?php

namespace App\Rules;

use App\Support\BadWordFilter;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for chat and comments: rejects profanity.
 * Usage: 'body' => ['required', 'string', new NoBadWords]
 */
class NoBadWords implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && BadWordFilter::containsBadWord($value)) {
            $fail('Please avoid inappropriate language.');
        }
    }
}
