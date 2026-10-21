<?php

namespace App\Services;

use App\Models\EnrollmentRequest;
use App\Models\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Section balancing — heterogeneous sections (principal: "hetero mix ang mga bata kaya
 * dapat maayos na nahahati ang section").
 *
 * For a grade level, summarises every active section: size, boys/girls, average general
 * average, and how many students fall in each DepEd descriptor band. The SUGGESTED
 * section for a new student is the one that keeps the mix most even:
 *   1) fewest students already in the student's band (spreads high/low performers),
 *   2) then smallest section,
 *   3) then fewest students of the same gender.
 * It is only a suggestion — the reviewer still picks the section.
 */
class SectionBalanceService
{
    /** DepEd descriptors (key => [label, min]) */
    public const BANDS = [
        'o'  => ['Outstanding (90–100)', 90],
        'vs' => ['Very Satisfactory (85–89)', 85],
        's'  => ['Satisfactory (80–84)', 80],
        'fs' => ['Fairly Satisfactory (75–79)', 75],
        'dn' => ['Did Not Meet (below 75)', 0],
    ];

    /** @var array<string, Collection> per-grade cache (one query per grade per request) */
    private array $cache = [];

    public static function band(?float $ga): ?string
    {
        if ($ga === null) {
            return null;
        }
        foreach (self::BANDS as $key => [, $min]) {
            if ($ga >= $min) {
                return $key;
            }
        }
        return 'dn';
    }

    /**
     * Stats for every active section of a grade.
     * @return Collection<int, array{section:Section, total:int, male:int, female:int, avg:?float, withGa:int, bands:array<string,int>}>
     */
    public function forGrade(string $grade): Collection
    {
        if (isset($this->cache[$grade])) {
            return $this->cache[$grade];
        }

        $sections = Section::with('adviser:id,name')
            ->where('is_active', true)
            ->where('grade_level', $grade)
            ->get()
            ->sortBy(fn ($s) => mb_strtolower($s->name))
            ->values();

        // Enrolled students of those sections with gender + general average from their profile
        $rows = DB::table('users')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('users.role', 'student')
            ->where('users.status', 'approved')
            ->whereIn('users.section_id', $sections->pluck('id'))
            ->get(['users.section_id', 'student_profiles.gender', 'student_profiles.general_average'])
            ->groupBy('section_id');

        return $this->cache[$grade] = $sections->map(function ($s) use ($rows) {
            $list  = $rows[$s->id] ?? collect();
            $gas   = $list->pluck('general_average')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
            $bands = array_fill_keys(array_keys(self::BANDS), 0);
            foreach ($gas as $ga) {
                $bands[self::band($ga)]++;
            }
            return [
                'section' => $s,
                'total'   => $list->count(),
                'male'    => $list->where('gender', 'Male')->count(),
                'female'  => $list->where('gender', 'Female')->count(),
                'avg'     => $gas->isNotEmpty() ? round($gas->avg(), 1) : null,
                'withGa'  => $gas->count(),
                'bands'   => $bands,
            ];
        });
    }

    /** Best section id for this request among $allowedIds (null = any section of the grade). */
    public function suggest(EnrollmentRequest $request, ?string $grade = null, ?array $allowedIds = null): ?int
    {
        $grade = $grade ?? (string) $request->grade_level;
        if (!in_array($grade, ['7', '8', '9', '10', '11', '12'], true)) {
            return null;
        }

        $band   = self::band($request->general_average !== null ? (float) $request->general_average : null);
        $gender = $request->gender;

        $best = $this->forGrade($grade)
            ->filter(fn ($st) => $allowedIds === null || in_array($st['section']->id, $allowedIds))
            ->sortBy(fn ($st) => sprintf(
                '%05d|%05d|%05d|%s',
                $band ? $st['bands'][$band] : 0,
                $st['total'],
                $gender === 'Male' ? $st['male'] : ($gender === 'Female' ? $st['female'] : 0),
                mb_strtolower($st['section']->name)
            ))
            ->first();

        return $best['section']->id ?? null;
    }
}
