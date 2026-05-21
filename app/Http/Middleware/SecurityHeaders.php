<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $scriptSources = ["'self'", "'unsafe-inline'"];
        $styleSources = ["'self'", "'unsafe-inline'"];
        $connectSources = ["'self'"];

        if (app()->environment('local')) {
            $scriptSources[] = 'http://localhost:*';
            $scriptSources[] = 'http://127.0.0.1:*';
            $styleSources[] = 'http://localhost:*';
            $styleSources[] = 'http://127.0.0.1:*';
            $connectSources[] = 'ws://localhost:*';
            $connectSources[] = 'ws://127.0.0.1:*';
            $connectSources[] = 'http://localhost:*';
            $connectSources[] = 'http://127.0.0.1:*';
        }

        $csp = implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            "font-src 'self' data:",
            "img-src 'self' data: https://cdn.discordapp.com",
            'connect-src '.implode(' ', $connectSources),
            'frame-src https://discord.com',
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
