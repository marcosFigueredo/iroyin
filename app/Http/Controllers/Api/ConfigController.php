<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\Instituicao;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $inst = Instituicao::current();
        $cfg  = Configuracao::current();

        return response()->json([
            // identidade da instituição
            'nome'         => $inst->nome,
            'sigla'        => $inst->sigla,
            'departamento' => $inst->departamento,
            'cidade'       => $inst->cidade,
            'estado'       => $inst->estado,
            'texto_banner' => $inst->texto_banner,

            // ciclo de exibição
            'duracao_horarios' => $cfg->duracao_horarios,
            'duracao_noticia'  => $cfg->duracao_noticia,

            // tema visual
            'tema' => $cfg->tema,
        ]);
    }
}
