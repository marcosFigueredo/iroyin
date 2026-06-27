<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Semestre;
use Illuminate\View\View;

class AcessivelController extends Controller
{
    private const DIAS = [
        0 => 'domingo', 1 => 'segunda', 2 => 'terca',
        3 => 'quarta',  4 => 'quinta',  5 => 'sexta', 6 => 'sabado',
    ];

    private const DIAS_NOMES = [
        'domingo' => 'Domingo',      'segunda' => 'Segunda-feira',
        'terca'   => 'Terça-feira',  'quarta'  => 'Quarta-feira',
        'quinta'  => 'Quinta-feira', 'sexta'   => 'Sexta-feira',
        'sabado'  => 'Sábado',
    ];

    public function index(): View
    {
        $diaKey  = self::DIAS[now()->dayOfWeek];
        $diaNome = self::DIAS_NOMES[$diaKey];

        $semestre = Semestre::where('ativo', true)->first();

        $aulas = collect();
        if ($semestre) {
            $aulas = Horario::where('semestre_id', $semestre->id)
                ->where('dia_semana', $diaKey)
                ->orderBy('inicio')
                ->get();
        }

        $turnos = [
            'Manhã'  => $aulas->filter(fn($a) => $a->inicio < '12:00'),
            'Tarde'  => $aulas->filter(fn($a) => $a->inicio >= '12:00' && $a->inicio < '18:00'),
            'Noite'  => $aulas->filter(fn($a) => $a->inicio >= '18:00'),
        ];

        return view('acessivel', [
            'semestre'   => $semestre,
            'diaNome'    => $diaNome,
            'turnos'     => $turnos,
            'totalAulas' => $aulas->count(),
        ]);
    }
}
