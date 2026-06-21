<?php

namespace App\Plugins\Kurikulum\Policies;

use App\Models\User;
use App\Plugins\Kurikulum\Models\Kurikulum;

class KurikulumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kurikulum.view') || $user->can('kurikulum.manage');
    }

    public function view(User $user, Kurikulum $kurikulum): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return ($user->can('kurikulum.view') || $user->can('kurikulum.manage'))
            && $user->tenant_id === $kurikulum->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->can('kurikulum.manage');
    }

    public function update(User $user, Kurikulum $kurikulum): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->can('kurikulum.manage')
            && $user->tenant_id === $kurikulum->tenant_id;
    }

    public function delete(User $user, Kurikulum $kurikulum): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->can('kurikulum.manage')
            && $user->tenant_id === $kurikulum->tenant_id;
    }
}
