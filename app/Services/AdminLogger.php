<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminLogger
{
    /**
     * Log an admin action.
     */
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $details = null): void
    {
        if (!Auth::check()) {
            return;
        }

        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }
}
