<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Informe;
use Illuminate\Http\JsonResponse;

class InformeController extends Controller
{
    public function index(Departamento $departamento): JsonResponse
    {
        $informes = Informe::with(['departamento', 'unidade'])
            ->paraMonitor($departamento)
            ->latest()
            ->get()
            ->map(fn (Informe $i) => $i->paraApi())
            ->values();

        return response()->json(['informes' => $informes]);
    }
}
