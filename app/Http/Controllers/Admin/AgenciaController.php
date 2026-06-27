<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use Illuminate\Http\Request;

class AgenciaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:100'],
            'sigla'           => ['required', 'string', 'max:20'],
            'cor_hex'         => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'url_noticias_rss'=> ['nullable', 'url', 'max:500'],
            'url_editais'     => ['nullable', 'url', 'max:500'],
        ]);

        Agencia::create($data);

        return back()->with('success', 'Agência cadastrada.');
    }

    public function update(Request $request, Agencia $agencia)
    {
        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:100'],
            'sigla'           => ['required', 'string', 'max:20'],
            'cor_hex'         => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'url_noticias_rss'=> ['nullable', 'url', 'max:500'],
            'url_editais'     => ['nullable', 'url', 'max:500'],
        ]);

        $agencia->update($data);

        return back()->with('success', 'Agência atualizada.');
    }

    public function destroy(Agencia $agencia)
    {
        $agencia->delete();
        return back()->with('success', 'Agência removida.');
    }
}
