<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inaayos ang "schema drift": mga migration na naka-markang "Ran" pero kulang
 * ang column sa database (na-edit ang migration file PAGKATAPOS itong tumakbo).
 *
 * Idempotent — kung kumpleto na ang schema (hal. sa bagong deploy), walang
 * ginagawa. Kaya ligtas patakbuhin kahit saan.
 *
 * Mga sintomas na inaayos nito:
 *  - fcm_tokens: walang user_id/token/platform → sira ang push notifications
 *  - messages.edited_at: wala → sira ang pag-edit ng chat message
 *  - users.role: may natirang 'faculty' sa enum at mga user na 'faculty' ang role
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── fcm_tokens ────────────────────────────────────────────────────────
        if (!Schema::hasColumn('fcm_tokens', 'token')) {
            // Walang silbi ang token row na walang user/token — ligtas burahin.
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

        // ── users.role: tanggalin ang 'faculty' (MySQL/MariaDB lang) ──────────
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
        // Pag-aayos lang ito ng drift — walang ibabalik.
    }
};
