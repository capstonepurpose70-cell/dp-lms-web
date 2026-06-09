<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('student_id')->nullable();
            $table->string('student_name')->nullable();
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('set null');
            $table->string('source')->default('iot');
            $table->timestamp('attended_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};