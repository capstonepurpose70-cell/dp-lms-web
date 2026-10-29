<?php

namespace App\Services;

use App\Models\OfficialLrn;
use App\Support\NameMatcher;
use App\Support\UnreadableSpreadsheet;
use Illuminate\Support\Facades\DB;

/**
 * LRN Master List import from a spreadsheet: the school's own list, the template, or DepEd SF1 as exported
 * from LIS — no reformatting needed.
 *
 * analyze() finds the header row (a cell named "LRN" plus the student-name column, or separate last / first /
 * middle name columns), checks every row and labels it:
 *   ready  — will be added
 *   exists — the LRN is already on the list; it is reported and never changed
 *   fix    — something is wrong with the row (the note says what)
 * Rows with no LRN and no student name (totals, notes, blank lines) are left out and only counted.
 *
 * commit() adds the ready rows in one transaction: all of them or none.
 */
class LrnImportService
{
    public const MAX_ROWS = 5000;
    public const GRADES = ['7', '8', '9', '10', '11', '12'];

    private const HEADER_SCAN_ROWS = 30;
    private const NAME_PATTERN = "/^[\pL\pM\s.,'\-]+$/u";
    // Name columns in SF1 that are not the learner's own name
    private const NOT_LEARNER = ['FATHER', 'MOTHER', 'GUARDIAN', 'PARENT', 'TEACHER', 'ADVISER', 'SCHOOL', 'PRINCIPAL', 'HEAD'];

    /**
     * @param list<array{name: string, rows: array<int, array<int, string>>}> $sheets from SpreadsheetReader
     * @param string|null $gradeForAll grade for rows without a grade of their own
     */
    public function analyze(array $sheets, ?string $gradeForAll = null): array
    {
        $found = null;
        foreach ($sheets as $sheet) {
            if ($layout = $this->findColumns($sheet['rows'])) {
                $found = [$sheet, $layout];
                break;
            }
        }
        if (!$found) {
            throw new UnreadableSpreadsheet('No column named "LRN" was found. The sheet needs a header row with "LRN" and the student name — download the template to see the layout.');
        }
        [$sheet, $layout] = $found;
        if ($layout['name'] === null && ($layout['last'] === null || $layout['first'] === null)) {
            throw new UnreadableSpreadsheet('The "LRN" column was found, but not the student name. Add a "Name" column (or "Last name" and "First name" columns) next to it.');
        }

        $rows = [];
        $seen = [];
        $ignored = 0;

        foreach ($sheet['rows'] as $rowNo => $cells) {
            if ($rowNo <= $layout['dataFrom']) {
                continue;
            }
            $lrnRaw = $cells[$layout['lrn']] ?? '';
            $name = $this->name($cells, $layout);
            $gradeRaw = $layout['grade'] !== null ? ($cells[$layout['grade']] ?? '') : '';

            // Totals, notes and blank lines: no LRN and nothing that reads like a person's name
            if ($lrnRaw === '' && !$this->looksLikeName($name)) {
                $ignored++;
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                throw new UnreadableSpreadsheet('The file has more than ' . number_format(self::MAX_ROWS) . ' students. Split it into smaller files (for example one per grade).');
            }

            $row = ['row' => $rowNo, 'lrn' => $lrnRaw, 'name' => $name, 'grade' => null, 'status' => 'fix', 'note' => ''];
            [$lrn, $problem] = $this->lrn($lrnRaw);
            if ($problem === null) {
                $row['lrn'] = $lrn;
                $problem = $this->nameProblem($name);
            }
            if ($problem === null) {
                [$row['grade'], $problem] = $this->grade($gradeRaw, $gradeForAll);
            }
            if ($problem === null && isset($seen[$lrn])) {
                $problem = "Same LRN as row {$seen[$lrn]}.";
            }
            if ($problem === null) {
                $seen[$lrn] = $rowNo;
                $row['status'] = 'ready';
            } else {
                $row['note'] = $problem;
            }
            $rows[] = $row;
        }

        $this->markExisting($rows);

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
                // insertOrIgnore: an LRN someone added after the preview is skipped, not duplicated
                $added += OfficialLrn::insertOrIgnore(array_map(fn ($r) => [
                    'lrn'          => $r['lrn'],
                    'student_name' => $r['name'],
                    'grade_level'  => $r['grade'],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ], $chunk));
            }

