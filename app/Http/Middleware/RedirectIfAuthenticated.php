<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirecționează în funcție de rol
                if ($user->hasRole('super-admin')) {
                    return redirect()->intended('/admin/dashboard');
                } else {
                    return redirect()->intended('/app/dashboard');
                }
            }
        }

        return $next($request);
    }
}
