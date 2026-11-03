<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The section a student is listed in (from the school's class lists): their enrollment form goes to that adviser. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('official_lrns', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->index()->after('grade_level');
        });
    }

    public function down(): void
    {
        Schema::table('official_lrns', function (Blueprint $table) {
            $table->dropIndex(['section_id']);
            $table->dropColumn('section_id');
        });
    }
};
