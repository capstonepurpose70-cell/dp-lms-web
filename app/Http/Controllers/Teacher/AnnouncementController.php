<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ParentNotifier;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('section:id,name,grade_level')
            ->where('user_id', auth()->id())
            ->latest()->paginate(10);

        // Para sa "Send to" — dati walang section selector, kaya laging school-wide
        $sections = Section::whereIn('id', auth()->user()->teachingSectionIds())
            ->orderByRaw('CAST(grade_level AS UNSIGNED)')->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        return view('teacher.announcements', compact('announcements', 'sections'));
    }

    public function store(Request $request, StudentNotifier $students, ParentNotifier $parents)
    {
        $teachingIds = auth()->user()->teachingSectionIds();

        $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|max:20000',
            'audience'   => 'required|in:all,students,parents',
            // Sariling section lang ang puwedeng i-target
            'section_id' => ['nullable', 'integer', Rule::in($teachingIds->all())],
        ], ['section_id.in' => 'You can only post to your own sections.']);

        $announcement = Announcement::create([
            'user_id'      => auth()->id(),
            'title'        => $request->title,
            'body'         => $request->body,
            'audience'     => $request->audience,
            'section_id'   => $request->section_id,
            'is_published' => true,
        ]);

        // 🔔 Push (FCM) to the students and/or parents of the target section(s). Never breaks posting.
        $sectionIds = $request->filled('section_id') ? [(int) $request->section_id] : $teachingIds;
        $studentIds = User::studentIdsInSections($sectionIds);

        if ($request->audience !== 'parents') {
            $students->announcement($studentIds, $announcement->title, $announcement->body, $announcement->id, auth()->user()->name);
        }
        if ($request->audience !== 'students') {
            $parents->announcement($studentIds, $announcement->title, $announcement->body, $announcement->id, auth()->user()->name);
        }

        AuditLogService::log(
            "Posted announcement: {$announcement->title}",
            'Announcements',
            $announcement->target_label
        );

        return back()->with('success', 'Announcement posted successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_if((int) $announcement->user_id !== (int) auth()->id(), 403);

        AuditLogService::log("Deleted announcement: {$announcement->title}", 'Announcements');
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}
