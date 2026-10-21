<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section balancing (heterogeneous sections): the student's general average from
 * the last school year. Optional on the enrollment form; copied to the profile on
 * approval so each section's mix of performance levels can be shown to reviewers.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['enrollment_requests', 'student_profiles'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'general_average')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->decimal('general_average', 5, 2)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['enrollment_requests', 'student_profiles'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'general_average')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('general_average');
                });
            }
        }
    }
};
