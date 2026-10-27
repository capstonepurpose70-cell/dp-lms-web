<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Section;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\GradeService;

class TeacherStudentController extends Controller
{
    public function index()
    {
        $teacher    = auth()->user();
        $schoolYear = GradeService::currentSchoolYear();

        $students = User::whereIn('id', User::studentIdsInSections($teacher->teachingSectionIds()))
            ->where('status', 'approved')
            ->with([
                'section:id,name,grade_level',
                'studentEnrollment',   // studentSectionId() — iwas isang query kada estudyante
                'grades' => fn ($q) => $q->where('school_year', $schoolYear)->with('subject:id,name'),
            ])
            ->orderBy('name')
            ->get();

        // Which grades this teacher may see (dati: hindi naglo-load ng grades → laging "No grades recorded yet"):
        //  - adviser of the student's section → all subjects
        //  - otherwise → only the subjects they teach in that section
        $advisory = Section::where('adviser_id', $teacher->id)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $taught   = TeacherSubject::where('user_id', $teacher->id)->get(['section_id', 'subject_id'])
            ->map(fn ($t) => $t->section_id . '-' . $t->subject_id)->all();

        $details = $students->mapWithKeys(function (User $s) use ($advisory, $taught) {
            $sectionId = $s->studentSectionId();
            $isAdviser = $sectionId && in_array($sectionId, $advisory, true);

            $bySubject = [];
            foreach ($s->grades as $g) {
                if (!$isAdviser && !in_array($sectionId . '-' . $g->subject_id, $taught, true)) {
                    continue;
                }
                $name = $g->subject?->name ?? 'Subject';
                $bySubject[$name] ??= ['Q1' => null, 'Q2' => null, 'Q3' => null, 'Q4' => null];
                if (array_key_exists($g->quarter, $bySubject[$name])) {
                    $bySubject[$name][$g->quarter] = (float) $g->final_grade;
                }
            }

            // Average of the recorded quarterly grades (dati: ang huling quarter lang ang "Final")
            $grades = [];
            ksort($bySubject);
            foreach ($bySubject as $name => $q) {
                $recorded = array_filter($q, fn ($v) => $v !== null);
                $avg      = $recorded ? round(array_sum($recorded) / count($recorded), 2) : null;
                $grades[] = [
                    'subject'  => $name,
                    'quarters' => $q,
                    'average'  => $avg,
                    'complete' => count($recorded) === 4,
                    'passed'   => $avg !== null && $avg >= Grade::PASSING,
                ];
            }

            return [$s->id => [
                'id'          => $s->id,
                'name'        => $s->name,
                'lrn'         => $s->lrn,
                'email'       => $s->email,
                'section'     => $s->section?->name ?? '—',
                'grade_level' => $s->grade_level ?? '—',
                'adviser'     => $isAdviser,
                'registered'  => $s->created_at?->format('M d, Y') ?? '—',
                'grades'      => $grades,
            ]];
        });

        return view('teacher.students.index', compact('students', 'details', 'schoolYear'));
    }
}
