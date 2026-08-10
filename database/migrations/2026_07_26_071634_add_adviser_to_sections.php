<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Ang adviser (teacher) na hawak ng section na ito.
            // Sila ang mag-rereview ng enrollment ng students sa section nila.
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