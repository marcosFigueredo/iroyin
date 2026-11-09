<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartamentoController extends Controller
{
    public function index()
    {
        return view('admin.departamentos.index', [
            'departamentos' => Departamento::with('campus')->orderBy('slug')->get(),
            'campi'         => Campus::orderBy('numero')->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (blank($request->input('slug')) && $request->filled('sigla')) {
            $campus = Campus::find($request->input('campus_id'));
            $request->merge(['slug' => Departamento::gerarSlug($request->input('sigla'), $campus?->numero)]);
        }

        $departamento = Departamento::create($this->validar($request));

        return back()->with('success', "Departamento criado. Endereço do monitor: /{$departamento->slug}");
    }

    public function update(Request $request, Departamento $departamento)
    {
        $departamento->update($this->validar($request, $departamento) + ['ativo' => $request->boolean('ativo')]);

        return back()->with('success', 'Departamento atualizado.');
    }

    public function destroy(Departamento $departamento)
    {
        if ($departamento->unidades()->exists() || $departamento->usuarios()->exists()) {
            return back()->with('error', 'Este departamento tem cursos, unidades ou usuários. Desative-o em vez de excluir.');
        }

        $departamento->delete();

        return back()->with('success', 'Departamento removido.');
    }

    private function validar(Request $request, ?Departamento $atual = null): array
    {
        return $request->validate([
            'campus_id' => ['required', 'exists:campi,id'],
            'sigla'     => ['required', 'string', 'max:30'],
            'nome'      => ['required', 'string', 'max:255'],
            'slug'      => [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/',
                Rule::notIn(Departamento::SLUGS_RESERVADOS),
                Rule::unique('departamentos', 'slug')->ignore($atual?->id),
            ],
        ], [
            'slug.not_in' => 'Este endereço é usado pelo sistema. Escolha outro.',
            'slug.unique' => 'Já existe um departamento com este endereço.',
            'slug.regex'  => 'Use apenas letras minúsculas, números e hífen.',
        ]);
    }
}
