<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * "Your child arrived at school" — in-app (bell + /api/notifications).
 * The push (FCM) is sent by ParentNotifier at the same time.
 *
 * title/subject/instructor/type/url have the same shape as ActivityAssigned
 * so the one bell UI works; the other keys are for the Flutter app.
 */
class ChildArrived extends Notification
{
    public function __construct(
        public User $student,
        public Attendance $attendance,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $at = $this->attendance->attended_at;

        return [
            // Bell UI (same as ActivityAssigned)
            'title'        => "{$this->student->name} arrived at school",
            'subject'      => $this->attendance->status === 'late' ? 'Late' : 'On time',
            'instructor'   => $at?->format('M j, Y g:i A'),
            'type'         => 'arrival',
            'url'          => '/parent/child-records',

            // For Flutter
            'student_id'   => $this->student->id,
            'student_name' => $this->student->name,
            'status'       => $this->attendance->status,
            'attended_at'  => $at?->toIso8601String(),
            'attendance_id'=> $this->attendance->id,
        ];
    }
}
