<?php

namespace App\Modules\Academic\Policies;

use App\Models\User;
use App\Modules\Academic\Models\Guru;

class GuruPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('guru.view') || $user->can('employee.*') || $user->can('employee.view');
    }

    public function view(User $user, Guru $guru): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return ($user->can('employee.*') || $user->can('employee.view'))
            && $user->tenant_id === $guru->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->can('employee.*');
    }

    public function update(User $user, Guru $guru): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->can('employee.*') && $user->tenant_id === $guru->tenant_id;
    }

    public function delete(User $user, Guru $guru): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->can('employee.*') && $user->tenant_id === $guru->tenant_id;
    }
}
