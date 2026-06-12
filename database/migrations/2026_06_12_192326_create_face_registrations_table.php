<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedTinyInteger('images_count')->default(0);
            $table->string('reject_reason')->nullable();
            $table->boolean('inappropriate')->default(false); // flagged by admin
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('face_warnings')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_registrations');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('face_warnings');
        });
    }
};