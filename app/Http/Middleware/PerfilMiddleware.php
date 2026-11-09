<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerfilMiddleware
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        $usuario = $request->user();

        if (! in_array($usuario?->role, $perfis, true)) {
            abort(403, 'Seu perfil não tem acesso a esta área.');
        }

        // Sem vínculo não há escopo: um filtro vazio não pode virar acesso amplo.
        if (($usuario->isDepartamento() && ! $usuario->departamento_id) || ($usuario->isUnidade() && ! $usuario->unidade_id)) {
            abort(403, 'Seu usuário ainda não foi vinculado a um departamento ou unidade.');
        }

        return $next($request);
    }
}
