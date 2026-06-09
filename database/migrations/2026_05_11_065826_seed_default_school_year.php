<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only insert if no school year exists yet — safe to re-run
        if (DB::table('school_years')->count() === 0) {
            DB::table('school_years')->insert([
                'label'      => '2025-2026',
                'starts_at'  => '2025-06-01',
                'ends_at'    => '2026-03-31',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('school_years')->where('label', '2025-2026')->delete();
    }
};