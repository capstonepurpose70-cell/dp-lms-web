<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->role,   fn($q) => $q->byRole($request->role))
            ->when($request->module, fn($q) => $q->byModule($request->module))
            ->when($request->date,   fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(20);

        return view('admin.audit-logs.index', compact('logs'));
    }
}