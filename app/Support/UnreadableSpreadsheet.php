<?php

namespace App\Support;

use RuntimeException;

/** An uploaded spreadsheet that cannot be used; the message is safe to show to the admin as-is. */
final class UnreadableSpreadsheet extends RuntimeException
{
}
