<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student who hearted
            $table->timestamps();
            $table->unique(['learning_material_id', 'user_id']); // one heart per student
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_likes');
    }
};