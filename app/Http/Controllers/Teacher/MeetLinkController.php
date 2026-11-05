<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\ActionNotAllowed;
use App\Http\Controllers\Controller;
use App\Services\MeetLinkService;
use Illuminate\Http\Request;

class MeetLinkController extends Controller
{
    private const CLASS_RULE = ['required', 'regex:/^\d+-\d+$/'];   // "{section_id}-{subject_id}"

    public function index(MeetLinkService $links)
    {
        return view('teacher.meet', ['classes' => $links->forTeacher(auth()->user())]);
    }

    public function store(Request $request, MeetLinkService $links)
    {
        $data = $request->validate(['class' => self::CLASS_RULE, 'url' => 'required|string|max:255'], [
            'url.required' => 'Paste the Google Meet link first.',
        ]);
        [$sectionId, $subjectId] = array_map('intval', explode('-', $data['class']));

        try {
            $link = $links->publish(auth()->user(), $sectionId, $subjectId, $data['url']);
        } catch (ActionNotAllowed $e) {
            return $request->expectsJson() ? $e->toJson() : back()->with('error', $e->getMessage());
        }

        $message = 'Link published. The class can see it on their dashboard.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'url' => $link->url, 'message' => $message])
            : back()->with('success', $message);
    }

    public function destroy(Request $request, MeetLinkService $links)
    {
        $data = $request->validate(['class' => self::CLASS_RULE]);
        [$sectionId, $subjectId] = array_map('intval', explode('-', $data['class']));

        try {
            $links->remove(auth()->user(), $sectionId, $subjectId);
        } catch (ActionNotAllowed $e) {
            return $request->expectsJson() ? $e->toJson() : back()->with('error', $e->getMessage());
        }

        $message = 'Link removed.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
