<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('user_id', auth()->id())
            ->latest()->paginate(10);
        return view('teacher.announcements', compact('announcements'));
    }

    public function store(Request $request, StudentNotifier $notifier)
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

        // 🔔 Push (FCM) to students in the teacher's section(s). Never breaks posting.
        if ($request->audience !== 'parents') {
            $sectionIds = $request->filled('section_id') ? [(int) $request->section_id] : $teachingIds;

            $notifier->announcement(
                User::studentIdsInSections($sectionIds),
                $announcement->title,
                $announcement->body,
                $announcement->id,
                auth()->user()->name,
            );
        }

        AuditLogService::log(
            "Posted announcement: {$announcement->title}",
            'Announcements'
        );

        return back()->with('success', 'Announcement posted successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_if($announcement->user_id !== auth()->id(), 403);

        AuditLogService::log("Deleted announcement: {$announcement->title}", 'Announcements');
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}
