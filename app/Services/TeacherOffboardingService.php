<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\FacultyRecord;
use App\Models\Section;
use App\Models\TeacherAssignment;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A teacher leaves the school (retires, resigns, transfers).
 *
 * The account is closed but kept, so grades, materials and messages stay on record. The advisory section,
 * the timetable classes and the teaching load go to a replacement teacher, or wait for a new one
 * (the section's enrollment forms then go to the grade's pool, classes show "No teacher yet").
 * The faculty entry stays claimed, so the employee ID cannot be used to activate a new account.
 */
class TeacherOffboardingService
{
    public const REASONS = ['retired' => 'Retired', 'resigned' => 'Resigned', 'transferred' => 'Transferred', 'other' => 'Left'];

    /** @return array{sections: int, classes: int, loads: int} what was handed over or freed */
    public function leave(FacultyRecord $record, string $reason, ?User $replacement): array
    {
        $teacher = $record->claimedBy;

        return DB::transaction(function () use ($record, $reason, $replacement, $teacher) {
            $to = $replacement?->id;
            $moved = [
                'sections' => Section::where('adviser_id', $teacher->id)->update(['adviser_id' => $to]),
                'classes'  => ClassSchedule::where('teacher_id', $teacher->id)->update(['teacher_id' => $to]),
                'loads'    => $this->moveLoad(TeacherSubject::class, $teacher, $replacement)
                            + $this->moveLoad(TeacherAssignment::class, $teacher, $replacement),
            ];

            $teacher->forceFill(['status' => 'inactive'])->save();
            $teacher->tokens()->delete();             // the app signs out
            $teacher->trustedDevices()->delete();     // every browser asks for a code again (and is refused)
            $record->forceFill(['left_at' => now(), 'left_reason' => $reason])->save();

            return $moved;
        });
    }

    /** The teacher comes back: the account opens again (classes are assigned anew by the admin). */
    public function reinstate(FacultyRecord $record): void
    {
        DB::transaction(function () use ($record) {
            $record->claimedBy->forceFill(['status' => 'approved'])->save();
            $record->forceFill(['left_at' => null, 'left_reason' => null])->save();
        });
    }

    /** Hand each load row to the replacement (skipping ones they already have), or drop it. */
    private function moveLoad(string $model, User $teacher, ?User $replacement): int
    {
        $rows = $model::where('user_id', $teacher->id)->get();
        foreach ($rows as $row) {
            $taken = $replacement && $model::where('user_id', $replacement->id)
                ->where('section_id', $row->section_id)->where('subject_id', $row->subject_id)->exists();
            if ($replacement && !$taken) {
                $row->forceFill(['user_id' => $replacement->id])->save();
            } else {
                $row->delete();
            }
        }

        return $rows->count();
    }
}
