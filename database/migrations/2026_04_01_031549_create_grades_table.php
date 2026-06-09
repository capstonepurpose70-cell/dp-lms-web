<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('quarter'); // Q1, Q2, Q3, Q4
            $table->string('school_year');
            $table->decimal('written_works', 5, 2)->nullable();
            $table->decimal('performance_tasks', 5, 2)->nullable();
            $table->decimal('quarterly_assessment', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->string('remarks')->nullable(); // Passed / Failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};