<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficialLrn;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

/**
 * Official LRN Master List.
 * Dito idinadagdag ng admin ang mga LRN ng estudyante (bulk o isa-isa —
 * hal. para sa BAGONG student/transferee) bago sila makapag-register.
 */
class LrnController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all'); // all | available | claimed

        $lrns = OfficialLrn::with('claimedBy:id,name,email')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('lrn', 'like', "%{$q}%")
                      ->orWhere('student_name', 'like', "%{$q}%");
                });
            })
            ->when($status === 'available', fn($query) => $query->whereNull('claimed_by'))
            ->when($status === 'claimed',   fn($query) => $query->whereNotNull('claimed_by'))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.lrns.index', [
            'lrns'      => $lrns,
            'q'         => $q,
            'status'    => $status,
            'total'     => OfficialLrn::count(),
            'claimed'   => OfficialLrn::whereNotNull('claimed_by')->count(),
            'available' => OfficialLrn::whereNull('claimed_by')->count(),
        ]);
    }

    /** Magdagdag ng ISANG LRN — para sa bagong student/transferee. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'lrn'          => 'required|digits:12|unique:official_lrns,lrn',
            'student_name' => 'nullable|string|max:255',
            'grade_level'  => 'nullable|string|max:20',
        ], [
            'lrn.digits' => 'Ang LRN ay dapat eksaktong 12 digits.',
            'lrn.unique' => 'Nasa listahan na ang LRN na ito.',
        ]);

        OfficialLrn::create($data);
        AuditLogService::log("Added LRN: {$data['lrn']}", 'LRN Master List');

        return back()->with('success', "LRN {$data['lrn']} added — pwede nang mag-register ang estudyante gamit ito.");
    }

    /**
     * Bulk import — i-paste ang listahan, isang LRN bawat linya.
     * Tinatanggap: "123456789012" o "123456789012,Dela Cruz Juan" o "...,Juan,11".
     */
    public function bulkImport(Request $request)
    {
        $request->validate(['bulk' => 'required|string']);

        $lines   = preg_split('/\r\n|\r|\n/', (string) $request->bulk);
        $added   = 0;
        $skipped = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = array_map('trim', explode(',', $line));
            $lrn   = preg_replace('/\D/', '', $parts[0] ?? '');
            $name  = $parts[1] ?? null;
            $grade = $parts[2] ?? null;

            if (strlen($lrn) !== 12) {
                $skipped[] = 'Line ' . ($i + 1) . ' — invalid LRN';
                continue;
            }
            if (OfficialLrn::where('lrn', $lrn)->exists()) {
                $skipped[] = $lrn . ' — nasa listahan na';
                continue;
            }

            OfficialLrn::create([
                'lrn'          => $lrn,
                'student_name' => $name ?: null,
                'grade_level'  => $grade ?: null,
            ]);
            $added++;
        }

        AuditLogService::log("Bulk imported {$added} LRN(s)", 'LRN Master List');

        $msg = "{$added} LRN(s) added.";
        if ($skipped) {
            $msg .= ' Skipped: ' . implode('; ', array_slice($skipped, 0, 8))
                  . (count($skipped) > 8 ? ' …at ' . (count($skipped) - 8) . ' pa' : '');
        }

        return back()->with('success', $msg);
    }

    /** Burahin — kung HINDI pa claimed (protektado ang may account na). */
    public function destroy(OfficialLrn $lrn)
    {
        if ($lrn->claimed_by !== null) {
            return back()->withErrors(['general' => 'Hindi mabubura — may naka-register nang account sa LRN na ito.']);
        }

        AuditLogService::log("Deleted LRN: {$lrn->lrn}", 'LRN Master List');
        $lrn->delete();

        return back()->with('success', 'LRN removed from the list.');
    }
}