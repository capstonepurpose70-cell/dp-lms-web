<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Idagdag at gawing aktibo ang S.Y. 2026-2027.
 *
 * Bakit kailangan: ang seeder noong una ay 2025-2026 lang ang ginawa, at
 * walang admin UI para makagawa ng bagong school year. Ang lahat ng bagong
 * section, enrollment, at teacher assignment ay kinukuha ang school year
 * mula sa SchoolYear::current() — kaya kung mali ito, mali rin lahat ng
 * mabubuong record.
 *
 * Ligtas patakbuhin nang paulit-ulit (idempotent).
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

        // Iisa lang dapat ang aktibo — ang SchoolYear::current() ay
        // ->where('is_active', true)->first(), kaya kung dalawa ang aktibo,
        // hindi matitiyak kung alin ang makukuha.
        DB::table('school_years')->update(['is_active' => false, 'updated_at' => now()]);

        DB::table('school_years')
            ->where('label', self::LABEL)
            ->update(['is_active' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Ibalik ang dating aktibo. Hindi binubura ang 2026-2027 dahil
        // maaaring may mga record nang nakakabit dito.
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