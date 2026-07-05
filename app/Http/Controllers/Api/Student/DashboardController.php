<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\LearningMaterial;
use App\Models\TeacherSubject;

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
                ->forAudience('student')->with('author')->latest()->take(5)->get()
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
        $user       = auth()->user();
        $enrollment = $user->studentEnrollment;
        $section    = $enrollment?->section ?? $user->section;

        $subjectIds = $section
            ? TeacherSubject::where('section_id', $section->id)->pluck('subject_id')
            : collect();

        $materials = LearningMaterial::with(['subject','teacher'])
            ->whereIn('subject_id', $subjectIds)
            ->where('is_published', true)
            ->latest()->get()
            ->map(fn($m) => [
                'id'          => $m->id,
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
            ->with('author')->latest()->get()
            ->map(fn($a) => ['id'=>$a->id,'title'=>$a->title,'body'=>$a->body,'date'=>$a->created_at->diffForHumans(),'author'=>$a->author?->name]);

        return response()->json($items);
    }

    public function assignments()
    {
        // Placeholder — extend once assignments are tied to student sections
        return response()->json([]);
    }

    public function enroll(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'grade_level'  => 'required|string|in:7,8,9,10,11,12',
            'school_year'  => 'required|string',
            'student_type' => 'required|in:new,old,transfer',
            'full_name'    => 'required|string|max:255',
            'age'          => 'required|integer',
            'birthdate'    => 'required|date',
            'gender'       => 'required|in:Male,Female',
            'address'      => 'required|string',
        ]);

        $user = auth()->user();
        \App\Models\EnrollmentRequest::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            $request->only(['grade_level','school_year','student_type','full_name','age','birthdate','gender','address','mother_name','father_name','guardian_name','guardian_contact']) + ['status' => 'pending']
        );

        return response()->json(['message' => 'Enrollment submitted!']);
    }

    /**
     * School years for the mobile enrollment dropdown (active first, newest first).
     * Guarantees the student picks a real, existing school year.
     */
    public function schoolYears()
    {
        $years = \App\Models\SchoolYear::orderByDesc('is_active')
            ->orderByDesc('starts_at')
            ->pluck('label')
            ->filter()
            ->unique()
            ->values();

        return response()->json(['school_years' => $years]);
    }
}