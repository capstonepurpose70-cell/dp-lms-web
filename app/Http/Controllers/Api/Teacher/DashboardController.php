<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\LearningMaterial;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    // ── GET /api/teacher/dashboard ────────────────────────────────────────────
    public function index()
    {
        $teacher = auth()->user();

        $assignments = TeacherSubject::with(['subject', 'section'])
            ->where('user_id', $teacher->id)
            ->get();

        $sectionIds = $assignments->pluck('section_id')->unique();

        $studentCount = User::where('role', 'student')
            ->where('status', 'approved')
            ->whereIn('section_id', $sectionIds)
            ->count();

        // Sections with subject counts
        $sections = $assignments->groupBy('section_id')->map(function ($group) {
            $first = $group->first();
            return [
                'section_id'    => $first->section?->id,
                'section_name'  => $first->section?->name,
                'grade_level'   => $first->section?->grade_level,
                'subject_count' => $group->count(),
            ];
        })->values();

        // Subjects assigned to this teacher
        $subjects = $assignments->map(fn($a) => [
            'subject_id'   => $a->subject?->id,
            'subject_name' => $a->subject?->name,
            'subject_code' => $a->subject?->code,
            'section_id'   => $a->section?->id,
            'section_name' => $a->section?->name,
            'grade_level'  => $a->subject?->grade_level,
        ])->unique('subject_id')->values();

        return response()->json([
            'teacher' => [
                'id'          => $teacher->id,
                'name'        => $teacher->name,
                'email'       => $teacher->email,
                'employee_id' => $teacher->employee_id,
            ],
            'summary' => [
                'student_count' => $studentCount,
                'subject_count' => $assignments->unique('subject_id')->count(),
                'section_count' => $sectionIds->count(),
            ],
            'sections' => $sections,
            'subjects' => $subjects,
        ]);
    }

    // ── GET /api/teacher/students ─────────────────────────────────────────────
    public function students()
    {
        $teacher = auth()->user();

        $sectionIds = TeacherSubject::where('user_id', $teacher->id)
            ->pluck('section_id')
            ->unique();

        $students = User::where('role', 'student')
            ->where('status', 'approved')
            ->whereIn('section_id', $sectionIds)
            ->with('section')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'email'       => $s->email,
                'lrn'         => $s->lrn,
                'grade_level' => $s->grade_level,
                'section'     => $s->section?->name,
                'section_id'  => $s->section_id,
            ]);

        return response()->json($students);
    }

    // ── GET /api/teacher/gradebook ────────────────────────────────────────────
    public function gradebook(Request $request)
    {
        $teacher = auth()->user();

        $subjectIds = TeacherSubject::where('user_id', $teacher->id)
            ->pluck('subject_id');
        $sectionIds = TeacherSubject::where('user_id', $teacher->id)
            ->pluck('section_id');

        $query = Grade::with(['student.section', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->whereHas('student', fn($q) => $q->whereIn('section_id', $sectionIds));

        // Optional filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('quarter')) {
            $query->where('quarter', $request->quarter);
        }
        if ($request->filled('section_id')) {
            $query->whereHas('student', fn($q) => $q->where('section_id', $request->section_id));
        }

        $grades = $query->latest()->get()->map(fn($g) => [
            'id'                   => $g->id,
            'student_id'           => $g->student?->id,
            'student_name'         => $g->student?->name,
            'student_lrn'          => $g->student?->lrn,
            'section'              => $g->student?->section?->name,
            'subject_id'           => $g->subject?->id,
            'subject_name'         => $g->subject?->name,
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

    // ── POST /api/teacher/grades ──────────────────────────────────────────────
    // Body: { student_id, subject_id, quarter, written_works, performance_tasks, quarterly_assessment }
    public function saveGrade(Request $request)
    {
        $request->validate([
            'student_id'           => 'required|exists:users,id',
            'subject_id'           => 'required|exists:subjects,id',
            'quarter'              => 'required|in:Q1,Q2,Q3,Q4',
            'written_works'        => 'nullable|numeric|min:0|max:100',
            'performance_tasks'    => 'nullable|numeric|min:0|max:100',
            'quarterly_assessment' => 'nullable|numeric|min:0|max:100',
            'remarks'              => 'nullable|string|max:255',
        ]);

        $teacher = auth()->user();

        // Ensure teacher is assigned to this subject
        $isAssigned = TeacherSubject::where('user_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['message' => 'You are not assigned to this subject.'], 403);
        }

        // Compute final grade using DepEd formula: WW 25% + PT 50% + QA 25%
        $ww = $request->written_works ?? 0;
        $pt = $request->performance_tasks ?? 0;
        $qa = $request->quarterly_assessment ?? 0;
        $finalGrade = round(($ww * 0.25) + ($pt * 0.50) + ($qa * 0.25), 2);

        $grade = Grade::updateOrCreate(
            [
                'user_id'    => $request->student_id,
                'subject_id' => $request->subject_id,
                'quarter'    => $request->quarter,
            ],
            [
                'written_works'        => $ww,
                'performance_tasks'    => $pt,
                'quarterly_assessment' => $qa,
                'final_grade'          => $finalGrade,
                'remarks'              => $finalGrade >= 75 ? 'Passed' : 'Failed',
                'school_year'          => \App\Models\SchoolYear::current()?->name ?? now()->year . '-' . (now()->year + 1),
            ]
        );

        AuditLogService::log(
            "Updated grade for student ID {$request->student_id}",
            'Gradebook',
            "Subject: {$request->subject_id} | Quarter: {$request->quarter} | Final: {$finalGrade}"
        );

        return response()->json([
            'message'     => 'Grade saved successfully.',
            'final_grade' => $finalGrade,
            'passed'      => $finalGrade >= 75,
            'grade'       => $grade,
        ]);
    }

    // ── GET /api/teacher/materials ────────────────────────────────────────────
    public function materials(Request $request)
    {
        $teacher = auth()->user();

        $query = LearningMaterial::with('subject')
            ->where('user_id', $teacher->id);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('quarter')) {
            $query->where('quarter', $request->quarter);
        }

        $materials = $query->latest()->get()->map(fn($m) => [
            'id'           => $m->id,
            'title'        => $m->title,
            'description'  => $m->description,
            'subject_id'   => $m->subject?->id,
            'subject_name' => $m->subject?->name,
            'file_type'    => $m->file_type,
            'quarter'      => $m->quarter,
            'week'         => $m->week,
            'is_published' => $m->is_published,
            'file_url'     => $m->file_path ? asset('storage/' . $m->file_path) : null,
            'created_at'   => $m->created_at?->toDateTimeString(),
        ]);

        return response()->json($materials);
    }

    // ── POST /api/teacher/materials ───────────────────────────────────────────
    // Multipart: title, subject_id, quarter, week, description, file (optional)
    public function storeMaterial(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'subject_id'  => 'required|exists:subjects,id',
            'quarter'     => 'required|in:Q1,Q2,Q3,Q4',
            'week'        => 'nullable|integer|min:1|max:20',
            'description' => 'nullable|string|max:1000',
            'file'        => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,png,jpg,jpeg,mp4,zip',
        ]);

        $teacher = auth()->user();

        // Verify subject is assigned to this teacher
        $isAssigned = TeacherSubject::where('user_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['message' => 'You are not assigned to this subject.'], 403);
        }

        $filePath = null;
        $fileType = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileType = $file->getClientOriginalExtension();
            $filePath = $file->store('materials', 'public');
        }

        $material = LearningMaterial::create([
            'user_id'      => $teacher->id,
            'subject_id'   => $request->subject_id,
            'title'        => $request->title,
            'description'  => $request->description,
            'file_path'    => $filePath,
            'file_type'    => $fileType,
            'quarter'      => $request->quarter,
            'week'         => $request->week,
            'is_published' => true,
        ]);

        // Notify enrolled students
        $subject  = Subject::find($request->subject_id);
        $students = User::where('role', 'student')
            ->where('status', 'approved')
            ->where('grade_level', $subject->grade_level)
            ->whereHas('studentEnrollment')
            ->get();

        foreach ($students as $student) {
            $student->notify(new \App\Notifications\ActivityAssigned([
                'title'      => $material->title,
                'subject'    => $subject->name,
                'instructor' => $teacher->name,
                'type'       => 'module',
                'url'        => route('student.modules'),
            ]));
        }

        AuditLogService::log(
            "Uploaded material: {$material->title}",
            'Materials'
        );

        return response()->json([
            'message'  => 'Material uploaded successfully.',
            'material' => [
                'id'       => $material->id,
                'title'    => $material->title,
                'file_url' => $filePath ? asset('storage/' . $filePath) : null,
            ],
        ], 201);
    }

    // ── DELETE /api/teacher/materials/{id} ────────────────────────────────────
    public function deleteMaterial(int $id)
    {
        $teacher  = auth()->user();
        $material = LearningMaterial::where('id', $id)
            ->where('user_id', $teacher->id)
            ->firstOrFail();

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        AuditLogService::log("Deleted material: {$material->title}", 'Materials');
        $material->delete();

        return response()->json(['message' => 'Material deleted.']);
    }

    // ── GET /api/teacher/announcements ────────────────────────────────────────
    public function announcements()
    {
        $announcements = Announcement::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'body'         => $a->body,
                'audience'     => $a->audience,
                'section_id'   => $a->section_id,
                'is_published' => $a->is_published,
                'date'         => $a->created_at?->toDateTimeString(),
                'time_ago'     => $a->created_at?->diffForHumans(),
            ]);

        return response()->json($announcements);
    }

    // ── POST /api/teacher/announcements ───────────────────────────────────────
    // Body: { title, body, audience, section_id? }
    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string',
            'audience'   => 'required|in:all,students,parents',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $announcement = Announcement::create([
            'user_id'    => auth()->id(),
            'title'      => $request->title,
            'body'       => $request->body,
            'audience'   => $request->audience,
            'section_id' => $request->section_id,
            'is_published' => true,
        ]);

        AuditLogService::log(
            "Posted announcement: {$announcement->title}",
            'Announcements'
        );

        return response()->json([
            'message'      => 'Announcement posted successfully.',
            'announcement' => [
                'id'    => $announcement->id,
                'title' => $announcement->title,
            ],
        ], 201);
    }

    // ── DELETE /api/teacher/announcements/{id} ────────────────────────────────
    public function deleteAnnouncement(int $id)
    {
        $announcement = Announcement::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        AuditLogService::log("Deleted announcement: {$announcement->title}", 'Announcements');
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }

    // ── GET /api/teacher/attendance ───────────────────────────────────────────
    // Optional query params: date (YYYY-MM-DD), section_id
    public function attendance(Request $request)
    {
        $teacher = auth()->user();

        $sectionIds = TeacherSubject::where('user_id', $teacher->id)
            ->pluck('section_id')
            ->unique();

        $query = Attendance::with('user')
            ->orderBy('attended_at', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('attended_at', $request->date);
        }

        if ($request->filled('section_id')) {
            $query->whereHas('user', fn($q) =>
                $q->where('section_id', $request->section_id)
            );
        }

        $records = $query->take(100)->get()->map(fn($a) => [
            'id'           => $a->id,
            'student_name' => $a->student_name ?? $a->user?->name,
            'student_id'   => $a->student_id,
            'section'      => $a->user?->section?->name,
            'status'       => $a->status,
            'source'       => $a->source,
            'attended_at'  => $a->attended_at?->toDateTimeString(),
            'time'         => $a->attended_at?->format('h:i A'),
            'date'         => $a->attended_at?->toDateString(),
        ]);

        return response()->json($records);
    }
}
