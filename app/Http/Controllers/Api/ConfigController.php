<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Instituicao;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    public function index(Departamento $departamento): JsonResponse
    {
        $inst   = Instituicao::current();
        $campus = $departamento->campus;

        return response()->json([
            // identidade
            'nome'         => $inst->nome,
            'sigla'        => $inst->sigla,
            'departamento' => $departamento->nome,
            'slug'         => $departamento->slug,
            'cidade'       => $campus->cidade,
            'estado'       => $campus->estado,
            'texto_banner' => $inst->texto_banner,
            'logo_url'     => $inst->logo_url ? asset($inst->logo_url) : asset('images/logoUneb.png'),
            'cabecalho'    => mb_strtoupper("{$departamento->slug} · {$departamento->nome} · {$campus->cidade}"),

            // ciclo de exibição
            'duracao_horarios' => $departamento->duracao_horarios,
            'duracao_noticia'  => $departamento->duracao_noticia,

            // tema visual
            'tema' => $departamento->tema,

            // versão do celular (usada pelo QR code do monitor)
            'url_acessivel' => url("/{$departamento->slug}/acessivel"),
        ]);
    }
}
