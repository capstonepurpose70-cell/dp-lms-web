<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs "schema drift": migrations marked "Ran" while the database is
 * missing columns (the migration file was edited AFTER it ran).
 *
 * Idempotent — when the schema is already complete (e.g. a fresh deploy) it
 * does nothing, so it is safe to run anywhere.
 *
 * Symptoms it fixes:
 *  - fcm_tokens: no user_id/token/platform → push notifications broken
 *  - messages.edited_at: missing → editing chat messages broken
 *  - users.role: 'faculty' left in the enum and users still on the 'faculty' role
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── fcm_tokens ────────────────────────────────────────────────────────
        if (!Schema::hasColumn('fcm_tokens', 'token')) {
            // A token row without a user/token is useless — safe to delete.
            DB::table('fcm_tokens')->delete();

            Schema::table('fcm_tokens', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
                $table->string('token', 255)->after('user_id');
                $table->string('platform', 20)->nullable()->after('token');
                $table->unique('token');
            });
        }

        // ── messages.edited_at ────────────────────────────────────────────────
        if (!Schema::hasColumn('messages', 'edited_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->timestamp('edited_at')->nullable()->after('reply_to_id');
            });
        }

        // ── users.role: drop 'faculty' (MySQL/MariaDB only) ──────────────────
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::table('users')->where('role', 'faculty')->update(['role' => 'teacher']);

            $type = DB::selectOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'"
            )?->t;

            if ($type && str_contains($type, 'faculty')) {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student','teacher','parent') NOT NULL");
            }
        }
    }

    public function down(): void
    {
        // This only repairs drift — nothing to roll back.
    }
};
