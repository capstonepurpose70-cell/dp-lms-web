<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * otp_codes.expires_at: timestamp → dateTime.
 *
 * Sa MariaDB na explicit_defaults_for_timestamp=OFF (XAMPP), naging
 * ON UPDATE CURRENT_TIMESTAMP ang column na ito — nare-reset ang expiry
 * tuwing may update sa row (hal. kapag minarkahang used). Tinatanggal
 * ito ng ->change() dahil buong column definition ang muling isinusulat.
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
