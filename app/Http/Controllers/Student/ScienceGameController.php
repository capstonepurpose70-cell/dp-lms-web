<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GameQuestion;
use App\Models\GameScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScienceGameController extends Controller
{
    /** Only Grade 11 & 12 may play. */
    private function guardGrade(): string
    {
        $grade = (string) (Auth::user()->grade_level ?? '');
        abort_unless(in_array($grade, ['11', '12'], true), 403,
            'Ang Strata Rush Science Game ay para sa Grade 11 at Grade 12 lang.');
        return $grade;
    }

    private function worldFor(string $grade): string
    {
        return $grade === '11' ? 'Formula Clash' : 'Field Researcher';
    }

    /** Game page */
    public function index()
    {
        $grade = $this->guardGrade();

        return view('student.science-game', [
            'grade'   => $grade,
            'world'   => $this->worldFor($grade),
            'myBest'  => GameScore::where('user_id', Auth::id())->max('score') ?? 0,
        ]);
    }

    /** JSON: questions for this student's grade (shuffled) */
    public function questions(Request $request)
    {
        $grade = $this->guardGrade();

        $questions = GameQuestion::where('grade_level', $grade)
            ->get(['id', 'topic', 'difficulty', 'question', 'options', 'correct_index'])
            ->shuffle()
            ->values();

        return response()->json([
            'grade'     => $grade,
            'world'     => $this->worldFor($grade),
            'questions' => $questions,
        ]);
    }

    /** Save a finished session to the leaderboard */
    public function submitScore(Request $request)
    {
        $grade = $this->guardGrade();

        $data = $request->validate([
            'score'           => 'required|integer|min:0|max:1000000',
            'accuracy'        => 'required|numeric|min:0|max:100',
            'correct'         => 'required|integer|min:0|max:1000',
            'incorrect'       => 'required|integer|min:0|max:1000',
            'max_combo'       => 'required|integer|min:0|max:1000',
            'avg_response_ms' => 'required|integer|min:0|max:600000',
        ]);

        $score = GameScore::create([
            'user_id'         => Auth::id(),
            'grade_level'     => $grade,
            'world'           => $this->worldFor($grade),
            'score'           => $data['score'],
            'accuracy'        => $data['accuracy'],
            'correct'         => $data['correct'],
            'incorrect'       => $data['incorrect'],
            'max_combo'       => $data['max_combo'],
            'avg_response_ms' => $data['avg_response_ms'],
        ]);

        return response()->json([
            'ok'        => true,
            'id'        => $score->id,
            'personalBest' => GameScore::where('user_id', Auth::id())->max('score'),
        ]);
    }

    /** Leaderboard page (top scores for the student's grade) */
    public function leaderboard()
    {
        $grade = $this->guardGrade();

        $top = GameScore::with('user:id,name')
            ->where('grade_level', $grade)
            ->orderByDesc('score')
            ->orderByDesc('accuracy')
            ->limit(50)
            ->get();

        $myBest = GameScore::where('user_id', Auth::id())->max('score') ?? 0;

        return view('student.science-game-leaderboard', [
            'grade'   => $grade,
            'world'   => $this->worldFor($grade),
            'top'     => $top,
            'myBest'  => $myBest,
        ]);
    }
}