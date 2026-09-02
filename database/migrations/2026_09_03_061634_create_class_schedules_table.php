<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class schedule — Lunes hanggang Biyernes.
 *
 * Nakakabit sa SECTION, hindi sa bawat estudyante. Iisa ang schedule
 * ng buong section, kaya awtomatikong nakikita ito ng bawat estudyante
 * sa pamamagitan ng section niya. Hindi na kailangang gumawa ulit
 * kapag may bagong na-enroll.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();

            // Pwedeng wala pang guro sa umpisa — itatakda mamaya.
            $table->foreignId('teacher_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            // 1 = Lunes ... 5 = Biyernes
            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time');
            $table->time('end_time');

            $table->string('room', 60)->nullable();
            $table->string('school_year', 20);

            $table->timestamps();

            // Para mabilis ang paghahanap ng banggaan (overlap check)
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