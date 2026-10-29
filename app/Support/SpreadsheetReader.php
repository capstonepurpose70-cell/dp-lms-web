<?php

namespace App\Support;

use XMLReader;
use ZipArchive;

/**
 * Reads cell values from an uploaded .xlsx or .csv file — text only: formulas are not run (the value Excel
 * saved is used), styles and macros are ignored.
 *
 * Uses PHP's zip and XMLReader, so no spreadsheet library is needed. Every part read from the workbook is
 * capped at MAX_PART_BYTES once unzipped (a small file cannot expand into gigabytes), external entities are
 * never loaded, and reading stops after $maxRows non-empty rows per sheet.
 */
final class SpreadsheetReader
{
    private const MAX_PART_BYTES = 40 * 1024 * 1024;
    private const MAX_SHEETS = 10;

    /**
     * @return list<array{name: string, rows: array<int, array<int, string>>}>
     *         Sheets in workbook order. Rows are keyed by their spreadsheet row number (1-based) and cells by
     *         column index (0 = A); empty rows and cells are left out.
     */
    public static function read(string $path, string $extension, int $maxRows): array
    {
        return match (strtolower($extension)) {
            'xlsx'  => self::xlsx($path, $maxRows),
            'csv'   => [['name' => 'CSV', 'rows' => self::csv($path, $maxRows)]],
            default => throw new UnreadableSpreadsheet('Upload an .xlsx or .csv file.'),
        };
    }

    /* ───────────────────────────── .xlsx ───────────────────────────── */

