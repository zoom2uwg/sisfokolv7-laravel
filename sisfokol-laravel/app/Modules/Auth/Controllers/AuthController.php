<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        $user = \App\Models\User::where('username', $credentials['username'])->first();

        if (! $user || ! $user->aktif) {
            return back()->withErrors(['username' => 'Akun tidak ditemukan atau tidak aktif.'])->onlyInput('username');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->audit->log('login.failed', null, ['username' => $credentials['username']], $request);
            return back()->withErrors(['password' => 'Kredensial salah.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        $this->audit->log('login.success', $user, [], $request);

        return $this->redirectAfterLogin($user);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($user) {
            $this->audit->log('logout', $user, [], $request);
        }
        return redirect()->route('login');
    }

    private function redirectAfterLogin($user): \Illuminate\Http\RedirectResponse
    {
        if ($user->must_reset_password) {
            return redirect()->route('password.change');
        }
        return redirect()->intended(route('dashboard'));
    }
}
