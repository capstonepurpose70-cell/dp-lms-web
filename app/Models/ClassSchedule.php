<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Isang class period sa weekly schedule ng isang section.
 */
class ClassSchedule extends Model
{
    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'school_year',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    /** 1 = Lunes ... 5 = Biyernes */
    public const DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? '—';
    }

    /** "8:00 AM – 9:00 AM" */
    public function getTimeRangeAttribute(): string
    {
        return self::formatTime($this->start_time) . ' – ' . self::formatTime($this->end_time);
    }

    /** Tanggapin ang "08:00", "08:00:00" o Carbon; ilabas ang "8:00 AM". */
    public static function formatTime($time): string
    {
        if (empty($time)) {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse((string) $time)->format('g:i A');
        } catch (\Throwable $e) {
            return (string) $time;
        }
    }

    // ─── Overlap detection ────────────────────────────────────────────────────

    /**
     * Nagsasapawan ba ang dalawang time range?
     *
     * Ang tamang tuntunin ay:  bagoStart < lumaEnd  AT  bagoEnd > lumaStart
     *
     * HINDI sapat ang paghahambing ng eksaktong oras lamang. Ang klaseng
     * 8:00–9:00 ay sumasapaw sa 8:30–9:30 kahit walang magkaparehong oras.
     * Pansinin din na ang 8:00–9:00 at 9:00–10:00 ay HINDI magkasapaw —
     * magkasunod lang sila, kaya `<` at `>` ang ginagamit, hindi `<=`.
     */
    public function scopeOverlapping(Builder $q, string $start, string $end): Builder
    {
        return $q->where('start_time', '<', $end)
                 ->where('end_time', '>', $start);
    }

    /**
     * Hanapin ang unang sagabal para sa isang bagong (o ina-update na) entry.
     * Ibabalik ang isang tao/bagay na abala, o null kung malinis.
     *
     * @return array{type:string, schedule:ClassSchedule}|null
     */
    public static function findConflict(array $data, ?int $ignoreId = null): ?array
    {
        $base = fn () => static::query()
            ->where('school_year', $data['school_year'])
            ->where('day_of_week', $data['day_of_week'])
            ->overlapping($data['start_time'], $data['end_time'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->with(['subject', 'section', 'teacher']);

        // 1. Hindi mahahati sa dalawa ang isang section
        if ($hit = $base()->where('section_id', $data['section_id'])->first()) {
            return ['type' => 'section', 'schedule' => $hit];
        }

        // 2. Hindi makakapunta sa dalawang silid ang isang guro
        if (!empty($data['teacher_id'])) {
            if ($hit = $base()->where('teacher_id', $data['teacher_id'])->first()) {
                return ['type' => 'teacher', 'schedule' => $hit];
            }
        }

        // 3. Hindi pwedeng dalawang klase sa iisang silid
        if (!empty($data['room'])) {
            if ($hit = $base()->where('room', $data['room'])->first()) {
                return ['type' => 'room', 'schedule' => $hit];
            }
        }

        return null;
    }
}