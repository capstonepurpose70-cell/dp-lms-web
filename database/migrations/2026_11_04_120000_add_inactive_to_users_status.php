<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** "inactive": the person has left the school (a retired or resigned teacher). The account is kept for the records but cannot sign in. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'inactive'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('status', 'inactive')->update(['status' => 'rejected']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
