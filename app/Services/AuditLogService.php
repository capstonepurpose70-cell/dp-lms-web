<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public static function log(
        string $action,
        string $module = 'General',
        string $description = null
    ): void {
        $user = auth()->user() ?? auth('admin')->user();

        AuditLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name ?? 'Guest',
            'role'        => $user ? ($user instanceof \App\Models\Admin ? 'admin' : $user->role) : 'unknown',
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'device'      => substr(request()->header('User-Agent') ?? '', 0, 255),
        ]);
    }
}