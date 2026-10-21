<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class attendance taken by the subject teacher (online, per class).
 *
 * The teacher opens a session for one section + subject with a time window:
 *   opened_at → on_time_until  = PRESENT (green)
 *   on_time_until → closes_at  = LATE (yellow)
 *   not marked at closes_at    = ABSENT
 * Students mark themselves by entering the 4-digit code shown in class.
 *
 * Separate from the `attendances` table (Raspberry Pi gate arrivals for parents).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_attendance_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('section_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->string('school_year')->nullable();
            $t->string('code', 6);
            $t->unsignedSmallInteger('on_time_minutes');
            $t->unsignedSmallInteger('late_minutes');
            $t->dateTime('opened_at');
            $t->dateTime('on_time_until');
            $t->dateTime('closes_at');
            $t->dateTime('closed_at')->nullable();   // set when finalized (absents written)
            $t->timestamps();

            $t->index(['section_id', 'subject_id', 'opened_at']);
            $t->index(['teacher_id', 'opened_at']);
        });

        Schema::create('class_attendance_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('class_attendance_sessions')->cascadeOnDelete();
            $t->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $t->string('status', 10);                // present | late | absent
            $t->dateTime('marked_at')->nullable();
            $t->string('marked_by', 10);             // student | teacher | system
            $t->timestamps();

            $t->unique(['session_id', 'student_id']);
            $t->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_attendance_records');
        Schema::dropIfExists('class_attendance_sessions');
    }
};
