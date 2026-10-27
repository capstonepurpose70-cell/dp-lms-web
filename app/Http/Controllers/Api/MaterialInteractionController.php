<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\MaterialView;
use App\Models\MaterialLike;
use App\Models\MaterialComment;
use App\Models\TeacherSubject;
use App\Rules\NoBadWords;
use Illuminate\Http\Request;

/**
 * Handles student interactions on learning materials:
 * read receipts (views), hearts (likes), and comments.
 *
 * All counts are queried directly by learning_material_id.
 */
class MaterialInteractionController extends Controller
{
    /**
     * A material the student can see: published, and its subject is taught in
     * the student's section (same as /api/student/modules) — so likes and
     * comments cannot reach unpublished or other sections' materials by id.
     */
    private function studentMaterial($id): LearningMaterial
    {
        $sectionId  = auth()->user()->studentSectionId();
        $subjectIds = $sectionId
            ? TeacherSubject::where('section_id', $sectionId)->pluck('subject_id')
            : collect();

        return LearningMaterial::where('is_published', true)
            ->whereIn('subject_id', $subjectIds)
            ->findOrFail($id);
    }

    // ── STUDENT: record a view (read receipt). Safe to call repeatedly. ──
    public function studentView($id)
    {
        $this->studentMaterial($id);

        MaterialView::firstOrCreate([
            'learning_material_id' => $id,
            'user_id'              => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── STUDENT: toggle heart/like. ──
    public function studentToggleLike($id)
    {
        $this->studentMaterial($id);

        $existing = MaterialLike::where('learning_material_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            MaterialLike::create([
                'learning_material_id' => $id,
                'user_id'              => auth()->id(),
            ]);
            $liked = true;
        }

        return response()->json([
            'liked'      => $liked,
            'like_count' => MaterialLike::where('learning_material_id', $id)->count(),
        ]);
    }

    // ── STUDENT: add a comment. ──
    public function studentAddComment(Request $request, $id)
    {
        $this->studentMaterial($id);

        $request->validate(['body' => ['required', 'string', 'max:1000', new NoBadWords]]);

        $comment = MaterialComment::create([
            'learning_material_id' => $id,
            'user_id'              => auth()->id(),
            'body'                 => $request->body,
        ]);

        return response()->json([
            'id'         => $comment->id,
            'user'       => auth()->user()->name,
            'body'       => $comment->body,
            'created_at' => 'just now',
            'mine'       => true,
        ], 201);
    }

    // ── STUDENT: material detail (counts + liked_by_me + comments). ──
    public function studentDetail($id)
    {
        $this->studentMaterial($id);
        $uid = auth()->id();

        return response()->json([
            'like_count'    => MaterialLike::where('learning_material_id', $id)->count(),
            'liked_by_me'   => MaterialLike::where('learning_material_id', $id)
                                    ->where('user_id', $uid)->exists(),
            'view_count'    => MaterialView::where('learning_material_id', $id)->count(),
            'comment_count' => MaterialComment::where('learning_material_id', $id)->count(),
            'comments'      => $this->formatComments($id, $uid),
        ]);
    }

    // ── Shared comment formatter. ──
    private function formatComments($id, $uid)
    {
        return MaterialComment::with('user')
            ->where('learning_material_id', $id)
            ->latest()->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'user'       => $c->user?->name,
                'body'       => $c->body,
                'created_at' => $c->created_at?->diffForHumans(),
                'mine'       => $c->user_id == $uid,
            ]);
    }
}
