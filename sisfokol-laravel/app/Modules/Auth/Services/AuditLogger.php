<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\AuditLog;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(private TenantContext $tenant) {}

    public function log(
        string $event,
        ?\App\Models\User $user,
        array $newValues = [],
        ?Request $request = null,
        array $oldValues = [],
        ?string $modelType = null,
        ?int $modelId = null,
    ): void {
        $tenantId = $user?->tenant_id ?? ($this->tenant->isInitialized() ? $this->tenant->id : null);

        AuditLog::create([
            'tenant_id'    => $tenantId,
            'user_id'      => $user?->id,
            'event'        => $event,
            'model_type'   => $modelType ?? ($newValues['model_type'] ?? null),
            'model_id'     => $modelId ?? ($newValues['model_id'] ?? null),
            'old_values'   => empty($oldValues) ? null : $oldValues,
            'new_values'   => empty($newValues) ? null : $newValues,
            'ip_address'   => $request?->ip(),
            'user_agent'   => $request?->userAgent(),
        ]);
    }
}
