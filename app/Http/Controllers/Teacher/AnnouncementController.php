<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\TeacherSubject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('user_id', auth()->id())
            ->latest()->paginate(10);
        return view('teacher.announcements', compact('announcements'));
    }

    public function store(Request $request)
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
        ]);

        // 🔔 Push notification (FCM) to students in the teacher's section(s).
        if ($request->audience !== 'parents') {
            // Sections this teacher handles — BOTH subject AND faculty assignments.
            $annSectionIds = TeacherSubject::where('user_id', auth()->id())->pluck('section_id')
                ->merge(TeacherAssignment::where('user_id', auth()->id())->pluck('section_id'))
                ->filter()->unique();

            $annQuery = User::where('role', 'student')
                ->where('status', 'approved')
                ->whereIn('section_id', $annSectionIds);

            if ($request->filled('section_id')) {
                $annQuery->where('section_id', $request->section_id);
            }

            $teacherName = auth()->user()->name;
            app(PushNotificationService::class)->sendToUsers(
                $annQuery->pluck('id')->all(),
                '📢 ' . $announcement->title,
                'Mula kay ' . $teacherName . ': ' . $announcement->body,
                ['type' => 'announcement', 'id' => $announcement->id, 'teacher' => $teacherName],
            );
        }

        AuditLogService::log(
            "Posted announcement: {$announcement->title}",
            'Announcements'
        );

        return back()->with('success', 'Announcement posted successfully.');
    }

    public function show(Announcement $announcement)
    {
        abort_if($announcement->user_id !== auth()->id(), 403);
        return view('teacher.announcements', compact('announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        abort_if($announcement->user_id !== auth()->id(), 403);

        AuditLogService::log("Deleted announcement: {$announcement->title}", 'Announcements');
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}