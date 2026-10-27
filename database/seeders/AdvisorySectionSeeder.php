<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use App\Support\NameMatcher;
use Illuminate\Database\Seeder;

/**
 * Advisory sections + advisers of Sto. Domingo NHS
 * (from the school's class schedule, SY 2025-2026 2nd sem).
 *
 * Run by hand (not part of DatabaseSeeder):
 *   php artisan db:seed --class=AdvisorySectionSeeder
 *
 * What it does:
 *  1. RENAMES the SHS placeholders (ABM-A / HUMSS-A) to their real names —
 *     rename, not delete, so attached records stay connected.
 *  2. Creates the advisory sections that do not exist yet.
 *  3. Attaches the adviser IF an approved teacher account matches the name
 *     and the section has NO adviser yet. It does not create teacher
 *     accounts — those need a real email, so they are made in
 *     Admin > User Management.
 *
 * Safe to run more than once: nothing is deleted or deactivated, and an
 * adviser that is already set is never replaced.
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

    /** Placeholder (from SectionSeeder) => real name. [grade, old, new] */
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
                continue; // never replace what the admin already set
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
     * Exactly one teacher whose surname matches AND at least 2 tokens match
     * (NameMatcher). With 2+ matches nothing is attached — no wrong adviser.
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
