<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Submitting answers (student) and grading (teacher) — web + mobile API.
 *
 *  - The deadline is enforced on the web as well as the API.
 *  - A score above max_score (e.g. 500/100) is rejected.
 *  - Teacher feedback goes to the `feedback` column; `remarks` holds the
 *    STUDENT's answer and is never overwritten by grading.
 */
class SubmissionService
{
    public function __construct(private StudentNotifier $notifier) {}

    /** For the student list/detail: published + in the student's section. */
    public static function visibleTo(Assignment $assignment, User $student): bool
    {
        return $assignment->is_published
            && (int) $assignment->section_id === $student->studentSectionId();
    }

    public function submit(Assignment $assignment, User $student, ?UploadedFile $file, ?string $answer): Submission
    {
        if (!static::visibleTo($assignment, $student)) {
            throw new ActionNotAllowed('This activity is not available to you.', 404);
        }

        if ($assignment->isOverdue()) {
            throw new ActionNotAllowed('This quiz is already closed — the deadline has passed.');
        }

        if (!$file && blank($answer)) {
            throw new ActionNotAllowed('Please attach a file or type your answer.', 422);
        }

        $submission = Submission::firstOrNew([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
        ]);

        if ($submission->exists && $submission->status === 'graded') {
            throw new ActionNotAllowed('This activity has already been graded. Submissions are locked.', 422);
        }

        if ($file) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->file_path = $file->store("submissions/{$assignment->id}/{$student->id}", 'public');
        }

        if ($answer !== null) {
            $submission->remarks = $answer;
        }
        $submission->status       = 'submitted';
        $submission->submitted_at = now();
        $submission->save();

        return $submission;
    }

    public function grade(Submission $submission, User $teacher, int $score, ?string $feedback): Submission
    {
        $assignment = $submission->assignment;

        if (!$assignment || (int) $assignment->user_id !== (int) $teacher->id) {
            throw new ActionNotAllowed('Not allowed.');
        }

        if ($score > (int) $assignment->max_score) {
            throw new ActionNotAllowed("Score cannot be higher than the max score ({$assignment->max_score}).", 422);
        }

        $submission->update([
            'score'    => $score,
            'feedback' => $feedback ?? $submission->feedback,
            'status'   => 'graded',
        ]);

        $this->notifier->graded($submission, $teacher);

        return $submission;
    }
}
