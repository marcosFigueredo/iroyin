<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerfilMiddleware
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        if (! in_array($request->user()?->role, $perfis, true)) {
            abort(403, 'Seu perfil não tem acesso a esta área.');
        }

        return $next($request);
    }
}
