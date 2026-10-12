<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Birthdate na inilagay ng estudyante sa ACTIVATION.
 *
 * Walang birthdate sa records ng paaralan, kaya hindi ito pang-verify sa
 * activation. Ginagamit ito sa CROSS-CHECK: ikinukumpara ng adviser sa
 * birthdate sa enrollment form. Kapag magkaiba → babala (posibleng impostor).
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
