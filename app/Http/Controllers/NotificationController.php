<?php

namespace App\Http\Controllers;

/**
 * In-app notifications (bell icon) — student at teacher.
 * Dati: magkaparehong closure sa routes/web.php para sa bawat role; ang sa
 * student ay walang `?? ''` kaya nag-e-error sa notification na kulang ang key.
 */
class NotificationController extends Controller
{
    public function data()
    {
        $user = auth()->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $user->unreadNotifications()->take(10)->get()->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? '',
                'subject'    => $n->data['subject'] ?? '',
                'instructor' => $n->data['instructor'] ?? '',
                'type'       => $n->data['type'] ?? 'module',
                'url'        => $n->data['url'] ?? '#',
                'time'       => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * GET /api/notifications — para sa Flutter: huling 50 (nabasa man o hindi),
     * kasama ang buong `data` (hal. arrival: student_name, status, attended_at).
     */
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $user->notifications()->take(50)->get()->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->data['type'] ?? 'module',
                'title'      => $n->data['title'] ?? '',
                'data'       => $n->data,
                'read'       => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
                'time'       => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function read(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
