<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = $this->visiveis($request)->with(['departamento', 'unidade'])->orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        return view('admin.users.create', $this->opcoes($request));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(Request $request, int $user)
    {
        return view('admin.users.edit', ['user' => $this->buscar($request, $user)] + $this->opcoes($request));
    }

    public function update(Request $request, int $user)
    {
        $alvo = $this->buscar($request, $user);
        $data = $this->validar($request, $alvo);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['active'] = $request->boolean('active');

        $alvo->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, int $user)
    {
        $alvo = $this->buscar($request, $user);

        if ($alvo->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $alvo->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso.');
    }

    /** Gestor vê todos; o gabinete só vê as secretarias do próprio departamento. */
    private function visiveis(Request $request): Builder
    {
        $ator = $request->user();

        return $ator->isGestor()
            ? User::query()
            : User::where('departamento_id', $ator->departamento_id)->where('role', 'unidade');
    }

    private function buscar(Request $request, int $id): User
    {
        return $this->visiveis($request)->findOrFail($id);
    }

    private function opcoes(Request $request): array
    {
        $ator = $request->user();

        return [
            'perfis'        => $ator->isGestor() ? User::NOMES_PERFIS : ['unidade' => User::NOMES_PERFIS['unidade']],
            'departamentos' => $ator->isGestor() ? Departamento::orderBy('slug')->get() : collect(),
            'unidades'      => Unidade::with('departamento')
                ->when(! $ator->isGestor(), fn ($q) => $q->where('departamento_id', $ator->departamento_id))
                ->orderBy('nome')->get(),
        ];
    }

    private function validar(Request $request, ?User $alvo = null): array
    {
        $ator = $request->user();
        $perfis = $ator->isGestor() ? User::PERFIS : ['unidade'];

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users')->ignore($alvo?->id)],
            'role'            => ['required', Rule::in($perfis)],
            'active'          => ['boolean'],
            'password'        => [$alvo ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'departamento_id' => [
                Rule::requiredIf($ator->isGestor() && $request->input('role') === 'departamento'),
                'nullable', 'exists:departamentos,id',
            ],
            'unidade_id'      => [
                Rule::requiredIf($request->input('role') === 'unidade'),
                'nullable',
                Rule::exists('unidades', 'id')->when(
                    ! $ator->isGestor(),
                    fn ($r) => $r->where('departamento_id', $ator->departamento_id)
                ),
            ],
        ]);

        // O vínculo decorre do perfil, nunca de combinações livres do formulário.
        if ($data['role'] === 'unidade') {
            $data['departamento_id'] = Unidade::find($data['unidade_id'])->departamento_id;
        } else {
            $data['unidade_id'] = null;
            if ($data['role'] !== 'departamento') {
                $data['departamento_id'] = null;
            }
        }

        return $data;
    }
}