    private static function xlsx(string $path, int $maxRows): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new UnreadableSpreadsheet('This file is not a valid .xlsx workbook. Open it in Excel and save it again as .xlsx.');
        }

        try {
            $strings = self::sharedStrings($zip);
            $sheets  = [];
            foreach (array_slice(self::sheetParts($zip), 0, self::MAX_SHEETS) as [$name, $part]) {
                $xml = self::part($zip, $part);
                if ($xml !== null) {
                    $sheets[] = ['name' => $name, 'rows' => self::sheetRows($xml, $strings, $maxRows)];
                }
            }
        } finally {
            $zip->close();
        }

        if (!$sheets) {
            throw new UnreadableSpreadsheet('This workbook has no worksheets to read.');
        }

        return $sheets;
    }

    /** One file inside the workbook, or null when it is missing. */
    private static function part(ZipArchive $zip, string $name): ?string
    {
        if ($zip->locateName($name) === false) {
            return null;
        }
        // Read one byte past the cap so an oversized part is detected without unpacking all of it
        $data = $zip->getFromName($name, self::MAX_PART_BYTES + 1);
        if ($data === false) {
            throw new UnreadableSpreadsheet('This workbook is damaged. Open it in Excel and save it again as .xlsx.');
        }
        if (strlen($data) > self::MAX_PART_BYTES) {
            throw new UnreadableSpreadsheet('This workbook is too large to read. Split it into smaller files (one per grade or section).');
        }

        return $data;
    }

    private static function reader(string $xml): XMLReader
    {
        $r = new XMLReader();
        if (!$r->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new UnreadableSpreadsheet('This workbook is damaged. Open it in Excel and save it again as .xlsx.');
        }

        return $r;
    }

    /** @return list<array{0: string, 1: string}> [sheet name, part path] in workbook order */
    private static function sheetParts(ZipArchive $zip): array
    {
        $targets = [];
        $rels = self::part($zip, 'xl/_rels/workbook.xml.rels');
        if ($rels !== null) {
            $r = self::reader($rels);
            while (@$r->read()) {
                if ($r->nodeType === XMLReader::ELEMENT && $r->localName === 'Relationship') {
                    $target = (string) $r->getAttribute('Target');
                    $targets[(string) $r->getAttribute('Id')] = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/' . $target;
                }
            }
        }

        $parts = [];
        $book = self::part($zip, 'xl/workbook.xml');
        if ($book !== null) {
            $r = self::reader($book);
            while (@$r->read()) {
                if ($r->nodeType !== XMLReader::ELEMENT || $r->localName !== 'sheet') {
                    continue;
                }
                $name = (string) $r->getAttribute('name');
                $id = null;
                // r:id — matched by local name so both the transitional and strict namespaces work
                while ($r->moveToNextAttribute()) {
                    if ($r->localName === 'id') {
                        $id = $r->value;
                    }
                }
                if ($id !== null && isset($targets[$id])) {
                    $parts[] = [$name, $targets[$id]];
                }
            }
        }

        return $parts ?: [['Sheet1', 'xl/worksheets/sheet1.xml']];
    }

    /** @return list<string> */
    private static function sharedStrings(ZipArchive $zip): array
    {
        $xml = self::part($zip, 'xl/sharedStrings.xml');
        if ($xml === null) {
            return [];
        }

        $r = self::reader($xml);
        $out = [];
        $current = null;
        $phonetic = false;
        while (@$r->read()) {
            if ($r->nodeType === XMLReader::ELEMENT) {
                if ($r->localName === 'si') {
                    if ($r->isEmptyElement) {
                        $out[] = '';
                    } else {
                        $current = '';
                    }
                } elseif ($r->localName === 'rPh') {
                    $phonetic = !$r->isEmptyElement;   // pronunciation guides are not part of the text
                } elseif ($r->localName === 't' && $current !== null && !$phonetic) {
                    $current .= $r->readString();
                }
            } elseif ($r->nodeType === XMLReader::END_ELEMENT) {
                if ($r->localName === 'si') {
                    $out[] = (string) $current;
                    $current = null;
                } elseif ($r->localName === 'rPh') {
                    $phonetic = false;
                }
            }
        }

        return $out;
    }

    private static function sheetRows(string $xml, array $strings, int $maxRows): array
    {
        $r = self::reader($xml);
        $rows = [];
        $rowNo = 0;
        $row = [];
        $col = -1;
        $type = '';
        $value = null;

        while (@$r->read()) {
            if ($r->nodeType === XMLReader::ELEMENT) {
                switch ($r->localName) {
                    case 'row':
                        $rowNo = (int) ($r->getAttribute('r') ?: $rowNo + 1);
                        $row = [];
                        $col = -1;
                        if ($r->isEmptyElement) {
                            $row = null;
                        }
                        break;
                    case 'c':
                        $ref = (string) $r->getAttribute('r');
                        $col = $ref !== '' ? self::columnIndex($ref) : $col + 1;
                        $type = (string) $r->getAttribute('t');
                        $value = null;
                        break;
                    case 'v':
                        $value = $r->readString();
                        break;
                    case 't':   // inline string: <c t="inlineStr"><is><t>…</t></is></c>
                        if ($type === 'inlineStr') {
                            $value = ($value ?? '') . $r->readString();
                        }
                        break;
                }
            } elseif ($r->nodeType === XMLReader::END_ELEMENT) {
                if ($r->localName === 'c' && $value !== null && is_array($row)) {
                    $text = trim(self::cellText($type, $value, $strings));
                    if ($text !== '') {
                        $row[$col] = $text;
                    }
                } elseif ($r->localName === 'row') {
                    if ($row) {
                        $rows[$rowNo] = $row;
                        if (count($rows) >= $maxRows) {
                            break;
                        }
                    }
                }
            }
        }

        return $rows;
    }

    private static function cellText(string $type, string $value, array $strings): string
    {
        return match ($type) {
            's'         => $strings[(int) $value] ?? '',
            'b'         => $value === '1' ? 'TRUE' : 'FALSE',
            'e'         => '',
            'str', 'inlineStr', 'd' => $value,
            default     => self::number($value),
        };
    }

    /** "1.23456789012E+11" → "123456789012": whole numbers come back without exponent or ".0" */
    private static function number(string $value): string
    {
        if (is_numeric($value) && preg_match('/[eE.]/', $value)) {
            $f = (float) $value;
            if (floor($f) === $f && abs($f) < 1e15) {
                return sprintf('%.0f', $f);
            }
        }

        return $value;
    }

    /** "B12" → 1 */
    private static function columnIndex(string $ref): int
    {
        $letters = strtoupper((string) preg_replace('/[^A-Za-z]/', '', $ref));
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return max(0, $n - 1);
    }

    /* ───────────────────────────── .csv ───────────────────────────── */

    private static function csv(string $path, int $maxRows): array
    {
        $raw = (string) file_get_contents($path);
        if (str_contains($raw, "\0")) {
            throw new UnreadableSpreadsheet('This is not a text CSV file. Upload the .xlsx file instead, or save it from Excel as "CSV UTF-8".');
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');   // Excel's default "CSV" on Windows
        }

        $first = strtok($raw, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($first, $d))->first();

        $h = fopen('php://temp', 'r+');
        fwrite($h, $raw);
        rewind($h);

        $rows = [];
        $rowNo = 0;
        while (($cells = fgetcsv($h, null, $delimiter, '"', '')) !== false) {
            $rowNo++;
            $row = [];
            foreach ($cells as $i => $cell) {
                $cell = trim((string) $cell);
                if ($cell !== '') {
                    $row[$i] = $cell;
                }
            }
            if ($row) {
                $rows[$rowNo] = $row;
                if (count($rows) >= $maxRows) {
                    break;
                }
            }
        }
        fclose($h);

        return $rows;
    }
}
