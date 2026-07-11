<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\TeacherAssignment;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /** Who the current user is allowed to message (IDs) */
    private function allowedContactIds($me)
    {
        if ($me->role === 'student') {
            $sectionId = $me->studentEnrollment?->section?->id ?? $me->section_id;
            if (! $sectionId) {
                return collect();
            }
            // Teachers who teach this student's section
            return TeacherSubject::where('section_id', $sectionId)->pluck('user_id')
                ->merge(TeacherAssignment::where('section_id', $sectionId)->pluck('user_id'))
                ->filter()->unique()->values();
        }

        if ($me->role === 'teacher') {
            $sectionIds = TeacherSubject::where('user_id', $me->id)->pluck('section_id')
                ->merge(TeacherAssignment::where('user_id', $me->id)->pluck('section_id'))
                ->filter()->unique()->values();
            if ($sectionIds->isEmpty()) {
                return collect();
            }
            // Students inside the teacher's sections (direct or via enrollment)
            $direct = User::where('role', 'student')
                ->whereIn('section_id', $sectionIds)->pluck('id');
            $viaEnroll = User::where('role', 'student')
                ->whereHas('studentEnrollment', fn($q) => $q->whereIn('section_id', $sectionIds))
                ->pluck('id');
            return $direct->merge($viaEnroll)->filter()->unique()->values();
        }

        return collect();
    }

    /** Compact display info for a user */
    private function userCard($u)
    {
        $section = null;
        if ($u->role === 'student') {
            $section = $u->studentEnrollment?->section?->name ?? $u->section?->name;
        }
        return [
            'id'      => $u->id,
            'name'    => $u->name,
            'role'    => $u->role,
            'section' => $section,
        ];
    }

    /** GET /api/messages — inbox: one row per person, newest first */
    public function conversations(Request $request)
    {
        $me = $request->user();

        $msgs = Message::where('sender_id', $me->id)
            ->orWhere('receiver_id', $me->id)
            ->orderByDesc('created_at')
            ->get();

        $threads = [];
        foreach ($msgs as $m) {
            $otherId = $m->sender_id === $me->id ? $m->receiver_id : $m->sender_id;
            if (! isset($threads[$otherId])) {
                $threads[$otherId] = [
                    'last_body' => $m->body,
                    'last_at'   => $m->created_at,
                    'last_mine' => $m->sender_id === $me->id,
                    'unread'    => 0,
                ];
            }
            if ($m->receiver_id === $me->id && ! $m->is_read) {
                $threads[$otherId]['unread']++;
            }
        }

        $users = User::whereIn('id', array_keys($threads))->get()->keyBy('id');

        $out = [];
        foreach ($threads as $oid => $t) {
            $u = $users[$oid] ?? null;
            if (! $u) {
                continue;
            }
            $out[] = array_merge($this->userCard($u), [
                'last_message' => $t['last_body'],
                'last_at'      => $t['last_at']?->diffForHumans(),
                'last_mine'    => $t['last_mine'],
                'unread'       => $t['unread'],
            ]);
        }

        return response()->json($out);
    }

    /** GET /api/messages/contacts — people the user can start a chat with */
    public function contacts(Request $request)
    {
        $me  = $request->user();
        $ids = $this->allowedContactIds($me);

        $users = User::whereIn('id', $ids)->orderBy('name')->get();

        return response()->json(
            $users->map(fn($u) => $this->userCard($u))->values()
        );
    }

    /** GET /api/messages/thread/{user} — full conversation, marks incoming as read */
    public function thread(Request $request, $userId)
    {
        $me    = $request->user();
        $other = User::findOrFail($userId);

        // mark messages from the other person as read
        Message::where('sender_id', $other->id)
            ->where('receiver_id', $me->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $messages = Message::where(function ($q) use ($me, $other) {
                $q->where('sender_id', $me->id)->where('receiver_id', $other->id);
            })
            ->orWhere(function ($q) use ($me, $other) {
                $q->where('sender_id', $other->id)->where('receiver_id', $me->id);
            })
            ->with('replyTo:id,body,file_name,sender_id')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'body'          => $m->body,
                'file_url'      => $m->file_path ? asset('storage/' . $m->file_path) : null,
                'file_name'     => $m->file_name,
                'mine'          => $m->sender_id === $me->id,
                'at'            => $m->created_at?->toDateTimeString(),
                'at_human'      => $m->created_at?->diffForHumans(),
                'time'          => $m->created_at?->format('g:i A'),
                'date'          => $m->created_at?->format('M j, Y'),
                // Swipe-to-reply context (null when not a reply)
                'reply_to_id'   => $m->reply_to_id,
                'reply_snippet' => $m->replyTo
                    ? ($m->replyTo->body !== '' && $m->replyTo->body !== null
                        ? \Illuminate\Support\Str::limit($m->replyTo->body, 80)
                        : ($m->replyTo->file_name ?? 'Attachment'))
                    : null,
                'reply_mine'    => $m->replyTo ? $m->replyTo->sender_id === $me->id : null,
            ]);

        return response()->json([
            'contact'     => $this->userCard($other),
            'messages'    => $messages,
            // Is the OTHER person typing to ME right now? (short-lived cache)
            'peer_typing' => (bool) \Illuminate\Support\Facades\Cache::get(
                'chat_typing:' . $other->id . ':' . $me->id, false),
        ]);
    }

    /** POST /api/messages/thread/{user} — send a message */
    public function send(Request $request, $userId)
    {
        $me = $request->user();

        $data = $request->validate([
            'body'        => 'nullable|string|max:2000',
            'file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,jpg,jpeg,png,zip',
            'reply_to_id' => 'nullable|integer',
        ]);

        if (blank($data['body'] ?? null) && ! $request->hasFile('file')) {
            return response()->json([
                'ok'      => false,
                'message' => 'Message cannot be empty.',
            ], 422);
        }

        $other = User::findOrFail($userId);

        // The recipient must be an allowed contact (same section scope)
        if (! $this->allowedContactIds($me)->contains((int) $other->id)) {
            return response()->json([
                'ok'      => false,
                'message' => 'You are not allowed to message this user.',
            ], 403);
        }

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat_files', 'public');
            $fileName = $request->file('file')->getClientOriginalName();
        }

        // Swipe-to-reply: only accept a reply target that belongs to THIS thread.
        $replyToId = null;
        $replyTo   = null;
        if (!empty($data['reply_to_id'])) {
            $replyTo = Message::where('id', (int) $data['reply_to_id'])
                ->where(function ($q) use ($me, $other) {
                    $q->where(function ($q2) use ($me, $other) {
                        $q2->where('sender_id', $me->id)->where('receiver_id', $other->id);
                    })->orWhere(function ($q2) use ($me, $other) {
                        $q2->where('sender_id', $other->id)->where('receiver_id', $me->id);
                    });
                })
                ->first();
            $replyToId = $replyTo?->id;
        }

        $msg = Message::create([
            'sender_id'   => $me->id,
            'receiver_id' => $other->id,
            'body'        => $data['body'] ?? '',
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'reply_to_id' => $replyToId,
            'is_read'     => false,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => [
                'id'            => $msg->id,
                'body'          => $msg->body,
                'file_url'      => $filePath ? asset('storage/' . $filePath) : null,
                'file_name'     => $fileName,
                'mine'          => true,
                'at'            => $msg->created_at?->toDateTimeString(),
                'at_human'      => $msg->created_at?->diffForHumans(),
                'time'          => $msg->created_at?->format('g:i A'),
                'date'          => $msg->created_at?->format('M j, Y'),
                'reply_to_id'   => $replyToId,
                'reply_snippet' => $replyTo
                    ? ($replyTo->body !== '' && $replyTo->body !== null
                        ? \Illuminate\Support\Str::limit($replyTo->body, 80)
                        : ($replyTo->file_name ?? 'Attachment'))
                    : null,
                'reply_mine'    => $replyTo ? $replyTo->sender_id === $me->id : null,
            ],
        ]);
    }

    /** POST /api/messages/typing/{user} — "I am typing to this user" ping */
    public function typing(Request $request, $userId)
    {
        $me = $request->user();
        \Illuminate\Support\Facades\Cache::put(
            'chat_typing:' . $me->id . ':' . (int) $userId,
            true,
            now()->addSeconds(5)
        );

        return response()->json(['ok' => true]);
    }
}