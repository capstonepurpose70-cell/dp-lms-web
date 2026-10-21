<?php

namespace App\Notifications;

use App\Models\ClassAttendanceRecord;

/** Shared wording for the parent's in-app notification and push (one place, same text). */
final class ClassAttendanceResultText
{
    public static function title(ClassAttendanceRecord $r, bool $updated = false): string
    {
        $name = $r->student?->name ?? 'Your child';
        $subj = $r->session?->subject?->name ?? 'class';
        $word = match ($r->status) { 'present' => 'present', 'late' => 'late', default => 'absent' };

        return ($updated ? 'Updated: ' : '') . "{$name} was {$word} in {$subj}";
    }

    public static function when(ClassAttendanceRecord $r): string
    {
        $s = $r->session;
        if ($r->status === 'absent') {
            return 'Did not check in · ' . ($s?->opened_at?->format('M j, Y g:i A') ?? '');
        }
        return 'Checked in at ' . ($r->marked_at?->format('g:i A') ?? '') . ' · ' . ($r->marked_at?->format('M j, Y') ?? '');
    }
}
