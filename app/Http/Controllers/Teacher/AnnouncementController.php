<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AuditLogService;
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
            'user_id'   => auth()->id(),
            'title'     => $request->title,
            'body'       => $request->body,
            'audience'  => $request->audience,
            'section_id'=> $request->section_id,
        ]);

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