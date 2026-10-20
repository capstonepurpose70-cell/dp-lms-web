<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Itinatapon ng mga service kapag hindi puwede ang isang aksyon
 * (hal. hindi mo estudyante, sarado na ang quiz, lampas sa max score).
 *
 * Web controller → back()->withErrors(...)
 * API controller → JSON { ok: false, message } na may $status (403 / 422)
 */
class ActionNotAllowed extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 403)
    {
        parent::__construct($message);
    }

    /** JSON na kapareho ng dating hugis ng mga API response. */
    public function toJson()
    {
        return response()->json(['ok' => false, 'message' => $this->getMessage()], $this->status);
    }
}
