<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ActivityAssigned;
use App\Models\User;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = LearningMaterial::where('user_id', auth()->id())
            ->with('subject')
            ->withCount(['views', 'likes', 'comments'])
            ->latest()
            ->paginate(15);

        $subjectIds = TeacherSubject::where('user_id', auth()->id())
            ->pluck('subject_id');

        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return view('teacher.materials', compact('materials', 'subjects'));
    }

    public function create()
    {
        $subjectIds = TeacherSubject::where('user_id', auth()->id())
            ->pluck('subject_id');

        $subjects = Subject::whereIn('id', $subjectIds)
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return view('teacher.materials-create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'subject_id'  => 'required|exists:subjects,id',
            'quarter'     => 'required|in:Q1,Q2,Q3,Q4',
            'week'        => 'nullable|integer|min:1|max:20',
            'description' => 'nullable|string|max:1000',
            'file'        => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,png,jpg,jpeg,mp4,zip',
        ]);

        $subject = Subject::findOrFail($request->subject_id);

        // Verify subject is actually assigned to this teacher
        $isAssigned = TeacherSubject::where('user_id', auth()->id())
            ->where('subject_id', $subject->id)
            ->exists();

        if (!$isAssigned) {
            return back()->withInput()->withErrors([
                'subject_id' => 'You are not assigned to teach this subject.',
            ]);
        }

        // Check if there are enrolled students in this grade level
        $hasStudents = User::where('role', 'student')
            ->where('status', 'approved')
            ->where('grade_level', $subject->grade_level)
            ->whereHas('studentEnrollment')
            ->exists();

        if (!$hasStudents) {
            return back()->withInput()->withErrors([
                'subject_id' => "No enrolled students found for Grade {$subject->grade_level}. You cannot upload materials until students are enrolled.",
            ]);
        }

        // File upload
        $filePath = null;
        $fileType = null;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file     = $request->file('file');
            $fileType = $file->getClientOriginalExtension();
            $filePath = $file->store('materials', 'public');
        }

        // Create material
        $material = LearningMaterial::create([
            'user_id'      => auth()->id(),
            'subject_id'   => $subject->id,
            'title'        => $request->title,
            'description'  => $request->description,
            'file_path'    => $filePath,
            'file_type'    => $fileType,
            'quarter'      => $request->quarter,
            'week'         => $request->week,
            'is_published' => $request->has('is_published'),
        ]);

        AuditLogService::log(
            "Uploaded material: {$material->title}",
            'Learning Materials'
        );

        // Notify students if published
        if ($material->is_published) {
            $students = User::where('role', 'student')
                ->where('status', 'approved')
                ->where('grade_level', $subject->grade_level)
                ->whereHas('studentEnrollment')
                ->get();

            foreach ($students as $student) {
                $student->notify(new ActivityAssigned(
                    title:      $material->title,
                    subject:    $subject->name,
                    instructor: auth()->user()->name,
                    type:       'module',
                    url:        '/student/modules',
                ));
            }
        }

        return redirect()->route('teacher.materials.index')
            ->with('success', "Material '{$material->title}' uploaded successfully.");
    }

    public function destroy(LearningMaterial $material)
    {

           if ($material->user_id !== auth()->id()) {
        abort(403, 'You can only delete your own materials.');
    }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        AuditLogService::log(
            "Deleted material: {$material->title}",
            'Learning Materials'
        );

        $material->delete();

        return back()->with('success', 'Material deleted successfully.');
    }

    public function show(LearningMaterial $material)
{
    abort_if($material->user_id !== auth()->id(), 403);

    $material->loadCount(['views', 'likes', 'comments']);

    $viewers = \App\Models\MaterialView::with('user')
        ->where('learning_material_id', $material->id)
        ->latest()->get();

    $comments = \App\Models\MaterialComment::with('user')
        ->where('learning_material_id', $material->id)
        ->latest()->get();

    // AJAX (modal) → JSON; normal request → the detail page (fallback, unchanged).
    if (request()->ajax() || request()->wantsJson()) {
        return response()->json([
            'title'         => $material->title,
            'subject'       => $material->subject->name ?? null,
            'quarter'       => $material->quarter,
            'week'          => $material->week,
            'description'   => $material->description,
            'is_published'  => (bool) $material->is_published,
            'view_count'    => $material->views_count,
            'like_count'    => $material->likes_count,
            'comment_count' => $material->comments_count,
            'viewers'  => $viewers->map(fn($v) => [
                'name' => $v->user->name ?? 'Student',
                'at'   => $v->created_at->diffForHumans(),
            ]),
            'comments' => $comments->map(fn($c) => [
                'name' => $c->user->name ?? 'Student',
                'body' => $c->body,
                'at'   => $c->created_at->diffForHumans(),
            ]),
        ]);
    }

    return view('teacher.materials-show', compact('material', 'viewers', 'comments'));
}

public function edit(LearningMaterial $material)
{
    abort_if($material->user_id !== auth()->id(), 403);

    $subjectIds = TeacherSubject::where('user_id', auth()->id())->pluck('subject_id');
    $subjects   = Subject::whereIn('id', $subjectIds)->where('is_active', true)->get();

    return view('teacher.materials-edit', compact('material', 'subjects'));
}

public function update(Request $request, LearningMaterial $material)
{
    abort_if($material->user_id !== auth()->id(), 403);

    $request->validate([
        'title'       => 'required|string|max:255',
        'subject_id'  => 'required|exists:subjects,id',
        'quarter'     => 'required|in:Q1,Q2,Q3,Q4',
        'week'        => 'nullable|integer|min:1|max:20',
        'description' => 'nullable|string|max:1000',
        'file'        => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,png,jpg,jpeg,mp4,zip',
    ]);

    if ($request->hasFile('file') && $request->file('file')->isValid()) {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->file_path = $request->file('file')->store('materials', 'public');
        $material->file_type = $request->file('file')->getClientOriginalExtension();
    }

    $material->update([
        'title'        => $request->title,
        'subject_id'   => $request->subject_id,
        'quarter'      => $request->quarter,
        'week'         => $request->week,
        'description'  => $request->description,
        'is_published' => $request->has('is_published'),
        'file_path'    => $material->file_path,
        'file_type'    => $material->file_type,
    ]);

    AuditLogService::log("Updated material: {$material->title}", 'Learning Materials');

    return redirect()->route('teacher.materials.index')
        ->with('success', "Material '{$material->title}' updated.");
}
}