<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unique index sa users.contact_number — para magamit ang mobile number
 * bilang login identifier (User::resolveByIdentifier).
 *
 * 1. Ginagawang iisang format (09XXXXXXXXX) ang mga lumang numero.
 *    Ang hindi ma-normalize ay iniiwan kung ano sila — hindi binubura.
 * 2. Kapag may duplicate pa rin, TITIGIL ang migration at ililista ang
 *    mga numero. Walang awtomatikong pagbura; tao ang magpapasya.
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

    /** "+63 917-123-4567" / "639171234567" / "9171234567" → "09171234567"; null kung hindi PH mobile. */
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
