<?php

namespace App\Http\Controllers;

/**
 * In-app notifications (bell icon) — students and teachers.
 * One controller for every role; missing keys in a notification's data are tolerated.
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
     * GET /api/notifications — for Flutter: the last 50 (read or not), with
     * the full `data` (e.g. arrival: student_name, status, attended_at).
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
