<?php

namespace App\Services;

use App\Models\Section;
use App\Models\User;
use App\Support\NameMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Bulk "Import Teachers" — isang paste para sa buong faculty (walang email na kailangan).
 *
 * Bawat linya:  Name  |  Name, Advisory  |  Name, Advisory, Employee ID
 *   Advisory: "7-GUTIERREZ", "Grade 11 - SMAW WORKER", o blangko / "-" kung wala.
 *
 * Bawat teacher ay ginagawang PENDING na account na walang email at walang alam na
 * password, may isang personal na activation link (14 araw). Sa link, ilalagay ng
 * teacher ang SARILI niyang email at password. Ang unang login ay magpapadala ng OTP
 * sa email na iyon — patunay na kanya ito. Ang adviser ay agad na nakakabit sa section
 * (sections.adviser_id) para pagka-activate ay makikita na niya ang enrollment.
 */
class TeacherRosterService
{
    public const LINK_DAYS = 14;

    /** @return array{created:int, results:array<int,array>} */
    public function import(string $bulk): array
    {
        $lines   = preg_split('/\r\n|\r|\n/', trim($bulk));
        $results = [];
        $created = 0;
        $seen    = [];

        // Existing teachers, for duplicate-name detection
        $existing = User::where('role', 'teacher')->get(['id', 'name']);

        foreach ($lines as $i => $raw) {
            $line = trim($raw);
            if ($line === '') {
                continue;
            }
            $no    = $i + 1;
            $parts = array_map('trim', str_getcsv($line));
            // "Ricardo C. Sison, Jr., 11-SMAW WORKER" — the suffix belongs to the name
            if (isset($parts[1]) && preg_match('/^(jr|sr|ii|iii|iv|v)\.?$/i', $parts[1])) {
                $parts[0] .= ' ' . $parts[1];
                array_splice($parts, 1, 1);
            }
            $name  = preg_replace('/\s+/', ' ', $parts[0] ?? '');
            $adv   = $parts[1] ?? '';
            $empId = $parts[2] ?? '';

            $row = ['line' => $no, 'name' => $name, 'ok' => false, 'link' => null, 'note' => ''];

            if ($name === '' || !preg_match('/^[\pL\s\.\'\-]+$/u', $name) || mb_strlen($name) > 255) {
                $results[] = array_merge($row, [
                    'name' => $name !== '' ? $name : "(line {$no})",
                    'note' => 'Invalid name — letters, spaces, periods, apostrophes and hyphens only.',
                ]);
                continue;
            }

            $key = implode(' ', NameMatcher::tokens($name));
            if (isset($seen[$key])) {
                $results[] = array_merge($row, ['note' => 'Duplicate of an earlier line — skipped.']);
                continue;
            }
            $seen[$key] = true;

            $dupe = $existing->first(fn ($t) => NameMatcher::matches($name, $t->name) && NameMatcher::matches($t->name, $name));
            if ($dupe) {
                $results[] = array_merge($row, ['note' => "Already has an account ({$dupe->name}) — skipped."]);
                continue;
            }

            if ($empId !== '' && (mb_strlen($empId) > 50 || User::where('employee_id', $empId)->exists())) {
                $results[] = array_merge($row, ['note' => "Employee ID {$empId} is invalid or already used — skipped."]);
                continue;
            }

            // Advisory section (optional)
            $section = null;
            $advNote = '';
            if ($adv !== '' && $adv !== '-') {
                $section = $this->findSection($adv);
                if (!$section) {
                    $advNote = "Advisory \"{$adv}\" not found — set it later in Sections & Advisers.";
                } elseif ($section->adviser_id) {
                    $advNote = "Grade {$section->grade_level} {$section->name} already has an adviser — not changed.";
                    $section = null;
                }
            }

            $token = Str::random(48);

            $teacher = DB::transaction(function () use ($name, $empId, $token, $section) {
                $t = User::create([
                    'name'                 => $name,
                    'email'                => null,
                    'password'             => Hash::make(Str::random(40)), // unusable until activation
                    'role'                 => 'teacher',
                    'status'               => 'pending',
                    'employee_id'          => $empId !== '' ? $empId : null,
                    'invite_token'         => hash('sha256', $token),
                    'invite_expires_at'    => now()->addDays(self::LINK_DAYS),
                    'must_change_password' => false,
                ]);

                $section?->update(['adviser_id' => $t->id]);

                return $t;
            });

            $created++;
            $results[] = array_merge($row, [
                'ok'       => true,
                'link'     => route('invite.show', $token),
                'advisory' => $section ? "Grade {$section->grade_level} – {$section->name}" : null,
                'note'     => $advNote,
            ]);
        }

        if ($created) {
            AuditLogService::log("Imported {$created} teacher account(s)", 'User Management');
        }

        return ['created' => $created, 'results' => $results];
    }

    /** New activation link for a teacher who has not activated yet. Returns the URL. */
    public function newLink(User $teacher): string
    {
        $token = Str::random(48);

        $teacher->update([
            'invite_token'      => hash('sha256', $token),
            'invite_expires_at' => now()->addDays(self::LINK_DAYS),
        ]);

        AuditLogService::log("New activation link: {$teacher->name}", 'User Management');

        return route('invite.show', $token);
    }

    /** "7-GUTIERREZ", "Grade 11 - SMAW WORKER", "12 ABM SPECIALIST" → active Section. */
    private function findSection(string $text): ?Section
    {
        if (!preg_match('/^\s*(?:grade\s*)?(7|8|9|10|11|12)\s*[-–:]?\s*(.+)$/iu', $text, $m)) {
            return null;
        }

        return Section::where('is_active', true)
            ->where('grade_level', $m[1])
            ->whereRaw('UPPER(name) = ?', [mb_strtoupper(trim($m[2]))])
            ->first();
    }
}
