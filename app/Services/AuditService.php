<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        Model $subject,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        $user = Request::user();

        return AuditLog::create([
            'tenant_id'   => $user?->tenant_id,
            'user_id'      => $user?->id,
            'user_name'    => $user?->name ?? 'guest',
            'action'       => $action,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->id,
            'old_values'   => $oldValues,
            'new_values'   => $newValues,
            'ip_address'   => Request::ip(),
            'user_agent'   => substr(Request::userAgent() ?? '', 0, 500),
        ]);
    }

    public static function logWorkspaceAction(string $action, Model $subject, ?Model $workspace = null): AuditLog
    {
        $old = $subject->wasRecentlyCreated ? null : $subject->getOriginal();
        $new = $subject->wasRecentlyCreated ? $subject->toArray() : $subject->getChanges();

        if ($old === null && $new === null) {
            $new = $subject->toArray();
        }

        return static::log(
            action: $action,
            subject: $workspace ?? $subject,
            oldValues: $old ? self::filterAuditValues($old) : null,
            newValues: $new ? self::filterAuditValues($new) : null,
        );
    }

    private static function filterAuditValues(array $values): array
    {
        $exclude = ['password', 'remember_token', 'secret', 'api_token', 'file_path'];
        return array_diff_key($values, array_flip($exclude));
    }
}
