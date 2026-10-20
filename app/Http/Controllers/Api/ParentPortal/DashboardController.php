<?php

namespace App\Http\Controllers\Api\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class DashboardController extends Controller
{
    // ── GET /api/parent/dashboard ─────────────────────────────────────────────
    public function index()
    {
        $parent = auth()->user();

        $childModels = $parent->children()
            ->with([
                'grades.subject',
                'section',
                'studentEnrollment.section',
            ])
            ->get();

        $children = $childModels->map(fn($child) => $this->formatChild($child, withGrades: true));

        // School-wide + para sa section ng mga anak lang (dati lahat ng section)
        $announcements = Announcement::published()
            ->forAudience('parent')
            ->visibleToSections($childModels->map(fn ($c) => $c->studentSectionId()))
            ->with('author')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($a) => [
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
            'children'      => $children,
            'announcements' => $announcements,
        ]);
    }

    // ── GET /api/parent/child-records ─────────────────────────────────────────
    public function childRecords()
    {
        $children = auth()->user()
            ->children()
            ->with([
                'grades.subject',
                'section',
                'studentEnrollment.section',
                'studentProfile',
            ])
            ->get()
            ->map(fn($child) => $this->formatChild($child, withGrades: true, withProfile: true));

        return response()->json($children);
    }

    // ── GET /api/parent/attendance?days=30 ────────────────────────────────────
    // Arrival history ng mga anak (mula sa Raspberry Pi scans).
    public function attendance(\Illuminate\Http\Request $request)
    {
        $days     = min(max((int) $request->query('days', 30), 1), 180);
        $children = auth()->user()->children()->get(['id', 'name', 'email']);

        $records = \App\Models\Attendance::where(function ($q) use ($children) {
                $q->whereIn('user_id', $children->pluck('id'));
                $emails = $children->pluck('email')->filter();
                if ($emails->isNotEmpty()) {
                    $q->orWhereIn('student_id', $emails);
                }
            })
            ->where('attended_at', '>=', now()->subDays($days)->startOfDay())
            ->orderByDesc('attended_at')
            ->get();

        return response()->json($children->map(fn ($child) => [
            'student_id'   => $child->id,
            'student_name' => $child->name,
            'records'      => $records
                ->filter(fn ($a) => (int) $a->user_id === (int) $child->id
                    || ($child->email && $a->student_id === $child->email))
                ->values()
                ->map(fn ($a) => [
                    'id'          => $a->id,
                    'status'      => $a->status,          // present | late
                    'attended_at' => $a->attended_at?->toIso8601String(),
                    'date'        => $a->attended_at?->toDateString(),
                    'time'        => $a->attended_at?->format('g:i A'),
                ]),
        ])->values());
    }

    // ── Private helper ─────────────────────────────────────────────────────────
    private function formatChild($child, bool $withGrades = false, bool $withProfile = false): array
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
            // Group grades by subject and quarter
            $grades = $child->grades->map(fn($g) => [
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

            // Compute general average across all quarters with final grades
            $gradedQuarters = $child->grades->filter(fn($g) => $g->final_grade !== null);
            $generalAverage = $gradedQuarters->count() > 0
                ? round($gradedQuarters->avg('final_grade'), 2)
                : null;

            $data['grades']          = $grades;
            $data['general_average'] = $generalAverage;
            $data['passing_count']   = $gradedQuarters->filter(fn($g) => $g->isPassed())->count();
            $data['failing_count']   = $gradedQuarters->filter(fn($g) => !$g->isPassed())->count();
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
