<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficialLrn;
use App\Services\AuditLogService;
use App\Services\LrnImportService;
use App\Support\NameMatcher;
use App\Support\SpreadsheetReader;
use App\Support\UnreadableSpreadsheet;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Official LRN Master List.
 * Students can activate an account only when their LRN and name match an entry here. The admin imports the
 * list from a spreadsheet (upload → preview → confirm) or adds one LRN at a time for a transferee.
 */
class LrnController extends Controller
{
    private const PREVIEW_MINUTES = 30;

    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), ['available', 'claimed'], true) ? $request->query('status') : 'all';
        $grade  = in_array($request->query('grade'), LrnImportService::GRADES, true) ? $request->query('grade') : null;

        $lrns = OfficialLrn::with('claimedBy:id,name,email')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('lrn', 'like', "%{$q}%")
                      ->orWhere('student_name', 'like', "%{$q}%");
                });
            })
            ->when($status === 'available', fn ($query) => $query->whereNull('claimed_by'))
            ->when($status === 'claimed', fn ($query) => $query->whereNotNull('claimed_by'))
            ->when($grade, fn ($query) => $query->where('grade_level', $grade))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $claimed = OfficialLrn::whereNotNull('claimed_by')->count();
        $total   = OfficialLrn::count();

        return view('admin.lrns.index', [
            'lrns'      => $lrns,
            'q'         => $q,
            'status'    => $status,
            'grade'     => $grade,
            'grades'    => LrnImportService::GRADES,
            'total'     => $total,
            'claimed'   => $claimed,
            'available' => $total - $claimed,
        ]);
    }

    /** Add ONE LRN — for a new student or a transferee. */
    public function store(Request $request)
    {
        $data = $request->validateWithBag('single', [
            'lrn'          => 'required|digits:12|unique:official_lrns,lrn',
            // Required: activation needs a name that matches the master list
            'student_name' => [
                'required', 'string', 'max:255', "regex:/^[\pL\pM\s.,'\-]+$/u",
                function ($attribute, $value, $fail) {
                    if (count(NameMatcher::tokens($value)) < 2) {
                        $fail('Enter at least a first name and a last name.');
                    }
                },
            ],
            'grade_level'  => ['nullable', Rule::in(LrnImportService::GRADES)],
        ], [
            'lrn.digits'            => 'The LRN must be exactly 12 digits.',
            'lrn.unique'            => 'This LRN is already on the list.',
            'student_name.required' => 'Enter the student name — it is checked when the student activates the account.',
            'student_name.regex'    => 'Use letters, spaces, commas, periods, apostrophes and hyphens only.',
        ]);

        $data['student_name'] = trim((string) preg_replace('/\s+/u', ' ', $data['student_name']));
        OfficialLrn::create($data);
        AuditLogService::log("Added LRN: {$data['lrn']}", 'LRN Master List');

        return back()->with('success', "LRN {$data['lrn']} added. The student can now activate the account.");
    }

    /* ───────────────────────── Import: upload → preview → confirm ───────────────────────── */

    public function import(Request $request, LrnImportService $importer)
    {
        $data = $request->validateWithBag('import', [
            'file'  => ['required', 'file', 'max:2048', 'extensions:xlsx,csv'],
            'grade' => ['nullable', Rule::in(LrnImportService::GRADES)],
        ], [
            'file.required'   => 'Choose the Excel or CSV file to upload.',
            'file.uploaded'   => 'The file could not be uploaded. Try again; if it is bigger than 2 MB, split it into smaller files.',
            'file.max'        => 'The file is bigger than 2 MB. Split it into smaller files (for example one per grade).',
            'file.extensions' => 'Upload an .xlsx or .csv file. For an old .xls file, open it in Excel and save it as .xlsx.',
        ]);

        $file = $data['file'];
        try {
            $sheets = SpreadsheetReader::read($file->getRealPath(), $file->getClientOriginalExtension(), LrnImportService::MAX_ROWS + 50);
            $result = $importer->analyze($sheets, $data['grade'] ?? null);
        } catch (UnreadableSpreadsheet $e) {
            return back()->withErrors(['file' => $e->getMessage()], 'import');
        }

        // Only the checked rows are kept, for 30 minutes and for this admin only; the upload itself is not stored
        $token = Str::random(32);
        $this->savePreview($token, $result + [
            'file'  => Str::limit(basename($file->getClientOriginalName()), 120),
            'grade' => $data['grade'] ?? null,
        ]);

        return redirect()->route('admin.lrns.import.show', $token);
    }

    public function preview(string $token)
    {
        $import = $this->loadPreview($token);
        if (!$import) {
            return $this->expired();
        }

        return view('admin.lrns.preview', ['import' => $import, 'token' => $token]);
    }

    public function confirm(string $token, LrnImportService $importer)
    {
        // pull(): a second click (or a refresh) finds nothing and cannot import twice
        $import = $this->loadPreview($token, pull: true);
        if (!$import) {
            return $this->expired();
        }

        $ready = $import['counts']['ready'];
        $added = $importer->commit($import['rows']);

        AuditLogService::log(
            "Imported {$added} LRN(s) from {$import['file']}",
            'LRN Master List',
            "Ready {$ready}, already on the list {$import['counts']['exists']}, needed fixing {$import['counts']['fix']}"
        );

        $msg = $added . ' ' . Str::plural('student', $added) . ' added to the master list.';
        if ($added < $ready) {
            $msg .= ' ' . ($ready - $added) . ' had just been added by someone else and were skipped.';
        }

        return redirect()->route('admin.lrns.index')->with('success', $msg);
    }

    public function cancel(string $token)
    {
        Cache::forget($this->previewKey($token));

        return redirect()->route('admin.lrns.index');
    }

    /** The rows that were not added, as .xlsx (LRNs stay whole), so they can be fixed and uploaded again. */
    public function problems(string $token)
    {
        $import = $this->loadPreview($token);
        if (!$import) {
            return $this->expired();
        }

        $rows = [['Row in your file', 'LRN', 'Student name', 'Grade', 'What to fix']];
        foreach ($import['rows'] as $r) {
            if ($r['status'] === 'fix') {
                $rows[] = [(string) $r['row'], $r['lrn'], $r['name'], (string) $r['grade'], $r['note']];
            }
        }

        $path = XlsxWriter::write('Rows to fix', $rows, [16, 16, 40, 8, 70], [1]);

        return response()->download($path, 'lrn-rows-to-fix.xlsx')->deleteFileAfterSend();
    }

    public function template()
    {
        $path = XlsxWriter::write('LRN list', [
            ['LRN', 'Student name (Last, First Middle)', 'Grade', '', 'One student per row. LRN: 12 digits. Grade: 7 to 12, or leave blank and pick the grade when you upload.'],
        ], [18, 40, 10, 4, 90], [0, 2]);

        return response()->download($path, 'lrn-import-template.xlsx')->deleteFileAfterSend();
    }

    /* ─────────────────────────────────────────────────────────────────────────── */

    /** Delete — only while NOT claimed (an LRN with an account is protected). */
    public function destroy(OfficialLrn $lrn)
    {
        if ($lrn->claimed_by !== null) {
            return back()->withErrors(['general' => 'This LRN cannot be removed — a student account is already using it.']);
        }

        AuditLogService::log("Deleted LRN: {$lrn->lrn}", 'LRN Master List');
        $lrn->delete();

        return back()->with('success', "LRN {$lrn->lrn} removed from the list.");
    }

    private function previewKey(string $token): string
    {
        return 'lrn-import:' . auth('admin')->id() . ':' . $token;
    }

    // Compressed: 5,000 rows are about 1.5 MB as text, more than MySQL's default 1 MB packet (XAMPP)
    private function savePreview(string $token, array $import): void
    {
        $packed = base64_encode(gzcompress(json_encode($import, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 6));
        Cache::put($this->previewKey($token), $packed, now()->addMinutes(self::PREVIEW_MINUTES));
    }

    private function loadPreview(string $token, bool $pull = false): ?array
    {
        $packed = $pull ? Cache::pull($this->previewKey($token)) : Cache::get($this->previewKey($token));
        $json = is_string($packed) ? @gzuncompress((string) base64_decode($packed, true)) : false;
        $import = $json === false ? null : json_decode($json, true);

        return is_array($import) ? $import : null;
    }

    private function expired()
    {
        return redirect()->route('admin.lrns.index')
            ->withErrors(['general' => 'That import preview has expired or was already used. Upload the file again.']);
    }
}
