<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadeController extends Controller
{
    public function index(Request $request)
    {
        if (! $id = $request->user()->escopoDepartamentoId()) {
            return $this->semDepartamento();
        }

        return view('admin.unidades.index', [
            'unidades' => Unidade::where('departamento_id', $id)->orderBy('tipo')->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $id = $request->user()->escopoDepartamentoId()) {
            return $this->semDepartamento();
        }

        Unidade::create($this->validar($request, $id) + ['departamento_id' => $id]);

        return back()->with('success', 'Cadastro criado.');
    }

    public function edit(Request $request, int $unidade)
    {
        return view('admin.unidades.edit', ['unidade' => $this->buscar($request, $unidade)]);
    }

    public function update(Request $request, int $unidade)
    {
        $item = $this->buscar($request, $unidade);

        if ($item->horarios()->exists() && $request->input('tipo') !== 'curso') {
            return back()->withInput()->withErrors(['tipo' => 'Este curso tem horários cadastrados e não pode mudar de tipo.']);
        }

        $item->update($this->validar($request, $item->departamento_id, $item) + ['ativo' => $request->boolean('ativo')]);

        return redirect()->route('admin.unidades.index')->with('success', 'Cadastro atualizado.');
    }

    public function destroy(Request $request, int $unidade)
    {
        $item = $this->buscar($request, $unidade);

        if (! $item->podeExcluir()) {
            return back()->with('error', "\"{$item->nome}\" tem horários, informes ou usuários e não pode ser excluído. Desative o cadastro.");
        }

        $item->delete();

        return back()->with('success', 'Cadastro removido.');
    }

    private function buscar(Request $request, int $id): Unidade
    {
        return Unidade::where('departamento_id', $request->user()->escopoDepartamentoId())->findOrFail($id);
    }

    private function validar(Request $request, int $departamentoId, ?Unidade $atual = null): array
    {
        return $request->validate([
            'tipo' => ['required', Rule::in(Unidade::TIPOS)],
            'nome' => [
                'required', 'string', 'max:255',
                Rule::unique('unidades', 'nome')->where('departamento_id', $departamentoId)->ignore($atual?->id),
            ],
        ]);
    }

    private function semDepartamento()
    {
        return redirect()->route('admin.departamentos.index')
            ->with('error', 'Escolha um departamento em "Operar como" para continuar.');
    }
}
