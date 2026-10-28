<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every face the gate camera acted on: recognized students (including repeat scans the same day)
 * and faces it could not match. This is the testing log for recognition accuracy; no image is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_scans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('result', 12);                      // recognized | repeat | unknown
            $t->decimal('score', 5, 4)->nullable();        // similarity reported by the Pi, 0–1
            $t->string('device', 64)->nullable();
            $t->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $t->dateTime('scanned_at');                    // when the camera saw the face (queued scans arrive later)
            $t->timestamp('created_at')->useCurrent();

            $t->index('scanned_at');
            $t->index(['user_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_scans');
    }
};
