<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigirTrocaSenha
{
    private const LIBERADAS = ['profile.edit', 'profile.update', 'password.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->trocar_senha && ! $request->routeIs(self::LIBERADAS)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Por segurança, troque a senha inicial para continuar.');
        }

        return $next($request);
    }
}
