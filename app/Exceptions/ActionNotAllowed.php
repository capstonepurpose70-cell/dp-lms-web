<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown by services when an action is not allowed
 * (e.g. not your student, the quiz is closed, score above the maximum).
 *
 * Web controller → back()->withErrors(...)
 * API controller → JSON { ok: false, message } with $status (403 / 422)
 */
class ActionNotAllowed extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 403)
    {
        parent::__construct($message);
    }

    /** Same JSON shape the API already returned for these errors. */
    public function toJson()
    {
        return response()->json(['ok' => false, 'message' => $this->getMessage()], $this->status);
    }
}
