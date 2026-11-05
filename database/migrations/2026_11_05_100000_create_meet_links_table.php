<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One Google Meet link for each subject of a section, per school year.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meet_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('school_year');
            $table->string('url');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['section_id', 'subject_id', 'school_year'], 'meet_links_class_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meet_links');
    }
};
