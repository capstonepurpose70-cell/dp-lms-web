<?php

namespace App\Services;

use App\Models\FacultyRecord;
use App\Models\Section;
use App\Models\User;
use App\Support\NameMatcher;
use App\Support\UnreadableSpreadsheet;
use Illuminate\Support\Facades\DB;

/**
 * Faculty Master List import from a spreadsheet (the school's own list or the template).
 *
 * analyze() finds the header row (an "Employee ID" column plus the name, or last / first / middle name columns;
 * an "Advisory" column is optional), checks every row and labels it:
 *   ready  — will be added (the note says when the advisory was left out)
 *   exists — the employee ID is already on the list or used by a teacher account; never changed
 *   fix    — something is wrong with the row (the note says what)
 * Rows with no employee ID and no name (totals, notes, blank lines) are left out and only counted.
 *
 * commit() adds the ready rows in one transaction: all of them or none.
 */
class FacultyImportService
{
    public const MAX_ROWS = 1000;
    public const ID_PATTERN = '/^[A-Z0-9\-]{1,50}$/';
    public const NAME_PATTERN = "/^[\pL\pM\s.,'\-]+$/u";

    private const HEADER_SCAN_ROWS = 30;

    public function __construct(private TeacherRosterService $roster) {}

    /** @param list<array{name: string, rows: array<int, array<int, string>>}> $sheets from SpreadsheetReader */
    public function analyze(array $sheets): array
    {
        $found = null;
        foreach ($sheets as $sheet) {
            if ($layout = $this->findColumns($sheet['rows'])) {
                $found = [$sheet, $layout];
                break;
            }
        }
        if (!$found) {
            throw new UnreadableSpreadsheet('No "Employee ID" column was found. The sheet needs a header row with "Employee ID" and the teacher name — download the template to see the layout.');
        }
        [$sheet, $layout] = $found;
        if ($layout['name'] === null && ($layout['last'] === null || $layout['first'] === null)) {
            throw new UnreadableSpreadsheet('The "Employee ID" column was found, but not the teacher name. Add a "Name" column (or "Last name" and "First name" columns) next to it.');
        }

        $rows = [];
        $seenIds = [];
        $seenSections = [];
        $sections = [];   // advisory text → Section|null, so each one is looked up once
        $ignored = 0;

        foreach ($sheet['rows'] as $rowNo => $cells) {
            if ($rowNo <= $layout['dataFrom']) {
                continue;
            }
            $idRaw = trim($cells[$layout['id']] ?? '');
            $name = $this->name($cells, $layout);
            $advisory = $layout['advisory'] !== null ? trim((string) preg_replace('/\s+/u', ' ', $cells[$layout['advisory']] ?? '')) : '';

            if ($idRaw === '' && !$this->looksLikeName($name)) {
                $ignored++;
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                throw new UnreadableSpreadsheet('The file has more than ' . number_format(self::MAX_ROWS) . ' teachers. Split it into smaller files.');
            }

            $row = ['row' => $rowNo, 'id' => $idRaw, 'name' => $name, 'advisory' => $advisory, 'section_id' => null, 'status' => 'fix', 'note' => ''];
            [$id, $problem] = $this->employeeId($idRaw);
            if ($problem === null) {
                $row['id'] = $id;
                $problem = $this->nameProblem($name);
            }
            if ($problem === null && isset($seenIds[$id])) {
                $problem = "Same employee ID as row {$seenIds[$id]}.";
            }
            if ($problem === null && $advisory !== '') {
                if (!array_key_exists($advisory, $sections)) {
                    $sections[$advisory] = $this->roster->findSection($advisory);
                }
                $section = $sections[$advisory];
                if (!$section) {
                    $problem = "No active section \"{$advisory}\". Write it as grade-section, for example 7-Gutierrez.";
                } elseif (isset($seenSections[$section->id])) {
                    $problem = "Same advisory as row {$seenSections[$section->id]}.";
                } else {
                    $seenSections[$section->id] = $rowNo;
                    $row['section_id'] = $section->id;
                }
            }
            if ($problem === null) {
                $seenIds[$id] = $rowNo;
                $row['status'] = 'ready';
            } else {
                $row['note'] = $problem;
            }
            $rows[] = $row;
        }

        $this->markExisting($rows);
        $this->dropTakenAdvisories($rows);

        $counts = ['ready' => 0, 'exists' => 0, 'fix' => 0];
        foreach ($rows as $r) {
            $counts[$r['status']]++;
        }

        return [
            'sheet'   => $sheet['name'],
            'header'  => $layout['header'],
            'columns' => $layout['labels'],
            'rows'    => $rows,
            'counts'  => $counts + ['ignored' => $ignored],
        ];
    }

