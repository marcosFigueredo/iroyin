<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracaoController extends Controller
{
    public const TEMAS = [
        'azul'     => ['label' => 'Azul',     'primaria' => '#1a3369', 'fundo' => '#f0f2f5', 'texto' => '#1a3369'],
        'verde'    => ['label' => 'Verde',    'primaria' => '#006633', 'fundo' => '#f0f5f0', 'texto' => '#004d26'],
        'vermelho' => ['label' => 'Vermelho', 'primaria' => '#cc0000', 'fundo' => '#f5f0f0', 'texto' => '#990000'],
        'escuro'   => ['label' => 'Escuro',   'primaria' => '#2d2d2d', 'fundo' => '#1e1e1e', 'texto' => '#d4d4d4'],
        'roxo'     => ['label' => 'Roxo',     'primaria' => '#6f42c1', 'fundo' => '#f5f0ff', 'texto' => '#4b2a8a'],
    ];

    public function edit(): View
    {
        return view('admin.configuracoes.edit', [
            'cfg'   => Configuracao::current(),
            'temas' => self::TEMAS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cidade_clima'     => ['required', 'string', 'max:100'],
            'weather_api_key'  => ['nullable', 'string', 'max:100'],
            'duracao_horarios' => ['required', 'integer', 'min:10', 'max:600'],
            'duracao_noticia'  => ['required', 'integer', 'min:5',  'max:120'],
            'tema'             => ['required', 'string',  'in:' . implode(',', array_keys(self::TEMAS))],
        ]);

        Configuracao::current()->update($data);

        return back()->with('success', 'Configurações atualizadas com sucesso.');
    }
}
