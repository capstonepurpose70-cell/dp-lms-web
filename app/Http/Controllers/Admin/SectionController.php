<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin — Sections & Advisers.
 * The admin creates sections here and assigns each section's ADVISER (teacher).
 * The adviser reviews the enrollment requests of their section.
 */
class SectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = Section::with('adviser:id,name,email,status')
            ->withCount('students')
            ->get()
            // Numeric grade order (string sort would put "10" before "7")
            ->sortBy(fn ($s) => sprintf('%02d|%s', (int) $s->grade_level, mb_strtolower($s->name)))
            ->values();

        // Teachers who can be advisers
        $teachers = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $grades = ['7', '8', '9', '10', '11', '12'];

        return view('admin.sections.index', compact('sections', 'teachers', 'grades'));
    }

    /** The adviser must be a teacher — checked on create as well as update. */
    private function adviserRule(): array
    {
        return ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')];
    }

    /** No two sections with the same name in one grade. */
    private function uniqueNameRule(Request $request, ?Section $ignore = null)
    {
        $rule = Rule::unique('sections', 'name')->where('grade_level', (string) $request->input('grade_level'));
        return $ignore ? $rule->ignore($ignore->id) : $rule;
    }

    public function store(Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', $this->uniqueNameRule($request)],
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'adviser_id'  => $this->adviserRule(),
        ], [
            'adviser_id.exists' => 'The adviser must be a teacher.',
            'name.unique'       => 'A section with this name already exists in that grade level.',
        ]);

        $data['school_year'] = SchoolYear::current()?->label;
        $data['is_active']   = true;

        $section = Section::create($data);
        AuditLogService::log("Created section: {$section->name}", 'Sections');

        return back()->with('success', "Section {$section->name} created.");
    }

    public function update(Request $request, Section $section)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        // Unique check only when the name/grade changes — so an old duplicate row
        // does not block saving its adviser.
        $nameRules = ['required', 'string', 'max:100'];
        if ($request->input('name') !== $section->name || (string) $request->input('grade_level') !== (string) $section->grade_level) {
            $nameRules[] = $this->uniqueNameRule($request, $section);
        }

        $data = $request->validate([
            'name'        => $nameRules,
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'adviser_id'  => $this->adviserRule(),
            'is_active'   => 'nullable|boolean',
        ], [
            'adviser_id.exists' => 'The adviser must be a teacher.',
            'name.unique'       => 'A section with this name already exists in that grade level.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $section->update($data);

        AuditLogService::log("Updated section: {$section->name}", 'Sections',
            "Adviser ID: " . ($data['adviser_id'] ?? 'none'));

        return back()->with('success', "Section {$section->name} updated.");
    }

    public function destroy(Section $section)
    {
        // Sections cascade on delete into 7 tables (enrollments, quizzes, schedules…),
        // so a section is only deleted when nothing refers to it any more —
        // checking users.section_id alone would silently wipe that data.
        $inUse = [
            'students'             => User::where('section_id', $section->id)->count(),
            'enrollments'          => DB::table('student_enrollments')->where('section_id', $section->id)->count()
                                    + DB::table('enrollments')->where('section_id', $section->id)->count(),
            'quizzes/assignments'  => DB::table('assignments')->where('section_id', $section->id)->count()
                                    + DB::table('quizzes')->where('section_id', $section->id)->count(),
            'teacher assignments'  => DB::table('teacher_subjects')->where('section_id', $section->id)->count()
                                    + DB::table('teacher_assignments')->where('section_id', $section->id)->count(),
            'class schedules'      => DB::table('class_schedules')->where('section_id', $section->id)->count(),
        ];
        $inUse = array_filter($inUse);

        if ($inUse) {
            $list = collect($inUse)->map(fn ($n, $what) => "{$n} {$what}")->implode(', ');
            return back()->withErrors(['general' => "Cannot delete — this section still has {$list}. "
                . 'Deactivate it instead (uncheck "Active") or rename it.']);
        }

        $name = $section->name;
        $section->delete();
        AuditLogService::log("Deleted section: {$name}", 'Sections');

        return back()->with('success', "Section {$name} deleted.");
    }
}