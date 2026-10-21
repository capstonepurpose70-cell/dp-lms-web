<?php

namespace App\Notifications;

use App\Models\ClassAttendanceRecord;
use Illuminate\Notifications\Notification;

/** To parents: "Juan — PRESENT in Mathematics (7:32 AM)". In-app; push is sent by ClassAttendanceService. */
class ClassAttendanceResult extends Notification
{
    public function __construct(public ClassAttendanceRecord $record, public bool $updated = false) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $r = $this->record;
        $s = $r->session;

        return [
            'title'        => ClassAttendanceResultText::title($r, $this->updated),
            'subject'      => ucfirst($r->status),
            'instructor'   => ClassAttendanceResultText::when($r),
            'type'         => 'class_attendance',
            'url'          => '/parent/child-records',

            'student_id'   => $r->student_id,
            'student_name' => $r->student?->name,
            'status'       => $r->status,                       // present | late | absent
            'subject_name' => $s?->subject?->name,
            'session_id'   => $r->session_id,
            'marked_at'    => $r->marked_at?->toIso8601String(), // null when absent
            'updated'      => $this->updated,                    // true = teacher corrected an earlier result
        ];
    }
}
