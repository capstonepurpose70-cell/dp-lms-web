<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassAttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * What a parent may see about their linked children. Shared by the parent web pages and
 * /api/parent/*, and always scoped to $parent->children() — never to an id from the request.
 */
class ParentPortalService
{
    public const MAX_DAYS = 180;

    /** Children with everything the dashboard and records pages render. */
    public function children(User $parent, array $extra = []): EloquentCollection
    {
        return $parent->children()
            ->with(array_merge([
                'grades.subject:id,name,code',
                'section:id,name,grade_level',
                'studentEnrollment.section:id,name,grade_level',
                'enrollmentRequest:id,user_id,status,school_year,remarks,created_at',
            ], $extra))
            ->orderBy('name')
            ->get();
    }

    /** Latest announcements for the children's sections (none while no child has a section). */
    public function announcements(Collection $children, int $limit = 5): EloquentCollection
    {
        return Announcement::published()
            ->forAudience('parent')
            ->visibleToSections($children->map(fn (User $c) => $c->studentSectionId()))
            ->with('author:id,name')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Gate arrivals (face kiosk / scanner), newest first, keyed by child id.
     * Scans saved before the student was matched to an account carry only the email in student_id.
     */
    public function arrivals(Collection $children, int $days): Collection
    {
        $ids    = $children->pluck('id')->all();
        $emails = $children->pluck('email')->filter()->values()->all();

        $records = Attendance::where(function ($q) use ($ids, $emails) {
                $q->whereIn('user_id', $ids);
                if ($emails) {
                    $q->orWhereIn('student_id', $emails);
                }
            })
            ->where('attended_at', '>=', $this->since($days))
            ->orderByDesc('attended_at')
            ->get();

        return $children->mapWithKeys(fn (User $child) => [
            $child->id => $records->filter(fn (Attendance $a) => (int) $a->user_id === (int) $child->id
                || ($child->email && $a->student_id === $child->email))->values(),
        ]);
    }

    /** Per-class attendance taken by subject teachers, newest first, keyed by child id. */
    public function classAttendance(Collection $children, int $days): Collection
    {
        $records = ClassAttendanceRecord::with(['session.subject:id,name', 'session.teacher:id,name'])
            ->whereIn('student_id', $children->pluck('id'))
            ->where('created_at', '>=', $this->since($days))
            ->latest('id')
            ->get()
            ->groupBy('student_id');

        return $children->mapWithKeys(fn (User $child) => [$child->id => $records->get($child->id, collect())]);
    }

    /** Report card of the current school year, or null when the child has no grades in it yet. */
    public function currentYearCard(User $child): ?array
    {
        return GradeService::reportCard($child->grades)->get(GradeService::currentSchoolYear());
    }

    /**
     * Where the child stands with enrollment this school year:
     *   enrolled | pending (form under review) | returned (form rejected) | none
     */
    public function enrollmentState(User $child): string
    {
        $year    = GradeService::currentSchoolYear();
        $request = $child->enrollmentRequest;

        if ($child->studentEnrollment?->school_year === $year) {
            return 'enrolled';
        }
        if ($request?->status === 'pending') {
            return 'pending';
        }
        if ($request?->status === 'rejected' && $request->school_year === $year) {
            return 'returned';
        }

        return 'none';
    }

    public static function clampDays(mixed $days, int $default = 30): int
    {
        return min(max((int) ($days ?? $default), 1), self::MAX_DAYS);
    }

    private function since(int $days): Carbon
    {
        return now()->subDays(self::clampDays($days))->startOfDay();
    }
}
