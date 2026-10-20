<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * "Your child arrived at school" — in-app (bell + /api/notifications).
 * Ang push (FCM) ay ipinapadala ng ParentNotifier kasabay nito.
 *
 * Ang title/subject/instructor/type/url ay parehong hugis ng ActivityAssigned
 * para gumana sa iisang bell UI; ang ibang key ay para sa Flutter app.
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
            // Bell UI (kapareho ng ActivityAssigned)
            'title'        => "{$this->student->name} arrived at school",
            'subject'      => $this->attendance->status === 'late' ? 'Late' : 'On time',
            'instructor'   => $at?->format('M j, Y g:i A'),
            'type'         => 'arrival',
            'url'          => '/parent/child-records',

            // Para sa Flutter
            'student_id'   => $this->student->id,
            'student_name' => $this->student->name,
            'status'       => $this->attendance->status,
            'attended_at'  => $at?->toIso8601String(),
            'attendance_id'=> $this->attendance->id,
        ];
    }
}
