<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hiwalay na column para sa feedback ng teacher.
 *
 * Dati iisang column (`remarks`) ang gamit sa SAGOT ng estudyante at sa
 * FEEDBACK ng teacher — kaya pagka-grade, napapalitan ang sagot ng estudyante.
 * Ngayon: remarks = sagot ng estudyante, feedback = komento ng teacher.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->text('feedback')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('feedback');
        });
    }
};
