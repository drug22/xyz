<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // Dacă utilizatorul nu are rolul necesar, redirectionează
        if ($request->user()->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('app.dashboard');
    }
}
