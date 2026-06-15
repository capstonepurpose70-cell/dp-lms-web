<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Question bank for the Strata Rush science game (per grade level)
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->string('grade_level', 4);            // '11' or '12'
            $table->string('topic')->nullable();          // Physics, Chemistry, Earth Science, Biology
            $table->string('difficulty', 10)->default('easy'); // easy | medium | hard
            $table->text('question');
            $table->json('options');                      // ["A","B","C","D"]
            $table->unsignedTinyInteger('correct_index'); // 0-based index of correct option
            $table->timestamps();

            $table->index(['grade_level', 'difficulty']);
        });

        // Saved sessions / leaderboard
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('grade_level', 4);
            $table->string('world')->nullable();          // Formula Clash / Field Researcher
            $table->unsignedInteger('score')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0); // 0-100
            $table->unsignedSmallInteger('correct')->default(0);
            $table->unsignedSmallInteger('incorrect')->default(0);
            $table->unsignedSmallInteger('max_combo')->default(0);
            $table->unsignedInteger('avg_response_ms')->default(0);
            $table->timestamps();

            $table->index(['grade_level', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_scores');
        Schema::dropIfExists('game_questions');
    }
};