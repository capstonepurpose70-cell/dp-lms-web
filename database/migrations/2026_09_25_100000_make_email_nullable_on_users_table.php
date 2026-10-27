<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allows users without an email.
 *
 * For parents who have (or use) no email — they sign in with a mobile
 * number instead. The UNIQUE index stays: MySQL and SQLite allow many
 * NULLs in a unique column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // This fails if a user already has no email — on purpose, so data is never
        // silently deleted or replaced. Fix those rows first.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
