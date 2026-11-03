<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyRecord;
use App\Models\Section;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FacultyImportService;
use App\Support\NameMatcher;
use App\Support\SpreadsheetReader;
use App\Support\UnreadableSpreadsheet;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Faculty Master List.
 * Teachers activate their own account when their employee ID and name match an entry here; the advisory set
 * on the entry is assigned on activation if the section is still free. The admin imports the list from a
 * spreadsheet (upload → preview → confirm) or adds one teacher at a time.
 */
class FacultyController extends Controller
{
    private const PREVIEW_MINUTES = 30;

    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), ['available', 'claimed'], true) ? $request->query('status') : 'all';

        $records = FacultyRecord::with(['claimedBy:id,name,email', 'section:id,name,grade_level'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w->where('employee_id', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($status === 'available', fn ($query) => $query->whereNull('claimed_by'))
            ->when($status === 'claimed', fn ($query) => $query->whereNotNull('claimed_by'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $claimed = FacultyRecord::whereNotNull('claimed_by')->count();
        $total   = FacultyRecord::count();

        return view('admin.faculty.index', [
            'records'   => $records,
            'q'         => $q,
            'status'    => $status,
            'total'     => $total,
            'claimed'   => $claimed,
            'available' => $total - $claimed,
            'sections'  => $this->openSections(),
        ]);
    }

    /** Add ONE teacher — a new hire or a transferee. */
    public function store(Request $request, FacultyImportService $importer)
    {
        $data = $request->validateWithBag('single', [
            'employee_id' => 'required|string|max:60',
            'name'        => 'required|string|max:255',
            'section_id'  => 'nullable|integer',
        ], ['employee_id.required' => 'Enter the employee ID.', 'name.required' => 'Enter the teacher name — it is checked when the teacher activates the account.']);

        [$id, $problem] = $importer->employeeId($data['employee_id']);
        $name = trim((string) preg_replace('/\s+/u', ' ', $data['name']));
        $errors = [];
        if ($problem !== null) {
            $errors['employee_id'] = $problem;
        } elseif (FacultyRecord::where('employee_id', $id)->exists()) {
            $errors['employee_id'] = 'This employee ID is already on the list.';
        } elseif (User::where('employee_id', $id)->exists()) {
            $errors['employee_id'] = 'A teacher account already uses this employee ID.';
        }
        if ($nameProblem = $importer->nameProblem($name)) {
            $errors['name'] = $nameProblem;
        }
        $sectionId = $data['section_id'] ?? null;
        if ($sectionId !== null) {
            if (!Section::whereKey($sectionId)->where('is_active', true)->exists()) {
                $errors['section_id'] = 'Choose an active section.';
            } elseif ($holder = FacultyImportService::advisoryHolders([(int) $sectionId])[(int) $sectionId] ?? null) {
                $errors['section_id'] = "This section already has an adviser ({$holder}).";
            }
        }
        if ($errors) {
            return back()->withErrors($errors, 'single')->withInput();
        }

        FacultyRecord::create(['employee_id' => $id, 'name' => $name, 'section_id' => $sectionId]);
        AuditLogService::log("Added to the Faculty Master List: {$id}", 'Faculty Master List');

        return back()->with('success', "{$name} added. The teacher can now activate the account with employee ID {$id}.");
    }

    /* ───────────────────────── Import: upload → preview → confirm ───────────────────────── */

    public function import(Request $request, FacultyImportService $importer)
    {
        $data = $request->validateWithBag('import', [
            'file' => ['required', 'file', 'max:2048', 'extensions:xlsx,csv'],
        ], [
            'file.required'   => 'Choose the Excel or CSV file to upload.',
            'file.uploaded'   => 'The file could not be uploaded. Try again.',
            'file.max'        => 'The file is bigger than 2 MB.',
            'file.extensions' => 'Upload an .xlsx or .csv file. For an old .xls file, open it in Excel and save it as .xlsx.',
        ]);

        $file = $data['file'];
        try {
            $sheets = SpreadsheetReader::read($file->getRealPath(), $file->getClientOriginalExtension(), FacultyImportService::MAX_ROWS + 50);
            $result = $importer->analyze($sheets);
        } catch (UnreadableSpreadsheet $e) {
            return back()->withErrors(['file' => $e->getMessage()], 'import');
        }

        // Only the checked rows are kept, for 30 minutes and for this admin only; the upload itself is not stored
        $token = Str::random(32);
        $this->savePreview($token, $result + ['file' => Str::limit(basename($file->getClientOriginalName()), 120)]);

        return redirect()->route('admin.faculty.import.show', $token);
    }

    public function preview(string $token)
    {
        $import = $this->loadPreview($token);

        return $import ? view('admin.faculty.preview', ['import' => $import, 'token' => $token]) : $this->expired();
    }

    public function confirm(string $token, FacultyImportService $importer)
    {
        // pull(): a second click (or a refresh) finds nothing and cannot import twice
        $import = $this->loadPreview($token, pull: true);
        if (!$import) {
            return $this->expired();
        }

        $ready = $import['counts']['ready'];
        $added = $importer->commit($import['rows']);
        AuditLogService::log(
            "Imported {$added} teacher(s) from {$import['file']}",
            'Faculty Master List',
            "Ready {$ready}, already listed {$import['counts']['exists']}, needed fixing {$import['counts']['fix']}"
        );

        $msg = $added . ' ' . Str::plural('teacher', $added) . ' added to the faculty list.';
        if ($added < $ready) {
            $msg .= ' ' . ($ready - $added) . ' had just been added by someone else and were skipped.';
        }

        return redirect()->route('admin.faculty.index')->with('success', $msg);
    }

    public function cancel(string $token)
    {
        Cache::forget($this->previewKey($token));

        return redirect()->route('admin.faculty.index');
    }

    /** The rows that were not added, as .xlsx, so they can be fixed and uploaded again. */
    public function problems(string $token)
    {
        $import = $this->loadPreview($token);
        if (!$import) {
            return $this->expired();
        }

        $rows = [['Row in your file', 'Employee ID', 'Name', 'Advisory', 'What to fix']];
        foreach ($import['rows'] as $r) {
            if ($r['status'] === 'fix') {
                $rows[] = [(string) $r['row'], $r['id'], $r['name'], $r['advisory'], $r['note']];
            }
        }
        $path = XlsxWriter::write('Rows to fix', $rows, [16, 18, 40, 18, 70], [1]);

        return response()->download($path, 'faculty-rows-to-fix.xlsx')->deleteFileAfterSend();
    }

    public function template()
    {
        $path = XlsxWriter::write('Faculty list', [
            ['Employee ID', 'Name (Last, First Middle)', 'Advisory', '', 'One teacher per row. Advisory is optional: grade-section, for example 7-Gutierrez.'],
        ], [18, 40, 18, 4, 90], [0]);

        return response()->download($path, 'faculty-import-template.xlsx')->deleteFileAfterSend();
    }

    /* ─────────────────────────────────────────────────────────────────────────── */

    /** Delete — only while no account uses the entry (or that account has since been removed). */
    public function destroy(FacultyRecord $faculty)
    {
        if ($faculty->claimed_by !== null && User::whereKey($faculty->claimed_by)->exists()) {
            return back()->withErrors(['general' => 'This entry cannot be removed — a teacher account is using it.']);
        }

        AuditLogService::log("Removed from the Faculty Master List: {$faculty->employee_id}", 'Faculty Master List');
        $faculty->delete();

        return back()->with('success', "{$faculty->name} removed from the faculty list.");
    }

    /** Active sections for the "Advisory" choice, marking the ones already taken. */
    private function openSections()
    {
        $sections = Section::where('is_active', true)->orderByRaw('CAST(grade_level AS UNSIGNED)')->orderBy('name')->get(['id', 'name', 'grade_level']);
        $holders = $sections->isEmpty() ? [] : FacultyImportService::advisoryHolders($sections->pluck('id')->all());

        return $sections->map(fn ($s) => ['id' => $s->id, 'label' => "{$s->grade_level}-{$s->name}", 'taken' => isset($holders[$s->id])]);
    }

    private function previewKey(string $token): string
    {
        return 'faculty-import:' . auth('admin')->id() . ':' . $token;
    }

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
        return redirect()->route('admin.faculty.index')
            ->withErrors(['general' => 'That import preview has expired or was already used. Upload the file again.']);
    }
}
