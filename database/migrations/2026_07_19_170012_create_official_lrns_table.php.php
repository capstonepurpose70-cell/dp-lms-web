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
            // The official Learner Reference Number from the registrar (12 digits)
            $table->string('lrn', 12)->unique();
            // Optional: name/grade pre-encoded from the masterlist
            $table->string('student_name')->nullable();
            $table->string('grade_level')->nullable();
            // Who claimed it (registered with this LRN)
            $table->unsignedBigInteger('claimed_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_lrns');
    }
};