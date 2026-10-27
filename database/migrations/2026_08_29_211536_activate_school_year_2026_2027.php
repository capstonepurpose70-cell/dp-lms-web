<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds S.Y. 2026-2027 and makes it the active school year.
 *
 * Why: the first seeder only created 2025-2026, and there is no admin UI to
 * create a new school year. Every new section, enrollment and teacher
 * assignment takes its school year from SchoolYear::current() — so if that
 * is wrong, every new record is wrong too.
 *
 * Safe to run more than once (idempotent).
 */
return new class extends Migration
{
    private const LABEL = '2026-2027';

    public function up(): void
    {
        $existing = DB::table('school_years')->where('label', self::LABEL)->first();

        if (!$existing) {
            DB::table('school_years')->insert([
                'label'      => self::LABEL,
                'starts_at'  => '2026-06-01',
                'ends_at'    => '2027-03-31',
                'is_active'  => false,   // ia-activate sa ibaba
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Only one may be active — SchoolYear::current() is
        // ->where('is_active', true)->first(), so with two active ones
        // there is no telling which one is returned.
        DB::table('school_years')->update(['is_active' => false, 'updated_at' => now()]);

        DB::table('school_years')
            ->where('label', self::LABEL)
            ->update(['is_active' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Re-activate the previous year. 2026-2027 is not deleted because
        // records may already point to it.
        DB::table('school_years')->update(['is_active' => false, 'updated_at' => now()]);

        $fallback = DB::table('school_years')
            ->where('label', '<>', self::LABEL)
            ->orderByDesc('starts_at')
            ->first();

        if ($fallback) {
            DB::table('school_years')
                ->where('id', $fallback->id)
                ->update(['is_active' => true, 'updated_at' => now()]);
        }
    }
};