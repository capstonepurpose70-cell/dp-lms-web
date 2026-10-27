<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing faculty accounts become teachers first (so nothing is orphaned).
        // (Usually there are none since the system is new — but this is safe.)
        DB::table('users')->where('role', 'faculty')->update(['role' => 'teacher']);

        // Remove 'faculty' from the role enum.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student','teacher','parent') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student','teacher','parent','faculty') NOT NULL");
    }
};