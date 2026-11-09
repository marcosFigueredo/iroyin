<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function index()
    {
        $campi = Campus::withCount('departamentos')->orderBy('numero')->orderBy('nome')->get();

        return view('admin.campi.index', compact('campi'));
    }

    public function store(Request $request)
    {
        Campus::create($this->validar($request));

        return back()->with('success', 'Campus cadastrado.');
    }

    public function update(Request $request, Campus $campi)
    {
        $campi->update($this->validar($request));

        return back()->with('success', 'Campus atualizado.');
    }

    public function destroy(Campus $campi)
    {
        if ($campi->departamentos()->exists()) {
            return back()->with('error', 'Este campus tem departamentos e não pode ser excluído.');
        }

        $campi->delete();

        return back()->with('success', 'Campus removido.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'numero' => ['nullable', 'integer', 'min:1', 'max:999'],
            'nome'   => ['required', 'string', 'max:120'],
            'cidade' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'size:2'],
        ]);
    }
}
