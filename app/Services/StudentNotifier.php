<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\ActivityAssigned;
use Illuminate\Support\Facades\Log;

/**
 * In-app (database) + FCM push notifications to students.
 *
 * One place for the web and the mobile API.
 * NEVER throws: when a notification fails, the material / quiz / grade
 * is still saved.
 */
class StudentNotifier
{
    public function __construct(private PushNotificationService $push) {}

    /** New learning material — everyone enrolled in the subject's grade level. */
    public function newMaterial(LearningMaterial $material, Subject $subject, User $teacher): void
    {
        $this->safely('Material', function () use ($material, $subject, $teacher) {
            $students = User::where('role', 'student')
                ->where('status', 'approved')
                ->where('grade_level', $subject->grade_level)
                ->whereHas('studentEnrollment')
                ->get();

            $this->send($students, new ActivityAssigned(
                title: $material->title, subject: $subject->name, instructor: $teacher->name,
                type: 'module', url: '/student/modules',
            ), 'New material: ' . $material->title, "{$teacher->name} posted in {$subject->name}",
               ['type' => 'material', 'id' => $material->id]);
        });
    }

    /** New quiz/assignment — the students of the section. */
    public function newAssignment(Assignment $assignment, User $teacher): void
    {
        $this->safely('Quiz', function () use ($assignment, $teacher) {
            $assignment->loadMissing('subject');

            $students = User::whereIn('id', User::studentIdsInSections([$assignment->section_id]))
                ->where('status', 'approved')
                ->get();

            $this->send($students, new ActivityAssigned(
                title: $assignment->title, subject: $assignment->subject?->name ?? 'Subject',
                instructor: $teacher->name, type: 'quiz', url: '/student/quizzes',
            ), 'New quiz: ' . $assignment->title, "{$teacher->name} posted a new quiz",
               ['type' => 'quiz', 'id' => $assignment->id]);
        });
    }

    /** Submission graded — that student only. */
    public function graded(Submission $submission, User $teacher): void
    {
        $this->safely('Grade', function () use ($submission, $teacher) {
            $submission->loadMissing(['assignment.subject', 'student']);
            $student    = $submission->student;
            $assignment = $submission->assignment;
            if (!$student) {
                return;
            }

            $this->send(collect([$student]), new ActivityAssigned(
                title: $assignment?->title ?? 'Activity', subject: $assignment?->subject?->name ?? 'Subject',
                instructor: $teacher->name, type: 'grade', url: '/student/quizzes',
            ), 'Your work was graded: ' . ($assignment?->title ?? 'Activity'),
               'Score: ' . $submission->score . '/' . ($assignment?->max_score ?? '-'),
               ['type' => 'grade', 'id' => $submission->assignment_id]);
        });
    }

    /** Announcement: the bell and a phone push, for the students it was posted to. */
    public function announcement(iterable $studentIds, string $title, string $body, int $id, string $teacherName): void
    {
        $this->safely('Announcement', function () use ($studentIds, $title, $body, $id, $teacherName) {
            $students = User::whereIn('id', collect($studentIds)->all())
                ->where('status', 'approved')
                ->get();

            $this->send($students, new ActivityAssigned(
                title: $title, subject: 'Announcement', instructor: $teacherName,
                type: 'announcement', url: '/student/dashboard',
            ), '📢 ' . $title, 'From ' . $teacherName . ': ' . $body,
               ['type' => 'announcement', 'id' => $id, 'teacher' => $teacherName]);
        });
    }

    /** A student turned in work: the bell of the teacher who posted it. */
    public function submissionReceived(Assignment $assignment, User $student, bool $isNew): void
    {
        $this->safely('Submission', function () use ($assignment, $student, $isNew) {
            User::find($assignment->user_id)?->notify(new ActivityAssigned(
                title: ($isNew ? 'New submission: ' : 'Updated submission: ') . $assignment->title,
                subject: $student->name, instructor: '', type: 'submission',
                url: '/teacher/assignments/' . $assignment->id,
            ));
        });
    }

    private function send($students, ActivityAssigned $notification, string $title, string $body, array $data): void
    {
        foreach ($students as $student) {
            $student->notify($notification);
        }

        // FCM v1: every data value must be a string (PushNotificationService converts them)
        $this->push->sendToUsers($students->pluck('id')->all(), $title, $body, $data);
    }

    private function safely(string $what, callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::warning("{$what} notification failed (the action itself succeeded): " . $e->getMessage());
        }
    }
}
