<?php

namespace App\Http\Controllers\Api\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GradeService;
use App\Services\ParentPortalService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ParentPortalService $portal)
    {
    }

    // ── GET /api/parent/dashboard ─────────────────────────────────────────────
    public function index()
    {
        $parent      = auth()->user();
        $childModels = $this->portal->children($parent);

        $announcements = $this->portal->announcements($childModels)->map(fn ($a) => [
            'id'       => $a->id,
            'title'    => $a->title,
            'body'     => $a->body,
            'author'   => $a->author?->name,
            'date'     => $a->created_at?->toDateString(),
            'time_ago' => $a->created_at?->diffForHumans(),
        ]);

        return response()->json([
            'parent' => [
                'id'   => $parent->id,
                'name' => $parent->name,
            ],
            'children'      => $childModels->map(fn ($child) => $this->formatChild($child, withGrades: true)),
            'announcements' => $announcements,
        ]);
    }

    // ── GET /api/parent/child-records ─────────────────────────────────────────
    public function childRecords()
    {
        $children = $this->portal->children(auth()->user(), ['studentProfile'])
            ->map(fn ($child) => $this->formatChild($child, withGrades: true, withProfile: true));

        return response()->json($children);
    }

    // ── GET /api/parent/attendance?days=30 ────────────────────────────────────
    // Gate arrivals of the children (face kiosk / scanner).
    public function attendance(Request $request)
    {
        $days     = ParentPortalService::clampDays($request->query('days'));
        $children = auth()->user()->children()->orderBy('name')->get(['id', 'name', 'email']);
        $arrivals = $this->portal->arrivals($children, $days);

        return response()->json($children->map(fn ($child) => [
            'student_id'   => $child->id,
            'student_name' => $child->name,
            'records'      => $arrivals[$child->id]->map(fn ($a) => [
                'id'          => $a->id,
                'status'      => $a->status,          // present | late
                'attended_at' => $a->attended_at?->toIso8601String(),
                'date'        => $a->attended_at?->toDateString(),
                'time'        => $a->attended_at?->format('g:i A'),
            ]),
        ])->values());
    }

    /**
     * GET /api/parent/class-attendance?days=30 — per-class results (present / late / absent)
     * taken by the subject teachers. (Gate arrivals are /api/parent/attendance.)
     */
    public function classAttendance(Request $request)
    {
        $days     = ParentPortalService::clampDays($request->query('days'));
        $children = auth()->user()->children()->orderBy('name')->get(['id', 'name']);
        $records  = $this->portal->classAttendance($children, $days);

        return response()->json($children->map(fn ($child) => [
            'student_id'   => $child->id,
            'student_name' => $child->name,
            'records'      => $records[$child->id]->map(fn ($r) => [
                'session_id'   => $r->session_id,
                'subject_name' => $r->session?->subject?->name,
                'teacher_name' => $r->session?->teacher?->name,
                'date'         => $r->session?->opened_at?->toDateString(),
                'status'       => $r->status,                            // present | late | absent
                'marked_at'    => $r->marked_at?->toIso8601String(),     // null when absent
            ])->values(),
        ])->values());
    }

    // ── Private helper ─────────────────────────────────────────────────────────
    private function formatChild(User $child, bool $withGrades = false, bool $withProfile = false): array
    {
        $enrollment = $child->studentEnrollment;

        $data = [
            'id'          => $child->id,
            'name'        => $child->name,
            'email'       => $child->email,
            'lrn'         => $child->lrn,
            'grade_level' => $child->grade_level,
            'status'      => $child->status,
            'section'     => $enrollment?->section?->name ?? $child->section?->name,
            'school_year' => $enrollment?->school_year,
            'enrollment_status' => $enrollment?->status ?? 'not enrolled',
        ];

        if ($withGrades) {
            $data['grades'] = $child->grades->map(fn ($g) => [
                'subject'              => $g->subject?->name,
                'subject_id'           => $g->subject?->id,
                'quarter'              => $g->quarter,
                'written_works'        => $g->written_works,
                'performance_tasks'    => $g->performance_tasks,
                'quarterly_assessment' => $g->quarterly_assessment,
                'final_grade'          => $g->final_grade,
                'remarks'              => $g->remarks,
                'passed'               => $g->isPassed(),
            ]);

            // Same figures as the student's Grades page: this school year only, averaged per subject
            $graded = $child->grades->where('school_year', GradeService::currentSchoolYear())
                ->filter(fn ($g) => $g->final_grade !== null);

            $data['general_average'] = $this->portal->currentYearCard($child)['general'] ?? null;
            $data['passing_count']   = $graded->filter(fn ($g) => $g->isPassed())->count();
            $data['failing_count']   = $graded->reject(fn ($g) => $g->isPassed())->count();
        }

        if ($withProfile && $child->relationLoaded('studentProfile') && $child->studentProfile) {
            $profile = $child->studentProfile;
            $data['profile'] = [
                'full_name'            => $profile->full_name,
                'age'                  => $profile->age,
                'birthdate'            => $profile->birthdate?->toDateString(),
                'gender'               => $profile->gender,
                'address'              => $profile->address,
                'mother_name'          => $profile->mother_name,
                'father_name'          => $profile->father_name,
                'guardian_name'        => $profile->guardian_name,
                'guardian_contact'     => $profile->guardian_contact,
                'student_type'         => $profile->student_type,
                'last_school'          => $profile->last_school,
                'last_grade_completed' => $profile->last_grade_completed,
            ];
        }

        return $data;
    }
}
