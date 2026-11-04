<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** When and why a teacher left the school (retired, resigned, transferred, other). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty_records', function (Blueprint $table) {
            $table->timestamp('left_at')->nullable()->after('claimed_by');
            $table->string('left_reason', 20)->nullable()->after('left_at');
        });
    }

    public function down(): void
    {
        Schema::table('faculty_records', fn (Blueprint $table) => $table->dropColumn(['left_at', 'left_reason']));
    }
};
