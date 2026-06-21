<?php

namespace App\Modules\Presence\Services;

use App\Models\Attendance;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Presence\Events\PresenceRecorded;
use App\Support\TenantContext;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class QrScannerService
{
    public function __construct(private PresensiRuleEngine $engine) {}

    /**
     * Scan student QR code and record attendance.
     * Returns the created Attendance record.
     */
    public function scan(string $qrPayload, int $tenantId, ?string $ipAddress = null): Attendance
    {
        // Set TenantContext
        app(TenantContext::class)->set($tenantId);

        return DB::transaction(function () use ($qrPayload, $tenantId, $ipAddress) {
            // Find student in this tenant
            $siswa = Siswa::where('status', 'aktif')
                ->where(function ($q) use ($qrPayload) {
                    $q->where('nis', $qrPayload)
                      ->orWhere('qrcode', $qrPayload);
                })
                ->first();

            if (! $siswa) {
                throw new Exception('Siswa tidak ditemukan atau tidak aktif.');
            }

            // Find or associate the student's User account if one exists
            $user = \App\Models\User::where('userable_type', Siswa::class)
                ->where('userable_id', $siswa->id)
                ->first();

            if (! $user) {
                // Create a temporary User account for the student
                $user = \App\Models\User::create([
                    'tenant_id' => $tenantId,
                    'username' => 'siswa_' . $siswa->nis,
                    'nama' => $siswa->nama,
                    'email' => $siswa->nis . '@sekolah.sch.id',
                    'tipe' => 'siswa',
                    'password' => bcrypt('password'),
                    'aktif' => true,
                    'userable_type' => Siswa::class,
                    'userable_id' => $siswa->id,
                ]);
            }

            $now = Carbon::now();
            $dateStr = $now->format('Y-m-d');
            $type = $now->hour < 12 ? 'in' : 'out';

            // Check if already checked in/out today
            $exists = Attendance::where('user_id', $user->id)
                ->where('date', $dateStr)
                ->where('type', $type)
                ->exists();

            if ($exists) {
                throw new Exception("Siswa sudah melakukan presensi " . ($type === 'in' ? 'masuk' : 'pulang') . " hari ini.");
            }

            // Evaluate status
            $status = $this->engine->evaluate($now, $type);

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'attendable_type' => Siswa::class,
                'attendable_id' => $siswa->id,
                'date' => $dateStr,
                'time' => $now->format('H:i'),
                'type' => $type,
                'source' => 'qr',
                'status' => $status,
                'ip_address' => $ipAddress,
            ]);

            event(new PresenceRecorded($attendance));

            return $attendance;
        });
    }
}
