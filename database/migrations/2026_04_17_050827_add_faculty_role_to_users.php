<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Faculty is just a user with role='faculty'
        // No migration needed if your role column is a string
        // Just make sure role column allows 'faculty'
    }

    public function down(): void {}
};