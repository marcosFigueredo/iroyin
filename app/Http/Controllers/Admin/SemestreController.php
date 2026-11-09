<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semestre;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SemestreController extends Controller
{
    public function index()
    {
        $semestres = Semestre::withCount('horarios')->orderByDesc('inicio')->get();
        return view('admin.semestres.index', compact('semestres'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'   => ['required', 'regex:/^\d{4}\.[12]$/', 'unique:semestres,nome'],
            'inicio' => ['required', 'date'],
            'fim'    => ['required', 'date', 'after:inicio'],
        ], [
            'nome.regex'  => 'Formato inválido. Use: 2026.1 ou 2026.2',
            'nome.unique' => 'Este semestre já existe.',
            'fim.after'   => 'O fim deve ser depois do início.',
        ]);

        if (Semestre::sobrepoe(Carbon::parse($request->inicio), Carbon::parse($request->fim))) {
            return back()->withInput()->withErrors(['inicio' => 'As datas coincidem com outro semestre.']);
        }

        Semestre::create($request->only('nome', 'inicio', 'fim'));

        return back()->with('success', "Semestre {$request->nome} criado.");
    }

    public function update(Request $request, Semestre $semestre)
    {
        $data = $request->validate([
            'inicio' => ['required', 'date'],
            'fim'    => ['required', 'date', 'after:inicio'],
        ], ['fim.after' => 'O fim deve ser depois do início.']);

        if (Semestre::sobrepoe(Carbon::parse($data['inicio']), Carbon::parse($data['fim']), $semestre->id)) {
            return back()->withErrors(['inicio' => 'As datas coincidem com outro semestre.']);
        }

        $semestre->update($data);

        return back()->with('success', "Datas do semestre {$semestre->nome} atualizadas.");
    }

    public function destroy(Semestre $semestre)
    {
        if ($semestre->estaVigente()) {
            return back()->with('error', 'Não é possível excluir o semestre vigente.');
        }

        if ($semestre->horarios()->exists()) {
            return back()->with('error', 'Este semestre tem horários cadastrados. Corrija as datas em vez de excluir.');
        }
        $semestre->delete();
        return back()->with('success', "Semestre {$semestre->nome} removido.");
    }
}
