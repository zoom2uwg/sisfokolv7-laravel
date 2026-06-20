<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordReset
{
    private array $exemptRoutes = ['password.change', 'password.change.store', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->must_reset_password && ! $request->routeIs($this->exemptRoutes)) {
            return redirect()->route('password.change');
        }
        return $next($request);
    }
}
