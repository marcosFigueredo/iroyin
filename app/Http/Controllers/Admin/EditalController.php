<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agencia;
use App\Models\Edital;
use Illuminate\Http\Request;

class EditalController extends Controller
{
    public function index()
    {
        $editais  = Edital::with('agencia')->orderByDesc('created_at')->get();
        $agencias = Agencia::orderBy('nome')->get();

        return view('admin.editais.index', compact('editais', 'agencias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agencia_id'      => ['nullable', 'exists:agencias,id'],
            'titulo'          => ['required', 'string', 'max:300'],
            'objetivo'        => ['required', 'string'],
            'link'            => ['required', 'url', 'max:500'],
            'data_fechamento' => ['nullable', 'date'],
        ]);

        Edital::create($data);

        return back()->with('success', 'Edital cadastrado.');
    }

    public function update(Request $request, Edital $edital)
    {
        $data = $request->validate([
            'agencia_id'      => ['nullable', 'exists:agencias,id'],
            'titulo'          => ['required', 'string', 'max:300'],
            'objetivo'        => ['required', 'string'],
            'link'            => ['required', 'url', 'max:500'],
            'data_fechamento' => ['nullable', 'date'],
        ]);

        $edital->update($data);

        return back()->with('success', 'Edital atualizado.');
    }

    public function destroy(Edital $edital)
    {
        $edital->delete();
        return back()->with('success', 'Edital removido.');
    }

    public function toggle(Edital $edital)
    {
        $edital->update(['ativo' => ! $edital->ativo]);
        return back()->with('success', $edital->ativo ? 'Edital ativado.' : 'Edital desativado.');
    }
}
