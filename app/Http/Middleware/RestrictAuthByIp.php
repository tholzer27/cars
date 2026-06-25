<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAuthByIp
{
    private const ALLOWED_IP = '31.165.85.175';

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->ip() === self::ALLOWED_IP, 404);

        return $next($request);
    }
}