            return $added;
        });
    }

    /* ───────────────────────────── Columns ───────────────────────────── */

    private function findColumns(array $rows): ?array
    {
        $scanned = 0;
        foreach ($rows as $rowNo => $cells) {
            if (++$scanned > self::HEADER_SCAN_ROWS) {
                break;
            }
            $lrnCol = null;
            foreach ($cells as $c => $text) {
                if (preg_match('/^(LRN|LEARNERREFERENCENUMBER)/', $this->key($text))) {
                    $lrnCol = $c;
                    break;
                }
            }
            if ($lrnCol === null) {
                continue;
            }

            // SF1 splits its header over two rows (merged cells); read the next row too unless it is data
            $header = $cells;
            $dataFrom = $rowNo;
            $next = $rows[$rowNo + 1] ?? null;
            if ($next !== null && !preg_grep('/\d{6,}/', $next)) {
                foreach ($next as $c => $text) {
                    $header[$c] = trim(($header[$c] ?? '') . ' ' . $text);
                }
                $dataFrom = $rowNo + 1;
            }

            $layout = ['lrn' => $lrnCol, 'name' => null, 'last' => null, 'first' => null, 'middle' => null, 'grade' => null];
            foreach ($header as $c => $text) {
                if ($c === $lrnCol) {
                    continue;
                }
                $k = $this->key($text);
                // "Name extension" (Jr., III) is not the name; NameMatcher ignores suffixes anyway
                if ($this->isOtherPerson($k) || str_contains($k, 'EXTENSION')) {
                    continue;
                }
                if ($layout['name'] === null && preg_match('/^(NAME|FULLNAME|COMPLETENAME)|(LEARNER|STUDENT|PUPIL)S?NAME/', $k)) {
                    $layout['name'] = $c;
                } elseif ($layout['last'] === null && preg_match('/^(LAST|SUR|FAMILY)NAME|^SURNAME/', $k)) {
                    $layout['last'] = $c;
                } elseif ($layout['first'] === null && preg_match('/^(FIRST|GIVEN)NAME/', $k)) {
                    $layout['first'] = $c;
                } elseif ($layout['middle'] === null && preg_match('/^MIDDLE(NAME|INITIAL)?$|^MI$/', $k)) {
                    $layout['middle'] = $c;
                } elseif ($layout['grade'] === null && preg_match('/^(GRADE|YEARLEVEL)/', $k)) {
                    $layout['grade'] = $c;
                }
            }

            $labels = ['LRN' => $this->label($header[$lrnCol], $lrnCol)];
            if ($layout['name'] !== null) {
                $labels['Name'] = $this->label($header[$layout['name']], $layout['name']);
            } else {
                foreach (['last' => 'Last name', 'first' => 'First name', 'middle' => 'Middle name'] as $part => $title) {
                    if ($layout[$part] !== null) {
                        $labels[$title] = $this->label($header[$layout[$part]], $layout[$part]);
                    }
                }
            }
            if ($layout['grade'] !== null) {
                $labels['Grade'] = $this->label($header[$layout['grade']], $layout['grade']);
            }

            return $layout + ['header' => $rowNo, 'dataFrom' => $dataFrom, 'labels' => $labels];
        }

        return null;
    }

    /** Header text reduced to capital letters: "Learner's Name" → "LEARNERSNAME" */
    private function key(string $text): string
    {
        return (string) preg_replace('/[^A-Z]/', '', mb_strtoupper($text));
    }

    private function isOtherPerson(string $key): bool
    {
        foreach (self::NOT_LEARNER as $word) {
            if (str_contains($key, $word)) {
                return true;
            }
        }

        return false;
    }

    /** "C — NAME (Last Name, First Name, Middle Name)" for the preview */
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

    /** @return array{0: string, 1: ?string} [12-digit LRN, problem] */
    private function lrn(string $raw): array
    {
        if ($raw === '') {
            return ['', 'No LRN.'];
        }
        if (preg_match('/^\d+(\.\d+)?E\+?\d+$/i', $raw)) {
            return ['', "Excel shortened this LRN to {$raw}. Upload the .xlsx file, or format the LRN column as Text."];
        }
        $digits = (string) preg_replace('/[\s\-]/', '', $raw);
        if (!preg_match('/^\d{12}$/', $digits)) {
            return ['', 'The LRN must be exactly 12 digits.'];
        }

        return [$digits, null];
    }

    private function nameProblem(string $name): ?string
    {
        if ($name === '') {
            return 'No student name. The name is checked when the student activates the account.';
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

    /** @return array{0: ?string, 1: ?string} [grade, problem] */
    private function grade(string $raw, ?string $gradeForAll): array
    {
        if ($raw === '') {
            return [$gradeForAll, null];
        }
        // "7", "7.0", "Grade 7", "G7", "Gr. 7"
        if (preg_match('/^(?:GRADE|GR\.?|G)?\s*(\d{1,2})(?:\.0+)?$/i', $raw, $m) && in_array($m[1], self::GRADES, true)) {
            return [$m[1], null];
        }

        return [null, "Grade \"{$raw}\" is not 7 to 12."];
    }

    /** Rows whose LRN is already on the list become "exists" (never changed). */
    private function markExisting(array &$rows): void
    {
        $ready = [];
        foreach ($rows as $i => $r) {
            if ($r['status'] === 'ready') {
                $ready[$r['lrn']] = $i;
            }
        }

        foreach (array_chunk(array_keys($ready), 1000) as $chunk) {
            $chunk = array_map('strval', $chunk);   // numeric-string keys come back as ints
            foreach (OfficialLrn::whereIn('lrn', $chunk)->get(['lrn', 'student_name', 'claimed_by']) as $existing) {
                $i = $ready[$existing->lrn];
                $same = $existing->student_name !== null
                    && NameMatcher::matches($rows[$i]['name'], $existing->student_name)
                    && NameMatcher::matches($existing->student_name, $rows[$i]['name']);
                $rows[$i]['status'] = 'exists';
                $rows[$i]['note'] = match (true) {
                    $existing->claimed_by !== null => 'Already on the list — the student has activated an account.',
                    $same                          => 'Already on the list.',
                    default                        => 'Already on the list as "' . ($existing->student_name ?? 'no name') . '" — not changed.',
                };
            }
        }
    }
}
