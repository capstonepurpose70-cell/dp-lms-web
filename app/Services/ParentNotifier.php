<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Notifications\ChildArrived;
use Illuminate\Support\Facades\Log;

/**
 * Notifications sa magulang.
 * HINDI kailanman nagtatapon ng error — ang attendance ay naka-save na
 * bago tawagin ito, kaya hindi masisira ng palyadong push ang scan.
 */
class ParentNotifier
{
    public function __construct(private PushNotificationService $push) {}

    /** Raspberry Pi scan → "Juan arrived at school · 7:12 AM" sa magulang. */
    public function childArrived(User $student, Attendance $attendance): void
    {
        try {
            $parent = $student->parent;
            if (!$parent || $parent->status !== 'approved') {
                return;
            }

            $parent->notify(new ChildArrived($student, $attendance));

            $time = $attendance->attended_at?->format('g:i A');
            $date = $attendance->attended_at?->format('M j, Y');
            $late = $attendance->status === 'late' ? ' (late)' : '';

            $this->push->sendToUsers(
                [$parent->id],
                "{$student->name} arrived at school",
                "Checked in at {$time}{$late} on {$date}.",
                [
                    'type'          => 'arrival',
                    'student_id'    => $student->id,
                    'student_name'  => $student->name,
                    'status'        => $attendance->status,
                    'attended_at'   => $attendance->attended_at?->toIso8601String(),
                    'attendance_id' => $attendance->id,
                ],
            );
        } catch (\Throwable $e) {
            Log::warning('Arrival notification failed (attendance still saved): ' . $e->getMessage());
        }
    }
}
