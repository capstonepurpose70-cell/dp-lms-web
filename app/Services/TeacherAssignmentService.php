<?php

namespace App\Services;

use App\Mail\TeacherInviteMail;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\TeacherAssignment;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeacherAssignmentService
{
public function createTeacherWithAssignments(array $data): User
{
    $teacher = User::create([
        'name'           => $data['name'],
        'email'          => $data['email'],
        'password'       => Hash::make(Str::random(32)),
        'role'           => 'teacher',
        'status'         => 'approved',
        'employee_id'    => $data['employee_id'] ?? null,
        'contact_number' => $data['contact_number'] ?? null,
        'grade_level'    => null,
    ]);

    // ── Professional info (i-store sa separate profile o JSON) ──
    // TEMPORARY: i-store sa TeacherAssignment notes hanggang may teacher_profiles table
    // Para sa ngayon, i-log lang para hindi mawala
    \App\Services\AuditLogService::log(
        "Teacher profile info submitted",
        'User Management',
        "Dept: " . ($data['department'] ?? '') .
        " | Position: " . ($data['position'] ?? '') .
        " | Employee ID: " . ($data['employee_id'] ?? '')
    );

    // ── Section assignment ──
    if (!empty($data['section_id'])) {
        $teacher->update(['section_id' => $data['section_id']]);
    }

    // ── Subject assignments ──
    if (!empty($data['subjects']) && !empty($data['section_id'])) {
        $section = \App\Models\Section::find($data['section_id']);
        $schoolYear = \App\Models\SchoolYear::current()?->name ?? '2025-2026';

        $rows = collect($data['subjects'])->map(fn($subjectId) => [
            'user_id'     => $teacher->id,
            'subject_id'  => $subjectId,
            'section_id'  => $data['section_id'],
            'grade_level' => $section?->grade_level ?? '',
            'school_year' => $schoolYear,
            'created_at'  => now(),
            'updated_at'  => now(),
        ])->toArray();

        \App\Models\TeacherSubject::insert($rows);
    }

    // ── Adviser assignment ──
    if (!empty($data['is_adviser']) && !empty($data['adviser_section_id'])) {
        $schoolYear = \App\Models\SchoolYear::current();
        $adviserSection = \App\Models\Section::find($data['adviser_section_id']);

        \App\Models\TeacherAssignment::create([
            'user_id'           => $teacher->id,
            'section_id'        => $data['adviser_section_id'],
            'subject_id'        => null,
            'school_year_id'    => $schoolYear?->id,
            'grade_level'       => $adviserSection?->grade_level ?? '',
            'school_year_label' => $schoolYear?->name ?? '2025-2026',
            'is_adviser'        => true,
            'status'            => 'active',
        ]);
    }

    // ── Invite token ──
    $token = Str::random(64);

    $teacher->update([
        'invite_token'         => \Illuminate\Support\Facades\Hash::make($token),
        'invite_expires_at'    => now()->addHours(72),
        'must_change_password' => true,
    ]);

    Mail::to($teacher->email)->send(new TeacherInviteMail($teacher, $token));

    return $teacher->fresh();
}
}