<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $minimumRole = 'reviewer'): Response
    {
        $user = $request->user();
        $role = UserRole::tryFrom($minimumRole);

        abort_if(! $user || ! $role || ! $user->isAtLeast($role), 403);

        return $next($request);
    }
}
