<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semestre;
use Illuminate\Http\Request;

class SemestreController extends Controller
{
    public function index()
    {
        $semestres = Semestre::withCount('horarios')->orderByDesc('nome')->get();
        return view('admin.semestres.index', compact('semestres'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'regex:/^\d{4}\.[12]$/', 'unique:semestres,nome'],
        ], [
            'nome.regex' => 'Formato inválido. Use: 2026.1 ou 2026.2',
            'nome.unique' => 'Este semestre já existe.',
        ]);

        Semestre::create(['nome' => $request->nome]);

        return back()->with('success', "Semestre {$request->nome} criado.");
    }

    public function ativar(Semestre $semestre)
    {
        Semestre::ativar($semestre->id);
        return back()->with('success', "Semestre {$semestre->nome} ativado no painel.");
    }

    public function destroy(Semestre $semestre)
    {
        if ($semestre->ativo) {
            return back()->with('error', 'Não é possível excluir o semestre ativo.');
        }
        $semestre->delete();
        return back()->with('success', "Semestre {$semestre->nome} removido.");
    }
}
