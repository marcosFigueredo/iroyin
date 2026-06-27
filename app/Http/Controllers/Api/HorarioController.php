<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Semestre;
use Illuminate\Http\JsonResponse;

class HorarioController extends Controller
{
    public function index(): JsonResponse
    {
        $dias = ['domingo','segunda','terca','quarta','quinta','sexta','sabado'];
        $dia  = $dias[now()->dayOfWeek];

        $semestre = Semestre::where('ativo', true)->first();

        if (!$semestre) {
            return response()->json(['aulas' => [], 'dia' => $dia, 'semestre' => null]);
        }

        $aulas = Horario::where('semestre_id', $semestre->id)
            ->where('dia_semana', $dia)
            ->orderBy('inicio')
            ->get(['disciplina','professor','curso','inicio','fim','sala']);

        return response()->json([
            'semestre' => $semestre->nome,
            'dia'      => $dia,
            'aulas'    => $aulas,
        ]);
    }
}
