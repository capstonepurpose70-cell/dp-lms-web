<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use App\Support\NameMatcher;
use Illuminate\Database\Seeder;

/**
 * Advisory sections + advisers ng Sto. Domingo NHS
 * (galing sa class schedule ng paaralan, SY 2025-2026 2nd sem).
 *
 * Patakbuhin nang mano-mano (hindi kasama sa DatabaseSeeder):
 *   php artisan db:seed --class=AdvisorySectionSeeder
 *
 * Ginagawa:
 *  1. Nire-RENAME ang SHS placeholder (ABM-A / HUMSS-A) sa tunay na pangalan —
 *     rename, hindi delete, para hindi maputol ang nakakabit na records.
 *  2. Ginagawa ang mga advisory section na wala pa.
 *  3. Ikinakabit ang adviser KUNG may approved teacher account na tugma ang
 *     pangalan at WALA pang adviser ang section. Hindi gumagawa ng teacher
 *     account — kailangan ng tunay na email (invite + temporary password),
 *     kaya sa Admin > User Management > Create Teacher iyon ginagawa.
 *
 * Ligtas patakbuhin nang paulit-ulit: walang binubura, walang dine-deactivate,
 * at hindi pinapalitan ang adviser na naitakda na.
 */
class AdvisorySectionSeeder extends Seeder
{
    /** [grade, section name, adviser full name] */
    private const ADVISORY = [
        // Junior High
        ['7',  'GUTIERREZ', 'Jonalyn L. Gutierrez'],
        ['7',  'AQUINO',    'Marie Jerela Kristina F. Aquino'],
        ['7',  'CALIMLIM',  'Merchiel B. Calimlim'],
        ['8',  'TUVERA',    'Arvin Dave D. Tuvera'],
        ['8',  'RAMOS',     'Daisy S. Ramos'],
        ['9',  'GANTE',     'Angelica C. Gante'],
        ['9',  'LAMPIOS',   'Cherry Ann Z. Lampios'],
        ['9',  'SARIBAY',   'Noime D. Saribay'],
        ['10', 'FABRO',     'Jessie L. Fabro'],
        ['10', 'NICOLAS',   'Reymond N. Nicolas'],

        // Senior High
        ['11', 'SMAW WORKER',    'Ricardo C. Sison, Jr.'],
        ['11', 'ABM INNOVATOR',  'Maria Rose A. Secretario'],
        ['11', 'ALS',            'Abegail T. Gabriel'],
        ['12', 'FBS EXPERT',     'Precious Joy R. Torrado'],
        ['12', 'ABM SPECIALIST', 'Roumella S. Sampilo'],
        ['12', 'HUMSS EXPLORER', 'Dexter C. Balubin'],
        ['12', 'ALS',            'Renald Christian C. Apiado'],
        ['12', 'SMAW CHAMPION',  'Benjamin L. Hidalgo Jr.'],
    ];

    /** Placeholder (galing SectionSeeder) => tunay na pangalan. [grade, old, new] */
    private const RENAMES = [
        ['11', 'ABM-A',   'ABM INNOVATOR'],
        ['12', 'ABM-A',   'ABM SPECIALIST'],
        ['12', 'HUMSS-A', 'HUMSS EXPLORER'],
    ];

    public function run(): void
    {
        $schoolYear = SchoolYear::current()?->label ?? '2026-2027';

        // ── 1. Rename placeholders ──────────────────────────────────────
        foreach (self::RENAMES as [$grade, $old, $new]) {
            $placeholder = Section::where('grade_level', $grade)->where('name', $old)->first();
            $targetTaken = Section::where('grade_level', $grade)->where('name', $new)->exists();

            if ($placeholder && !$targetTaken) {
                $placeholder->update(['name' => $new]);
                $this->say("Renamed: Grade {$grade} {$old} -> {$new}");
            }
        }

        // ── 2. Create missing sections, 3. link advisers ────────────────
        $teachers = User::where('role', 'teacher')
            ->where('status', 'approved')
            ->get(['id', 'name']);

        $missingAccounts = [];

        foreach (self::ADVISORY as [$grade, $name, $adviserName]) {
            $section = Section::firstOrCreate(
                ['grade_level' => $grade, 'name' => $name],
                ['school_year' => $schoolYear, 'is_active' => true]
            );

            if ($section->wasRecentlyCreated) {
                $this->say("Created: Grade {$grade} {$name}");
            }

            if ($section->adviser_id) {
                continue; // huwag palitan ang naitakda na ng admin
            }

            $teacher = $this->findTeacher($teachers, $adviserName);

            if ($teacher) {
                $section->update(['adviser_id' => $teacher->id]);
                $this->say("Adviser set: Grade {$grade} {$name} -> {$teacher->name}");
            } else {
                $missingAccounts[] = "{$adviserName} (Grade {$grade} {$name})";
            }
        }

        if ($missingAccounts) {
            $this->say('');
            $this->say('No approved teacher account found for (create them in User Management, then run this seeder again):');
            foreach ($missingAccounts as $line) {
                $this->say("  - {$line}");
            }
        }

        $this->say('');
        $this->say('Note: placeholder sections without a real adviser (e.g. STEM-A) were left untouched.');
        $this->say('Uncheck "Active" in Admin > Sections & Advisers if they are not used.');
    }

    /**
     * Isang teacher lang na tugma ang apelyido AT hindi bababa sa 2 token
     * (NameMatcher). Kapag 2+ ang tugma, walang ikinakabit — iwas maling adviser.
     */
    private function findTeacher($teachers, string $adviserName): ?User
    {
        $tokens  = NameMatcher::tokens($adviserName);
        $surname = end($tokens) ?: null;

        $hits = $teachers->filter(function ($t) use ($adviserName, $surname) {
            return $surname
                && in_array($surname, NameMatcher::tokens($t->name), true)
                && NameMatcher::matches($adviserName, $t->name);
        });

        return $hits->count() === 1 ? $hits->first() : null;
    }

    private function say(string $line): void
    {
        $this->command?->line($line);
    }
}
