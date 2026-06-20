<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 255);
            $table->string('platform', 20)->nullable(); // android / ios
            $table->timestamps();

            // One row per device token.
            $table->unique('token');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};