<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // The adviser (teacher) of this section.
            // They review the enrollment requests of students in their section.
            $table->unsignedBigInteger('adviser_id')->nullable()->after('grade_level');
            $table->index('adviser_id');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('adviser_id');
        });
    }
};