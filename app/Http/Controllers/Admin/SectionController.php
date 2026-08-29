<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'grade_level' => 'required|string|in:7,8,9,10,11,12',
            'adviser_id'  => 'nullable|exists:users,id',
        ]);

        $data['school_year'] = optional(SchoolYear::current())->label ?? '2025-2026';
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
            'adviser_id'  => 'nullable|exists:users,id',
            'is_active'   => 'nullable|boolean',
        ]);

        // Ensure the adviser is actually a teacher
        if (!empty($data['adviser_id'])) {
            $isTeacher = User::where('id', $data['adviser_id'])
                ->where('role', 'teacher')->exists();
            if (!$isTeacher) {
                return back()->withErrors(['adviser_id' => 'The adviser must be a teacher.']);
            }
        }

        $data['is_active'] = $request->boolean('is_active');
        $section->update($data);

        AuditLogService::log("Updated section: {$section->name}", 'Sections',
            "Adviser ID: " . ($data['adviser_id'] ?? 'none'));

        return back()->with('success', "Section {$section->name} updated.");
    }

    public function destroy(Section $section)
    {
        // Huwag burahin kung may naka-assign na student
        $hasStudents = User::where('section_id', $section->id)->exists();
        if ($hasStudents) {
            return back()->withErrors(['general' => 'Cannot delete — students are assigned to this section.']);
        }

        $name = $section->name;
        $section->delete();
        AuditLogService::log("Deleted section: {$name}", 'Sections');

        return back()->with('success', "Section {$name} deleted.");
    }
}