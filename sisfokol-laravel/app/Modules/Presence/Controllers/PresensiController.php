<?php

namespace App\Modules\Presence\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Presence\Services\QrScannerService;
use App\Support\TenantContext;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PresensiController extends Controller
{
    public function __construct(private QrScannerService $scanner) {}

    /**
     * QR Scanner page — only picket officers and teachers may access.
     */
    public function scan()
    {
        Gate::authorize('viewAny', Attendance::class);

        return view('presence.scan');
    }

    /**
     * Process a QR code scan (AJAX + form fallback).
     */
    public function storeScan(Request $request)
    {
        Gate::authorize('create', Attendance::class);

        $request->validate(['qr_payload' => 'required|string|max:255']);

        $tenantId = Auth::user()->tenant_id;

        try {
            $attendance = $this->scanner->scan(
                $request->qr_payload,
                $tenantId,
                $request->ip()
            );

            $siswa = $attendance->attendable;

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => "Presensi {$siswa?->nama} berhasil dicatat.",
                    'status'   => $attendance->status,
                    'type'     => $attendance->type,
                    'time'     => $attendance->time->format('H:i'),
                ]);
            }

            return back()->with('success', "Presensi {$siswa?->nama} berhasil dicatat.");
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rekap / list of attendance records.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Attendance::class);

        // [2026-06-29 | AG] Validate date and status query parameters strictly
        $request->validate([
            'date'   => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|in:present,late,early',
        ]);

        $query = Attendance::with('attendable')
            ->latest('date');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(25)->withQueryString();

        return view('presence.rekap', compact('attendances'));
    }
}
