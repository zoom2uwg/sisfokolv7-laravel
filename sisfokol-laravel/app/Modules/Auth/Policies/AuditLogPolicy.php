<?php

namespace App\Modules\Auth\Policies;

use App\Models\User;
use App\Modules\Auth\Models\AuditLog;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit.view');
    }

    public function view(User $user, AuditLog $log): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->can('audit.view') && $user->tenant_id === $log->tenant_id;
    }
}
