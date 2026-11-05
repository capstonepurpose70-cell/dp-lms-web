<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClassAttendanceService;
use App\Services\GradeService;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    public function index(Request $request, GradeService $gradeService, ClassAttendanceService $classes)
    {
        $teacher = auth()->user();
        $filters = $request->validate([
            'class'   => ['nullable', 'regex:/^\d+-\d+$/'],   // "{section_id}-{subject_id}"
            'quarter' => 'nullable|in:Q1,Q2,Q3,Q4',
            'q'       => 'nullable|string|max:100',
        ]);
        $schoolYear = GradeService::currentSchoolYear();

        // Classes come from Class Schedules (teacher_subjects): one subject in one section
        $classList = $classes->teacherClasses($teacher)->map(fn ($ts) => [
            'key'        => $ts->section_id . '-' . $ts->subject_id,
            'section_id' => (int) $ts->section_id,
            'subject_id' => (int) $ts->subject_id,
            'label'      => "{$ts->subject->name} · Grade {$ts->section->grade_level} {$ts->section->name}",
        ])->values();

        // Students per section for the encode form (section from enrollment, same rule as GradeService)
        $roster = User::with('studentEnrollment')
            ->whereIn('id', User::studentIdsInSections($classList->pluck('section_id')->unique()))
            ->orderBy('name')->get(['id', 'name', 'lrn', 'section_id'])
            ->groupBy(fn ($s) => $s->studentSectionId())
            ->map(fn ($g) => $g->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'lrn' => $s->lrn])->values());

        $query = $gradeService->visibleTo($teacher)
            ->with(['student.studentEnrollment', 'student.section', 'subject'])
            ->where('school_year', $schoolYear);

        if (!empty($filters['class'])) {
            [$sectionId, $subjectId] = array_map('intval', explode('-', $filters['class']));
            $query->where('subject_id', $subjectId)->whereIn('user_id', User::studentIdsInSections([$sectionId]));
        }
        if (!empty($filters['quarter'])) {
            $query->where('quarter', $filters['quarter']);
        }
        if (!empty($filters['q'])) {
            $term = '%' . addcslashes(trim($filters['q']), '%_\\') . '%';
            $query->whereHas('student', fn ($s) => $s->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('lrn', 'like', $term)));
        }

        $grades = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        return view('teacher.gradebook', [
            'grades'     => $grades,
            'classList'  => $classList,
            'roster'     => $roster,
            'filters'    => $filters,
            'schoolYear' => $schoolYear,
            'weights'    => $gradeService->classWeights($classList),
        ]);
    }

    // POST /teacher/gradebook/weights — percentages of one class; same rules as the mobile API.
    public function weights(Request $request, GradeService $grades)
    {
        $data = $request->validate(['class_key' => ['required', 'regex:/^\d+-\d+$/']] + GradeService::weightRules());
        [$sectionId, $subjectId] = array_map('intval', explode('-', $data['class_key']));

        try {
            $saved = $grades->setWeights(auth()->user(), $sectionId, $subjectId, $data);
        } catch (ActionNotAllowed $e) {
            return $request->expectsJson() ? $e->toJson() : back()->with('error', $e->getMessage());
        }

        $message = 'Percentages saved for ' . implode(', ', $saved) . '.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'saved' => $saved, 'message' => $message])
            : back()->with('success', $message);
    }

    // POST /teacher/gradebook — same logic as the mobile API (GradeService).
    public function store(Request $request, GradeService $grades)
    {
        $data = $request->validate(GradeService::rules() + [
            'class_key'                    => ['required_with:percent', 'nullable', 'regex:/^\d+-\d+$/'],
            'percent'                      => 'nullable|array',
            'percent.written_works'        => 'required_with:percent|integer|min:0|max:100',
            'percent.performance_tasks'    => 'required_with:percent|integer|min:0|max:100',
            'percent.quarterly_assessment' => 'required_with:percent|integer|min:0|max:100',
            'all_quarters'                 => 'nullable|boolean',
        ], [], GradeService::attributes() + [
            'percent.written_works'        => 'written works percentage',
            'percent.performance_tasks'    => 'performance tasks percentage',
            'percent.quarterly_assessment' => 'quarterly assessment percentage',
        ]);

        try {
            // Percentages typed beside the scores go first, so this grade is computed with them
            if (!empty($data['percent'])) {
                [$sectionId, $subjectId] = array_map('intval', explode('-', $data['class_key']));
                $grades->applyWeights(auth()->user(), $sectionId, $subjectId, $data['percent'] + [
                    'quarter'      => $data['quarter'],
                    'all_quarters' => $data['all_quarters'] ?? false,
                ]);
            }

            $grade = $grades->save(auth()->user(), $data);
        } catch (ActionNotAllowed $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', "Grade saved. Final grade: {$grade->final_grade} ("
            . ($grade->isPassed() ? 'Passed' : 'Failed') . ').');
    }
}
