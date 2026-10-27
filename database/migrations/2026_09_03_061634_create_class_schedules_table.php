<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class schedule — Monday to Friday.
 *
 * Attached to the SECTION, not to each student. The whole section shares one
 * schedule, so every student sees it through their section automatically —
 * nothing to create again when a new student enrolls.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            // May have no teacher at first — assigned later.
            $table->foreignId('teacher_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            // 1 = Lunes ... 5 = Biyernes
            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->string('room', 60)->nullable();
            $table->string('school_year', 20);

            $table->timestamps();

            // Speeds up the clash search (overlap check)
            $table->index(['section_id', 'day_of_week']);
            $table->index(['teacher_id', 'day_of_week']);
            $table->index(['room', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};