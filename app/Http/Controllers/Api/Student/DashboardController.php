<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LearningMaterial;
use App\Models\TeacherSubject;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $subjects = $section
            ? TeacherSubject::with(['subject', 'teacher'])
                ->where('section_id', $section->id)
                ->get()->unique('subject_id')
            : collect();

        return response()->json([
            'user'          => $user->only(['id','name','email','grade_level','role']),
            'section'       => $section?->only(['id','name']),
            'subject_count' => $subjects->count(),
            'announcements' => Announcement::published()
                ->forAudience('student')->visibleToSections([$section?->id])
                ->with('author')->latest()->take(5)->get()
                ->map(fn($a) => ['id'=>$a->id,'title'=>$a->title,'body'=>$a->body,'date'=>$a->created_at->toDateString(),'author'=>$a->author?->name]),
        ]);
    }

    public function subjects()
    {
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $subjects = $section
            ? TeacherSubject::with(['subject','teacher'])
                ->where('section_id', $section->id)->get()->unique('subject_id')
            : collect();

        return response()->json($subjects->values()->map(fn($ts) => [
            'subject_id'   => $ts->subject?->id,
            'name'         => $ts->subject?->name,
            'code'         => $ts->subject?->code,
            'grade_level'  => $ts->subject?->grade_level,
            'teacher_name' => $ts->teacher?->name,
        ]));
    }

    public function modules()
    {
        $sectionId  = auth()->user()->studentSectionId();
        $subjectIds = $sectionId
            ? TeacherSubject::where('section_id', $sectionId)->pluck('subject_id')
            : collect();

        $materials = LearningMaterial::with(['subject','teacher'])
            ->whereIn('subject_id', $subjectIds)
            ->where('is_published', true)
            ->latest()->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'subject_id'  => $m->subject_id,
                'title'       => $m->title,
                'description' => $m->description,
                'file_type'   => $m->file_type,
                'quarter'     => $m->quarter,
                'week'        => $m->week,
                'subject'     => $m->subject?->name,
                'teacher'     => $m->teacher?->name,
                'file_url'    => $m->file_path ? asset('storage/'.$m->file_path) : null,
            ]);

        return response()->json($materials);
    }

    public function grades()
    {
        $grades = auth()->user()->grades()->with('subject')->orderBy('quarter')->get()
            ->map(fn($g) => [
                'subject'              => $g->subject?->name,
                'quarter'              => $g->quarter,
                'school_year'          => $g->school_year,
                'written_works'        => $g->written_works,
                'performance_tasks'    => $g->performance_tasks,
                'quarterly_assessment' => $g->quarterly_assessment,
                'final_grade'          => $g->final_grade,
                'remarks'              => $g->remarks,
                'passed'               => $g->isPassed(),
            ]);

        return response()->json($grades);
    }

    public function announcements()
    {
        $items = Announcement::published()->forAudience('student')
            ->visibleToSections([auth()->user()->studentSectionId()])
            ->with('author')->latest()->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'title'       => $a->title,
                'body'        => $a->body,
                'date'        => $a->created_at->diffForHumans(),
                'posted_date' => $a->created_at->format('M j, Y'),
                'posted_time' => $a->created_at->format('g:i A'),
                'author'      => $a->author?->name,
            ]);

        return response()->json($items);
    }

    /** POST /api/student/enroll — parehong rules at tsek sa web (EnrollmentService) */
    public function enroll(Request $request, EnrollmentService $enrollments)
    {
        $data = $request->validate(EnrollmentService::rules(), EnrollmentService::messages());

        $enrollments->submit($request->user(), $data);

        return response()->json(['message' => 'Enrollment submitted!']);
    }
}