    /** Adds the ready rows in one transaction. Returns how many were added. */
    public function commit(array $rows): int
    {
        $ready = array_values(array_filter($rows, fn ($r) => $r['status'] === 'ready'));
        $now = now();

        return DB::transaction(function () use ($ready, $now) {
            $added = 0;
            foreach (array_chunk($ready, 500) as $chunk) {
                // insertOrIgnore: an ID someone added after the preview is skipped, not duplicated
                $added += FacultyRecord::insertOrIgnore(array_map(fn ($r) => [
                    'employee_id' => $r['id'],
                    'name'        => $r['name'],
                    'section_id'  => $r['section_id'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ], $chunk));
            }

            return $added;
        });
    }

    /** Upper-cased employee ID without spaces; Excel's "12345.0" back to "12345". @return array{0: string, 1: ?string} */
    public function employeeId(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ['', 'No employee ID.'];
        }
        if (preg_match('/^\d+(\.\d+)?E\+?\d+$/i', $raw)) {
            return ['', "Excel shortened this ID to {$raw}. Upload the .xlsx file, or format the ID column as Text."];
        }
        $id = mb_strtoupper((string) preg_replace('/^(\d+)\.0+$/', '$1', str_replace(' ', '', $raw)));
        if (!preg_match(self::ID_PATTERN, $id)) {
            return ['', 'The employee ID may have letters, numbers and dashes only (up to 50).'];
        }

        return [$id, null];
    }

    public function nameProblem(string $name): ?string
    {
        if ($name === '') {
            return 'No name. The name is checked when the teacher activates the account.';
        }
        if (mb_strlen($name) > 255) {
            return 'The name is longer than 255 characters.';
        }
        if (!preg_match(self::NAME_PATTERN, $name)) {
            return 'The name has characters other than letters, spaces, commas, periods, apostrophes and hyphens.';
        }
        if (count(NameMatcher::tokens($name)) < 2) {
            return 'The name needs at least a first name and a last name.';
        }

        return null;
    }

    /* ───────────────────────────── Columns ───────────────────────────── */

    private function findColumns(array $rows): ?array
    {
        $scanned = 0;
        foreach ($rows as $rowNo => $cells) {
            if (++$scanned > self::HEADER_SCAN_ROWS) {
                break;
            }
            $idCol = null;
            foreach ($cells as $c => $text) {
                if (preg_match('/^(EMPLOYEE(ID|NO|NUMBER)|EMPID|EMPNO|IDNO|IDNUMBER)/', $this->key($text))) {
                    $idCol = $c;
                    break;
                }
            }
            if ($idCol === null) {
                continue;
            }

            $layout = ['id' => $idCol, 'name' => null, 'last' => null, 'first' => null, 'middle' => null, 'advisory' => null];
            foreach ($cells as $c => $text) {
                if ($c === $idCol) {
                    continue;
                }
                $k = $this->key($text);
                if (str_contains($k, 'EXTENSION')) {
                    continue;
                }
                if ($layout['name'] === null && preg_match('/^(NAME|FULLNAME|COMPLETENAME)|(TEACHER|FACULTY|EMPLOYEE)S?NAME/', $k)) {
                    $layout['name'] = $c;
                } elseif ($layout['last'] === null && preg_match('/^(LAST|SUR|FAMILY)NAME|^SURNAME/', $k)) {
                    $layout['last'] = $c;
                } elseif ($layout['first'] === null && preg_match('/^(FIRST|GIVEN)NAME/', $k)) {
                    $layout['first'] = $c;
                } elseif ($layout['middle'] === null && preg_match('/^MIDDLE(NAME|INITIAL)?$|^MI$/', $k)) {
                    $layout['middle'] = $c;
                } elseif ($layout['advisory'] === null && preg_match('/^ADVIS/', $k)) {
                    $layout['advisory'] = $c;
                }
            }

            $labels = ['Employee ID' => $this->label($cells[$idCol], $idCol)];
            if ($layout['name'] !== null) {
                $labels['Name'] = $this->label($cells[$layout['name']], $layout['name']);
            } else {
                foreach (['last' => 'Last name', 'first' => 'First name', 'middle' => 'Middle name'] as $part => $title) {
                    if ($layout[$part] !== null) {
                        $labels[$title] = $this->label($cells[$layout[$part]], $layout[$part]);
                    }
                }
            }
            if ($layout['advisory'] !== null) {
                $labels['Advisory'] = $this->label($cells[$layout['advisory']], $layout['advisory']);
            }

            return $layout + ['header' => $rowNo, 'dataFrom' => $rowNo, 'labels' => $labels];
        }

        return null;
    }

