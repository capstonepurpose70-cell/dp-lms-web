<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Percentages of written works, performance tasks and quarterly assessment for one subject of a
 * section in one quarter. Each grade also keeps the percentages it was computed with, so its
 * breakdown stays right after a student moves to another section. Older grades keep NULL there
 * and read as the school default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('quarter', 2);
            $table->string('school_year');
            $table->unsignedTinyInteger('written_works');
            $table->unsignedTinyInteger('performance_tasks');
            $table->unsignedTinyInteger('quarterly_assessment');
            $table->timestamps();

            $table->unique(['section_id', 'subject_id', 'quarter', 'school_year'], 'grade_weights_class_unique');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedTinyInteger('weight_written_works')->nullable()->after('final_grade');
            $table->unsignedTinyInteger('weight_performance_tasks')->nullable()->after('weight_written_works');
            $table->unsignedTinyInteger('weight_quarterly_assessment')->nullable()->after('weight_performance_tasks');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['weight_written_works', 'weight_performance_tasks', 'weight_quarterly_assessment']);
        });

        Schema::dropIfExists('grade_weights');
    }
};
