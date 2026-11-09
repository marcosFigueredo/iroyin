<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartamentoConfigController extends Controller
{
    public function edit(Request $request)
    {
        if (! $departamento = $this->departamento($request)) {
            return $this->semDepartamento();
        }

        return view('admin.departamento-config.edit', compact('departamento'));
    }

    public function update(Request $request)
    {
        if (! $departamento = $this->departamento($request)) {
            return $this->semDepartamento();
        }

        $departamento->update($request->validate([
            'tema'             => ['required', Rule::in(Departamento::TEMAS)],
            'duracao_horarios' => ['required', 'integer', 'min:10', 'max:600'],
            'duracao_noticia'  => ['required', 'integer', 'min:10', 'max:30'],
        ]));

        return back()->with('success', 'Configurações do departamento atualizadas.');
    }

    private function departamento(Request $request): ?Departamento
    {
        return Departamento::find($request->user()->escopoDepartamentoId());
    }

    private function semDepartamento()
    {
        return redirect()->route('admin.departamentos.index')
            ->with('error', 'Escolha um departamento em "Operar como" para continuar.');
    }
}
