<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Edital;
use Illuminate\Http\JsonResponse;

class EditalController extends Controller
{
    public function index(): JsonResponse
    {
        $editais = Edital::with('agencia')
            ->ativos()
            ->orderBy('data_fechamento')
            ->get()
            ->map(function (Edital $e) {
                return [
                    'id'             => $e->id,
                    'agencia_nome'   => $e->agencia?->nome ?? '',
                    'agencia_sigla'  => $e->agencia?->sigla ?? '',
                    'agencia_cor'    => $e->agencia?->cor_hex ?? '#1a3369',
                    'agencia_url'    => $e->agencia?->url_editais ?? '',
                    'titulo'         => $e->titulo,
                    'objetivo'       => $e->objetivo,
                    'link'           => $e->link,
                    'data_fechamento'=> $e->data_fechamento?->format('d/m/Y') ?? null,
                    'dias_restantes' => $e->dias_restantes,
                ];
            });

        return response()->json(['editais' => $editais]);
    }
}
