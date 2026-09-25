<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pinapayagan nang walang email ang user.
 *
 * Para ito sa mga magulang na walang (o hindi gumagamit ng) email —
 * mobile number o LRN ng anak ang gagamitin nila. Nananatili ang UNIQUE
 * index: sa MySQL at SQLite, puwedeng maraming NULL sa unique column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Babagsak ito kung may user nang walang email — sinadya, para hindi
        // tahimik na mabura o mapalitan ang datos. Ayusin muna ang mga row.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
