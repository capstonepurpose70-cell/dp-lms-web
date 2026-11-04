<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Senior High runs in two semesters. A class with no semester runs the whole year (Junior High);
 * the school year records which semester is current, so students see that semester's timetable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->nullable()->after('school_year');
        });
        Schema::table('school_years', function (Blueprint $table) {
            $table->unsignedTinyInteger('current_semester')->default(1)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('class_schedules', fn (Blueprint $table) => $table->dropColumn('semester'));
        Schema::table('school_years', fn (Blueprint $table) => $table->dropColumn('current_semester'));
    }
};
