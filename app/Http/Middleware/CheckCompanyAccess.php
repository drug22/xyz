<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Super admin poate accesa tot
        if ($request->user()->hasRole('super-admin')) {
            return $next($request);
        }

        // Utilizatorii normali trebuie să aibă companie
        if (!$request->user()->company_id) {
            abort(403, 'Access denied: No company assigned');
        }

        // Verifică dacă compania este activă
        if (!$request->user()->company->is_active) {
            abort(403, 'Access denied: Company is inactive');
        }

        // Verifică dacă utilizatorul este activ
        if (!$request->user()->is_active) {
            abort(403, 'Access denied: User is inactive');
        }

        return $next($request);
    }
}
