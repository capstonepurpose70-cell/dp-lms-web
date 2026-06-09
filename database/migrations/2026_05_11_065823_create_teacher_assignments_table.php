<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->cascadeOnDelete();

            // nullable — adviser-only rows have no subject
            $table->foreignId('subject_id')
                  ->nullable()
                  ->constrained('subjects')
                  ->nullOnDelete();

            // nullable — legacy/backfilled rows may not have a school year yet
            $table->foreignId('school_year_id')
                  ->nullable()
                  ->constrained('school_years')
                  ->nullOnDelete();

            // Mirrors teacher_subjects for compatibility during transition
            $table->string('grade_level')->nullable();
            $table->string('school_year_label')->nullable(); // e.g. "2025-2026"

            // Adviser flag — homeroom teacher per section
            $table->boolean('is_adviser')->default(false);

            // Soft status — deactivate without destroying history
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // Prevent exact duplicate assignment rows
            $table->unique(
                ['user_id', 'section_id', 'subject_id', 'school_year_id'],
                'unique_teacher_assignment'
            );

            $table->index(['section_id', 'school_year_id']);
            $table->index(['user_id', 'school_year_id']);
            $table->index('is_adviser');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};