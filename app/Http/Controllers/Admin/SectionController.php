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
 * Dito ginagawa ng admin ang sections at ina-assign ang ADVISER (teacher)
 * ng bawat section. Ang adviser ang mag-rereview ng enrollment ng section nila.
 */
class SectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = Section::with('adviser:id,name,email')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        // Mga teacher na pwedeng maging adviser
        $teachers = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $grades = ['7', '8', '9', '10', '11', '12'];

        return view('admin.sections.index', compact('sections', 'teachers', 'grades'));
    }

    /** Dapat teacher ang adviser (dati sa update lang may tsek — nalalampasan sa create). */
    private function adviserRule(): array
    {
        return ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'adviser_id'  => $this->adviserRule(),
        ], ['adviser_id.exists' => 'The adviser must be a teacher.']);

        $data['school_year'] = SchoolYear::current()?->label;
        $data['is_active']   = true;

        $section = Section::create($data);
        AuditLogService::log("Created section: {$section->name}", 'Sections');

        return back()->with('success', "Section {$section->name} created.");
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'adviser_id'  => $this->adviserRule(),
            'is_active'   => 'nullable|boolean',
        ], ['adviser_id.exists' => 'The adviser must be a teacher.']);

        $data['is_active'] = $request->boolean('is_active');
        $section->update($data);

        AuditLogService::log("Updated section: {$section->name}", 'Sections',
            "Adviser ID: " . ($data['adviser_id'] ?? 'none'));

        return back()->with('success', "Section {$section->name} updated.");
    }

    public function destroy(Section $section)
    {
        // Dati users.section_id lang ang tsine-check — pero cascadeOnDelete ang
        // sections sa 7 table: tahimik na nabubura ang enrollments, quizzes,
        // schedules, atbp. Ngayon: hindi mabubura hangga't may kaugnay na datos.
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