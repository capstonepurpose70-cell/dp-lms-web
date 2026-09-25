<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time code / QR na ginagawa ng ESTUDYANTE para ma-link ang magulang.
 *
 * - purpose 'link'    → paggawa ng parent account
 * - purpose 'recover' → reset ng password / bagong device ng naka-link nang magulang
 *
 * Isang aktibong code lang kada estudyante (unique student_id): binubura
 * ang luma tuwing gagawa ng bago. SHA-256 ang naka-store, hindi bcrypt,
 * kaya isang indexed lookup lang ang pag-verify — hindi scan ng buong table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_link_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->unique()
                  ->constrained('users')->cascadeOnDelete();

            $table->char('code_hash', 64)->unique();
            $table->string('purpose', 10)->default('link');

            // dateTime, hindi timestamp: sa MariaDB na explicit_defaults_for_timestamp=OFF
            // (XAMPP), ang unang NOT NULL timestamp ay nagiging ON UPDATE CURRENT_TIMESTAMP —
            // mare-reset ang expiry tuwing may update sa row.
            $table->dateTime('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_link_codes');
    }
};
