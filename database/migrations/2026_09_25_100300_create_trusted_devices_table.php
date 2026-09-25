<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Tandaan ang device na ito" — nilalaktawan ang OTP sa login kapag may
 * valid na trusted-device cookie. Ang cookie ay may random token; SHA-256
 * lang nito ang naka-store dito (indexed lookup).
 *
 * Para sa lahat ng role, pero pinakamahalaga sa magulang na walang email.
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
            // dateTime, hindi timestamp — tingnan ang paliwanag sa parent_link_codes migration.
            $table->dateTime('expires_at');

            $table->timestamps();

            // Para sa pag-prune ng mga expired (model:prune)
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
