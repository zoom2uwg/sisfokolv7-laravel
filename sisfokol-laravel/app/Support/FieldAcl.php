<?php

namespace App\Support;

use App\Models\User;
use App\Modules\Auth\Models\Field;
use App\Modules\Auth\Models\FieldRoleOverride;
use Illuminate\Support\Facades\Cache;

class FieldAcl
{
    /**
     * ADR-010: Resolve visibility untuk satu field.
     * Priority: role_override (highest) > field.default_visibility > 'visible'.
     */
    public static function visible(string $kode, ?User $user = null): string
    {
        $user ??= auth()->user();
        if (! $user) return 'hidden'; // guest = hidden everything

        // SuperAdmin bypass
        if ($user->isSuperAdmin()) return 'visible';

        $map = self::resolveForUser($user);
        return $map[$kode] ?? 'visible';
    }

    /**
     * Batch resolve semua field untuk user (cached per request via static).
     */
    public static function resolveForUser(User $user): array
    {
        return Cache::remember("fieldacl.{$user->id}." . ($user->tenant_id ?? 'global'), 60, function () use ($user) {
            $map = [];
            foreach (Field::all() as $field) {
                $map[$field->kode] = $field->default_visibility;
            }

            $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
            $originalTeamId = $registrar->getPermissionsTeamId();
            $registrar->setPermissionsTeamId($user->tenant_id ?? 0);

            $roleIds = $user->roles->pluck('id');

            $registrar->setPermissionsTeamId($originalTeamId);

            $overrides = FieldRoleOverride::whereIn('role_id', $roleIds)
                ->where(function ($q) use ($user) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id);
                })
                ->get();

            foreach ($overrides as $o) {
                $field = Field::find($o->field_id);
                if ($field) {
                    $map[$field->kode] = $o->visibility;
                }
            }
            return $map;
        });
    }

    /** Helper for DataTables: kolom yang boleh ditampilkan */
    public static function columnsForIndex(string $model, ?User $user = null): array
    {
        $user ??= auth()->user();
        $map = self::resolveForUser($user);
        $allowed = [];
        foreach (Field::where('model', $model)->get() as $f) {
            $vis = $map[$f->kode] ?? 'visible';
            if ($vis !== 'hidden') {
                $allowed[] = $f->kolom;
            }
        }
        return $allowed;
    }

    public static function clearCache(?int $userId = null, ?int $tenantId = null): void
    {
        if ($userId) {
            Cache::forget("fieldacl.{$userId}." . ($tenantId ?? 'global'));
        } else {
            // Clear all fieldacl caches (heavy — use sparingly)
            Cache::flush();
        }
    }
}
