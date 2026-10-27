<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $role   = (string) $request->role;
        $module = (string) $request->module;
        // Only a valid Y-m-d reaches whereDate()
        $date   = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->date) ? (string) $request->date : '';

        $filtered = fn () => AuditLog::query()
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when($role === 'unknown', fn ($q) => $q->where(fn ($w) => $w->whereNull('role')->orWhere('role', 'unknown')))
            ->when($role !== '' && $role !== 'unknown', fn ($q) => $q->byRole($role))
            ->when($module !== '', fn ($q) => $q->byModule($module))
            ->when($date !== '', fn ($q) => $q->whereDate('created_at', $date));

        $logs = $filtered()->latest()->paginate(20)->withQueryString();

        // Counts the whole filtered set, not just the current page.
        $stats = [
            'total'      => $logs->total(),
            'auth'       => $filtered()->where('module', 'Auth')->count(),
            // Security events: "Failed (admin) login", "Login blocked …", "OTP … failed", "Activation LOCKED …"
            'failed'     => $filtered()->where(fn ($w) => $w->where('action', 'like', '%fail%')
                                                         ->orWhere('action', 'like', '%blocked%')
                                                         ->orWhere('action', 'like', '%locked%'))->count(),
            'enrollment' => $filtered()->where('module', 'like', 'Enrollment%')->count(),
            'users'      => $filtered()->whereNotNull('user_name')->distinct()->count('user_name'),
        ];

        // Modules that actually appear in the logs
        $modules = AuditLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');

        return view('admin.audit-logs.index', compact('logs', 'stats', 'modules', 'search', 'role', 'module', 'date'));
    }
}
