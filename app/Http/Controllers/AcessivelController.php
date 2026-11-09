<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\Semestre;
use Illuminate\Http\JsonResponse;
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

    public function index(Departamento $departamento): View
    {
        $diaKey  = self::DIAS[now()->dayOfWeek];
        $diaNome = self::DIAS_NOMES[$diaKey];

        $semestre = Semestre::vigente();

        $aulas = collect();
        if ($semestre) {
            $aulas = Horario::with('unidade')
                ->where('semestre_id', $semestre->id)
                ->where('dia_semana', $diaKey)
                ->whereHas('unidade', fn ($q) => $q->where('departamento_id', $departamento->id))
                ->orderBy('inicio')
                ->get();
        }

        $turnos = [
            'Manhã'  => $aulas->filter(fn($a) => $a->inicio < '12:00'),
            'Tarde'  => $aulas->filter(fn($a) => $a->inicio >= '12:00' && $a->inicio < '18:00'),
            'Noite'  => $aulas->filter(fn($a) => $a->inicio >= '18:00'),
        ];

        $pesos = ['urgente' => 0, 'importante' => 1, 'normal' => 2];

        $informes = Informe::with(['departamento', 'unidade'])
            ->paraMonitor($departamento)
            ->latest()
            ->get()
            ->sortBy(fn (Informe $i) => [$pesos[$i->urgencia] ?? 2, $i->departamento_id ? 0 : 1])
            ->values();

        return view('acessivel', [
            'departamento' => $departamento,
            'informes'     => $informes,
            'semestre'   => $semestre,
            'diaNome'    => $diaNome,
            'turnos'     => $turnos,
            'totalAulas' => $aulas->count(),
        ]);
    }

    public function manifest(Departamento $departamento): JsonResponse
    {
        $endereco = "/{$departamento->slug}/acessivel";

        return response()->json([
            'name'             => 'Horários e informes · '.mb_strtoupper($departamento->slug),
            'short_name'       => 'Horários',
            'description'      => 'Consulta acessível de horários e informes',
            'start_url'        => $endereco,
            'scope'            => $endereco,
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#ffffff',
            'theme_color'      => '#1a3a6b',
            'lang'             => 'pt-BR',
            'icons'            => [['src' => '/favicon.ico', 'sizes' => 'any', 'type' => 'image/x-icon']],
        ]);
    }
}
