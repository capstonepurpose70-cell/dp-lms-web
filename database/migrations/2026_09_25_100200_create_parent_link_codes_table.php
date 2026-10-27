<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time code / QR the STUDENT creates to link their parent.
 *
 * - purpose 'link'    → creates the parent account
 * - purpose 'recover' → password reset / new device for an already linked parent
 *
 * One active code per student (unique student_id): the old one is deleted
 * whenever a new one is made. Stored as SHA-256, not bcrypt, so verifying is
 * a single indexed lookup instead of a scan of the whole table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_link_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->unique()
                  ->constrained('users')->cascadeOnDelete();

            $table->char('code_hash', 64)->unique();
            $table->string('purpose', 10)->default('link');

            // dateTime, not timestamp: on MariaDB with explicit_defaults_for_timestamp=OFF
            // (XAMPP) the first NOT NULL timestamp becomes ON UPDATE CURRENT_TIMESTAMP —
            // the expiry would reset on every update of the row.
            $table->dateTime('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_link_codes');
    }
};
