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
 * In-app (database) + FCM push notifications sa mga estudyante.
 *
 * Iisang lugar para sa web at mobile API (dati magkakahiwalay na kopya).
 * HINDI kailanman nagtatapon ng error: kapag pumalya ang notification,
 * naka-save pa rin ang material / quiz / grade.
 */
class StudentNotifier
{
    public function __construct(private PushNotificationService $push) {}

    /** Bagong learning material — lahat ng enrolled sa grade level ng subject. */
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

    /** Bagong quiz/assignment — mga estudyante ng section. */
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

    /** Na-grade ang submission — ang estudyante lang. */
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

    /** Push lang (walang in-app) para sa announcement. */
    public function announcement(iterable $studentIds, string $title, string $body, int $id, string $teacherName): void
    {
        $this->safely('Announcement', function () use ($studentIds, $title, $body, $id, $teacherName) {
            $approved = User::whereIn('id', collect($studentIds)->all())
                ->where('status', 'approved')
                ->pluck('id')
                ->all();

            $this->push->sendToUsers(
                $approved,
                '📢 ' . $title,
                'From ' . $teacherName . ': ' . $body,
                ['type' => 'announcement', 'id' => $id, 'teacher' => $teacherName],
            );
        });
    }

    private function send($students, ActivityAssigned $notification, string $title, string $body, array $data): void
    {
        foreach ($students as $student) {
            $student->notify($notification);
        }

        // FCM v1: lahat ng data value ay dapat string (ginagawa ng PushNotificationService)
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
