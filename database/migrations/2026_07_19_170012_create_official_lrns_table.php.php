<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_lrns', function (Blueprint $table) {
            $table->id();
            // Ang opisyal na Learner Reference Number mula sa registrar (12 digits)
            $table->string('lrn', 12)->unique();
            // Optional: pre-encoded na pangalan/grade mula sa masterlist
            $table->string('student_name')->nullable();
            $table->string('grade_level')->nullable();
            // Sino ang naka-claim (naka-register na gamit ang LRN na 'to)
            $table->unsignedBigInteger('claimed_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_lrns');
    }
};