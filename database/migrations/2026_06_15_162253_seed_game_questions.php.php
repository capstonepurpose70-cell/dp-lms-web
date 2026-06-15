<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Auto-seed the Strata Rush question bank on deploy (idempotent). */
    public function up(): void
    {
        if (class_exists(\Database\Seeders\GameQuestionSeeder::class)) {
            (new \Database\Seeders\GameQuestionSeeder())->run();
        }
    }

    public function down(): void
    {
        // leave questions in place
    }
};