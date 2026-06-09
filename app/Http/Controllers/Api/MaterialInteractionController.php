<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\MaterialView;
use App\Models\MaterialLike;
use App\Models\MaterialComment;
use Illuminate\Http\Request;

/**
 * Handles student/teacher interactions on learning materials:
 * read receipts (views), hearts (likes), and comments.
 *
 * This is a NEW, self-contained controller — it does not modify any
 * existing materials logic. All counts are queried directly by
 * learning_material_id so no existing models/methods are affected.
 */
class MaterialInteractionController extends Controller
{
    // ── STUDENT: record a view (read receipt). Safe to call repeatedly. ──
    public function studentView($id)
    {
        LearningMaterial::findOrFail($id);

        MaterialView::firstOrCreate([
            'learning_material_id' => $id,
            'user_id'              => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── STUDENT: toggle heart/like. ──
    public function studentToggleLike($id)
    {
        LearningMaterial::findOrFail($id);

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
        LearningMaterial::findOrFail($id);

        $request->validate(['body' => 'required|string|max:1000']);

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
        LearningMaterial::findOrFail($id);
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

    // ── TEACHER: detail with viewers list (owner only). ──
    public function teacherDetail($id)
    {
        // Ensure the requesting teacher owns this material.
        LearningMaterial::where('user_id', auth()->id())->findOrFail($id);

        $viewers = MaterialView::with('user')
            ->where('learning_material_id', $id)
            ->latest()->get()
            ->map(fn($v) => [
                'user'      => $v->user?->name,
                'viewed_at' => $v->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'view_count'    => $viewers->count(),
            'like_count'    => MaterialLike::where('learning_material_id', $id)->count(),
            'comment_count' => MaterialComment::where('learning_material_id', $id)->count(),
            'viewers'       => $viewers,
            'comments'      => $this->formatComments($id, auth()->id()),
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