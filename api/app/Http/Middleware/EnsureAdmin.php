<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['super_admin', 'admin', 'manager', 'staff'], true) || ! $user->is_active || $user->status !== 'active') {
            abort(403);
        }

        return $next($request);
    }
}
