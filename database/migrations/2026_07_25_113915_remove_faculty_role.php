<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kung may existing faculty accounts, gawing teacher muna (para hindi ma-orphan).
        // (Karaniwan ay wala, dahil bago pa lang ang system — pero safe ito.)
        DB::table('users')->where('role', 'faculty')->update(['role' => 'teacher']);

        // Tanggalin na ang 'faculty' sa role enum.
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student','teacher','parent') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student','teacher','parent','faculty') NOT NULL");
    }
};