    /** Header text reduced to capital letters: "Employee No." → "EMPLOYEENO" */
    private function key(string $text): string
    {
        return (string) preg_replace('/[^A-Z]/', '', mb_strtoupper($text));
    }

    /** "column B (Employee No.)" for the preview */
    private function label(string $text, int $col): string
    {
        $letter = '';
        for ($i = $col + 1; $i > 0; $i = intdiv($i - 1, 26)) {
            $letter = chr(65 + ($i - 1) % 26) . $letter;
        }

        return 'column ' . $letter . ' (' . mb_strimwidth(preg_replace('/\s+/', ' ', $text), 0, 48, '…') . ')';
    }

    /* ───────────────────────────── Values ───────────────────────────── */

    private function name(array $cells, array $layout): string
    {
        if ($layout['name'] !== null) {
            $name = $cells[$layout['name']] ?? '';
        } else {
            $last = $cells[$layout['last']] ?? '';
            $given = trim(($cells[$layout['first']] ?? '') . ' ' . ($layout['middle'] !== null ? ($cells[$layout['middle']] ?? '') : ''));
            $name = $last !== '' && $given !== '' ? "{$last}, {$given}" : $last . $given;
        }

        return trim((string) preg_replace('/\s+/u', ' ', $name));
    }

    private function looksLikeName(string $name): bool
    {
        return $name !== '' && preg_match(self::NAME_PATTERN, $name) && count(NameMatcher::tokens($name)) >= 2;
    }

    /** Ready rows whose ID is on the list, or already used by a teacher account, become "exists". */
    private function markExisting(array &$rows): void
    {
        $ready = [];
        foreach ($rows as $i => $r) {
            if ($r['status'] === 'ready') {
                $ready[$r['id']] = $i;
            }
        }

        // strval: numeric-string keys come back as ints
        foreach (array_chunk(array_map('strval', array_keys($ready)), 1000) as $chunk) {
            foreach (FacultyRecord::whereIn('employee_id', $chunk)->get(['employee_id', 'name', 'claimed_by']) as $existing) {
                $i = $ready[$existing->employee_id];
                $rows[$i]['status'] = 'exists';
                $rows[$i]['note'] = match (true) {
                    $existing->claimed_by !== null                           => 'Already on the list — the teacher has activated an account.',
                    NameMatcher::matches($rows[$i]['name'], $existing->name) => 'Already on the list.',
                    default                                                  => "Already on the list as \"{$existing->name}\" — not changed.",
                };
            }
            foreach (User::whereIn('employee_id', $chunk)->get(['employee_id', 'name']) as $user) {
                $i = $ready[$user->employee_id] ?? null;
                if ($i !== null && $rows[$i]['status'] === 'ready') {
                    $rows[$i]['status'] = 'exists';
                    $rows[$i]['note'] = "A teacher account already uses this ID ({$user->name}).";
                }
            }
        }
    }

    /**
     * A section that already has an adviser, or is waiting for another listed teacher, keeps that teacher:
     * this one is still added, without the advisory.
     */
    private function dropTakenAdvisories(array &$rows): void
    {
        $ids = array_values(array_filter(array_column(array_filter($rows, fn ($r) => $r['status'] === 'ready'), 'section_id')));
        if (!$ids) {
            return;
        }
        $holder = self::advisoryHolders($ids);

        foreach ($rows as &$r) {
            if ($r['status'] === 'ready' && $r['section_id'] && isset($holder[$r['section_id']])) {
                $r['note'] = "{$r['advisory']} already has an adviser ({$holder[$r['section_id']]}). Added without the advisory.";
                $r['section_id'] = null;
            }
        }
        unset($r);
    }

    /**
     * Section id → who holds it: the adviser's name, or the listed teacher it is reserved for.
     * @param list<int> $sectionIds
     * @return array<int, string>
     */
    public static function advisoryHolders(array $sectionIds): array
    {
        $holder = FacultyRecord::whereIn('section_id', $sectionIds)->whereNull('claimed_by')
            ->pluck('name', 'section_id')->map(fn ($n) => "{$n}, not yet activated")->all();
        foreach (Section::whereIn('id', $sectionIds)->whereNotNull('adviser_id')->with('adviser:id,name')->get() as $s) {
            $holder[$s->id] = $s->adviser->name ?? 'another teacher';
        }

        return $holder;
    }
}
