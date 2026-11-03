<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faculty Master List: the school's official list of teachers. A teacher activates their own account
     * with the employee ID and name on this list (like students with the LRN list).
     */
    public function up(): void
    {
        Schema::create('faculty_records', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->unique();
            $table->string('name');
            // Advisory section read from the list; assigned when the teacher activates (if still free)
            $table->unsignedBigInteger('section_id')->nullable()->index();
            $table->unsignedBigInteger('claimed_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_records');
    }
};
