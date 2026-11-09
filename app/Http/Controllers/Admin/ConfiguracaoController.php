<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracaoController extends Controller
{
    public function edit(): View
    {
        return view('admin.configuracoes.edit', ['cfg' => Configuracao::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        Configuracao::current()->update($request->validate([
            'weather_api_key' => ['nullable', 'string', 'max:100'],
        ]));

        return back()->with('success', 'Configurações atualizadas com sucesso.');
    }
}
