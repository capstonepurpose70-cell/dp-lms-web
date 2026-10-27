<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Birthdate the student entered at ACTIVATION.
 *
 * The school records have no birthdate, so it cannot verify the activation.
 * It is used as a CROSS-CHECK: the adviser compares it with the birthdate on
 * the enrollment form. A mismatch shows a warning (possible impostor).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('grade_level');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('birthdate');
        });
    }
};
