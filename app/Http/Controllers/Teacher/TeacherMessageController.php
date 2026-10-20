<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Rules\NoBadWords;
use Illuminate\Http\Request;

class TeacherMessageController extends Controller
{
    /** Student IDs this teacher is allowed to message (same-section scope, incl. advisory). */
    private function allowedStudentIds($me)
    {
        return User::studentIdsInSections($me->teachingSectionIds());
    }

    private function sectionNameFor($student)
    {
        return $student->studentEnrollment?->section?->name ?? $student->section?->name;
    }

    /** GET /teacher/messages  (optionally ?with={studentId}) */
    public function index(Request $request)
    {
        $me = auth()->user();

        // ── Inbox: one row per person, newest first ──────────────────────────
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

        $conversations = [];
        foreach ($threads as $oid => $t) {
            $u = $users[$oid] ?? null;
            if (! $u) {
                continue;
            }
            $conversations[] = [
                'id'           => $u->id,
                'name'         => $u->name,
                'section'      => $this->sectionNameFor($u),
                'last_message' => $t['last_body'],
                'last_at'      => $t['last_at']?->diffForHumans(),
                'last_mine'    => $t['last_mine'],
                'unread'       => $t['unread'],
            ];
        }

        // ── Optional open thread ─────────────────────────────────────────────
        $activeId   = $request->query('with');
        $activeUser = null;
        $messages   = collect();

        if ($activeId) {
            $activeUser = User::find($activeId);
            if ($activeUser) {
                Message::where('sender_id', $activeUser->id)
                    ->where('receiver_id', $me->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);

                $messages = Message::where(function ($q) use ($me, $activeUser) {
                        $q->where('sender_id', $me->id)->where('receiver_id', $activeUser->id);
                    })
                    ->orWhere(function ($q) use ($me, $activeUser) {
                        $q->where('sender_id', $activeUser->id)->where('receiver_id', $me->id);
                    })
                    ->orderBy('created_at')
                    ->get();
            }
        }

        // Students available to start a new conversation
        $contacts = User::whereIn('id', $this->allowedStudentIds($me))
            ->orderBy('name')->get(['id', 'name']);

        return view('teacher.messages', [
            'conversations' => $conversations,
            'contacts'      => $contacts,
            'activeUser'    => $activeUser,
            'activeSection' => $activeUser ? $this->sectionNameFor($activeUser) : null,
            'messages'      => $messages,
            'me'            => $me,
        ]);
    }

    /** POST /teacher/messages */
    public function store(Request $request)
    {
        $me = auth()->user();

        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => ['required', 'string', 'max:2000', new NoBadWords],
        ]);

        if (! $this->allowedStudentIds($me)->contains((int) $data['receiver_id'])) {
            return back()
                ->withErrors(['body' => 'You can only message students in your sections.'])
                ->withInput();
        }

        Message::create([
            'sender_id'   => $me->id,
            'receiver_id' => $data['receiver_id'],
            'body'        => $data['body'],
            'is_read'     => false,
        ]);

        return redirect()
            ->route('teacher.messages', ['with' => $data['receiver_id']])
            ->with('success', 'Message sent!');
    }
}