<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use App\Models\Departamento;
use Illuminate\Http\JsonResponse;

class NoticiaController extends Controller
{
    public function index(Departamento $departamento): JsonResponse
    {
        $noticias = Noticia::ativas()
            ->orderByDesc('inicio')
            ->get(['titulo', 'fonte', 'link', 'imagem', 'inicio', 'fim']);

        return response()->json([
            'gerado'   => now()->toISOString(),
            'total'    => $noticias->count(),
            'noticias' => $noticias,
        ]);
    }
}
