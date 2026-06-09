<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['student', 'teacher', 'parent'])->default('student');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('lrn')->nullable()->unique();
            $table->string('employee_id')->nullable()->unique();
            $table->string('grade_level')->nullable();
            $table->string('contact_number')->nullable();
            $table->boolean('otp_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};