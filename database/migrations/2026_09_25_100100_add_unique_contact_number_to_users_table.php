<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unique index on users.contact_number — so the mobile number can be used
 * as a login identifier (User::resolveByIdentifier).
 *
 * 1. Old numbers are rewritten in one format (09XXXXXXXXX).
 *    Numbers that cannot be normalized are left as they are — never deleted.
 * 2. If duplicates remain, the migration STOPS and lists the numbers.
 *    Nothing is deleted automatically; a person decides.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('contact_number', '')->update(['contact_number' => null]);

        DB::table('users')
            ->whereNotNull('contact_number')
            ->select('id', 'contact_number')
            ->orderBy('id')
            ->each(function ($row) {
                $normalized = static::normalize($row->contact_number);

                if ($normalized !== null && $normalized !== $row->contact_number) {
                    DB::table('users')->where('id', $row->id)
                        ->update(['contact_number' => $normalized]);
                }
            });

        $duplicates = DB::table('users')
            ->whereNotNull('contact_number')
            ->select('contact_number', DB::raw('COUNT(*) as total'))
            ->groupBy('contact_number')
            ->having('total', '>', 1)
            ->pluck('total', 'contact_number');

        if ($duplicates->isNotEmpty()) {
            $list = $duplicates->map(fn ($n, $num) => "{$num} (x{$n})")->implode(', ');

            throw new RuntimeException(
                "Hindi maidagdag ang unique index — may magkaparehong contact_number: {$list}. "
                . 'Ayusin o burahin muna ang mga duplicate na account, saka i-migrate ulit.'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['contact_number']);
        });
    }

    /** "+63 917-123-4567" / "639171234567" / "9171234567" → "09171234567"; null when not a PH mobile number. */
    private static function normalize(string $number): ?string
    {
        $digits = preg_replace('/\D/', '', $number);

        if (str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return preg_match('/^09\d{9}$/', $digits) ? $digits : null;
    }
};
