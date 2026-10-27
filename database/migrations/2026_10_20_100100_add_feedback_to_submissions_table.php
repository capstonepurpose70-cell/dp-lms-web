<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separate column for the teacher's feedback.
 *
 * remarks = the student's answer, feedback = the teacher's comment, so
 * grading never overwrites what the student submitted.
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
