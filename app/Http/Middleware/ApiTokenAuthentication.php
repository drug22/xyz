<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;

class ApiTokenAuthentication
{
    public function handle(Request $request, Closure $next, $ability = null)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'API token required',
                'message' => 'Authorization header with Bearer token is required'
            ], 401);
        }

        // Find valid token
        $apiToken = ApiToken::where('is_active', true)
            ->get()
            ->first(function ($apiToken) use ($token, $ability, $request) {
                return $apiToken->isValid($token, $ability, $request->ip());
            });

        if (!$apiToken) {
            return response()->json([
                'error' => 'Invalid or expired API token',
                'message' => 'The provided API token is invalid, expired, or lacks required permissions'
            ], 401);
        }

        // Mark token as used
        $apiToken->markAsUsed();

        // Add token info to request for logging
        $request->attributes->set('api_token', $apiToken);
        $request->attributes->set('api_token_name', $apiToken->name);

        return $next($request);
    }
}
