<?php

namespace App\Notifications;

use App\Models\ClassAttendanceSession;
use Illuminate\Notifications\Notification;

/** To students: "Attendance is open — Mathematics". In-app (bell + /api/notifications); push is sent by ClassAttendanceService. */
class AttendanceOpened extends Notification
{
    public function __construct(public ClassAttendanceSession $session) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $s = $this->session;

        return [
            // Bell UI keys (same shape as ActivityAssigned)
            'title'         => 'Attendance is open — ' . ($s->subject?->name ?? 'Class'),
            'subject'       => 'Present until ' . $s->on_time_until->format('g:i A'),
            'instructor'    => $s->teacher?->name ?? '',
            'type'          => 'attendance_open',
            'url'           => '/student/attendance',

            // For the Flutter app
            'session_id'    => $s->id,
            'subject_name'  => $s->subject?->name,
            'section_name'  => $s->section?->name,
            'on_time_until' => $s->on_time_until->toIso8601String(),
            'closes_at'     => $s->closes_at->toIso8601String(),
        ];
    }
}
