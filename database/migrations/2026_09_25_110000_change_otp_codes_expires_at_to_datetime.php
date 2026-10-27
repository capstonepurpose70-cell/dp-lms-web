<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * otp_codes.expires_at: timestamp → dateTime.
 *
 * On MariaDB with explicit_defaults_for_timestamp=OFF (XAMPP) this column
 * became ON UPDATE CURRENT_TIMESTAMP — the expiry reset whenever the row was
 * updated (e.g. when marked used). ->change() rewrites the whole column
 * definition, which drops that.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dateTime('expires_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->timestamp('expires_at')->change();
        });
    }
};
