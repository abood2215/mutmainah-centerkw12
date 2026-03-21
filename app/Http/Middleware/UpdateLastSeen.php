<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Updates the authenticated user's last_seen_at timestamp on every request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            auth()->user()->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
