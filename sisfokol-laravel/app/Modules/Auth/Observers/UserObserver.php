<?php

namespace App\Modules\Auth\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        app(\App\Modules\Auth\Services\AuditLogger::class)->log(
            'user.created',
            $user,
            ['username' => $user->username, 'nama' => $user->nama, 'tipe' => $user->tipe],
            request(),
            [],
            User::class,
            $user->id
        );
    }

    public function updated(User $user): void
    {
        // Don't log update event if it was just updating last_login_at
        $changes = $user->getChanges();
        if (count($changes) === 1 && isset($changes['last_login_at'])) {
            return;
        }

        app(\App\Modules\Auth\Services\AuditLogger::class)->log(
            'user.updated',
            $user,
            $changes,
            request(),
            $user->getOriginal(),
            User::class,
            $user->id
        );
    }
}
