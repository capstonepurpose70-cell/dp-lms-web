<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Remember this device" — skips the OTP at login when a valid
 * trusted-device cookie is present. The cookie holds a random token; only
 * its SHA-256 is stored here (indexed lookup).
 *
 * For every role, but it matters most for parents without an email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->char('token_hash', 64)->unique();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('last_used_at')->nullable();
            // dateTime, not timestamp — see the note in the parent_link_codes migration.
            $table->dateTime('expires_at');

            $table->timestamps();

            // For pruning expired rows (model:prune)
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
