<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time QR codes for registering a student's face at the Raspberry Pi kiosk.
 * Issued by the admin or the student's adviser; valid 10 minutes, single use.
 * Only the SHA-256 hash of the token is stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_kiosk_codes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->string('issued_by_role', 10);              // admin | teacher
            $t->unsignedBigInteger('issued_by_id')->nullable();
            $t->dateTime('expires_at');
            $t->dateTime('used_at')->nullable();
            $t->string('used_ip', 45)->nullable();
            $t->timestamps();

            $t->index(['student_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_kiosk_codes');
    }
};
