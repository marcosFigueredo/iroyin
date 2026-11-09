<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Semestre;
use Illuminate\Http\JsonResponse;

class HorarioController extends Controller
{
    public function index(Departamento $departamento): JsonResponse
    {
        $dias = ['domingo','segunda','terca','quarta','quinta','sexta','sabado'];
        $dia  = $dias[now()->dayOfWeek];

        $semestre = Semestre::vigente();

        if (!$semestre) {
            return response()->json(['aulas' => [], 'dia' => $dia, 'semestre' => null]);
        }

        $aulas = Horario::with('unidade')
            ->where('semestre_id', $semestre->id)
            ->where('dia_semana', $dia)
            ->whereHas('unidade', fn ($q) => $q->where('departamento_id', $departamento->id))
            ->orderBy('inicio')
            ->get()
            ->map(fn (Horario $h) => [
                'disciplina' => $h->disciplina,
                'professor'  => $h->professor,
                'curso'      => $h->curso,
                'inicio'     => $h->inicio,
                'fim'        => $h->fim,
                'sala'       => $h->sala,
            ]);

        return response()->json([
            'semestre' => $semestre->nome,
            'dia'      => $dia,
            'aulas'    => $aulas,
        ]);
    }
}
