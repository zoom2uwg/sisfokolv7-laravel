<?php

namespace App\Modules\Presence\Services;

use App\Models\Permit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class IzinApprovalService
{
    /**
     * Submit a new permit/leave request.
     *
     * @param  array  $data  Must contain: permitable_type, permitable_id, date, type, reason
     * @param  User   $submittedBy  The user submitting the request
     */
    public function submit(array $data, User $submittedBy): Permit
    {
        return DB::transaction(function () use ($data, $submittedBy) {
            return Permit::create([
                'user_id'         => $submittedBy->id,
                'permitable_type' => $data['permitable_type'],
                'permitable_id'   => $data['permitable_id'],
                'date'            => $data['date'],
                'type'            => $data['type'],
                'reason'          => $data['reason'],
                'status'          => 'pending',
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);
        });
    }

    /**
     * Approve a pending permit.
     *
     * @param  Permit  $permit
     * @param  User    $approver
     */
    public function approve(Permit $permit, User $approver): Permit
    {
        if ($permit->status !== 'pending') {
            throw new Exception('Izin ini sudah diproses sebelumnya.');
        }

        $permit->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $permit->fresh();
    }

    /**
     * Reject a pending permit.
     *
     * @param  Permit  $permit
     * @param  User    $approver
     * @param  string  $reason  Rejection reason / note
     */
    public function reject(Permit $permit, User $approver, string $reason = ''): Permit
    {
        if ($permit->status !== 'pending') {
            throw new Exception('Izin ini sudah diproses sebelumnya.');
        }

        $permit->update([
            'status'      => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'note'        => $reason,
        ]);

        return $permit->fresh();
    }
}
