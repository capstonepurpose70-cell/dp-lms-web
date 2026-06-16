<?php
 
namespace App\Services;
 
use App\Models\AuditLog;
 
class AuditLogService
{
    public static function log(
        string $action,
        string $module = 'General',
        string $description = null,
        $user = null
    ): void {
        // Allow passing the user explicitly (e.g. during API login, before the
        // request is authenticated via the token guard).
        $user = $user ?? auth()->user() ?? auth('admin')->user();
 
        AuditLog::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name ?? 'Guest',
            'role'        => $user ? ($user instanceof \App\Models\Admin ? 'admin' : $user->role) : 'unknown',
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'device'      => self::deviceLabel(),
        ]);
    }
 
    /**
     * A readable device label. The mobile app sends an "X-Client" header
     * (e.g. "DP-LMS Mobile (Android)"); browsers don't, so we fall back to the
     * User-Agent. This is what makes phone activity clearly detectable.
     */
    protected static function deviceLabel(): string
    {
        $client = request()->header('X-Client');
        if (!empty($client)) {
            return substr($client, 0, 255);
        }
        return substr(request()->header('User-Agent') ?? 'Unknown', 0, 255);
    }
}