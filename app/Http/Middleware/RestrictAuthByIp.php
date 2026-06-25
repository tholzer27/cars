<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAuthByIp
{
    public const ALLOWED_IP = '31.165.85.175';

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(self::allows($request), 404);

        return $next($request);
    }

    public static function allows(Request $request): bool
    {
        return $request->ip() === self::ALLOWED_IP;
    }
}
