<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Requests\StartImpersonationRequest;
use App\Modules\Auth\Services\ImpersonationService;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function __construct(private ImpersonationService $impersonation) {}

    public function start(User $target, StartImpersonationRequest $request)
    {
        $impersonator = $request->user();
        $this->impersonation->start($impersonator, $target, $request);
        return redirect()->route('dashboard')->with('status', "Login sebagai {$target->nama}");
    }

    public function stop(Request $request)
    {
        $this->impersonation->stop($request);
        return redirect()->route('dashboard')->with('status', 'Kembali ke akun Anda.');
    }
}
