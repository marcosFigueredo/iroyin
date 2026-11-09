<x-app-layout>
    <x-slot name="header">Cursos e unidades</x-slot>
    @php($tipos = ['curso' => 'Curso', 'laboratorio' => 'Laboratório', 'centro' => 'Centro de pesquisa'])
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1a3369;">Novo cadastro</h6>
                <form method="POST" action="{{ route('admin.unidades.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Tipo</label>
                        <select name="tipo" class="form-select">
                            @foreach($tipos as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Laboratórios e centros de pesquisa só publicam informes.</div></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Nome</label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <button class="btn text-white w-100" style="background-color:#1a3369;">Cadastrar</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm"><div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Nome</th><th>Tipo</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($unidades as $unidade)
                        <tr>
                            <td class="fw-semibold">{{ $unidade->nome }}</td>
                            <td class="text-muted">{{ $tipos[$unidade->tipo] ?? $unidade->tipo }}</td>
                            <td>@if($unidade->ativo)<span class="badge bg-success">Ativo</span>@else<span class="badge bg-secondary">Inativo</span>@endif</td>
                            <td class="text-end">
                                <a href="{{ route('admin.unidades.edit', $unidade) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i> Editar</a>
                                <form method="POST" action="{{ route('admin.unidades.destroy', $unidade) }}" class="d-inline" onsubmit="return confirm('Excluir este cadastro?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Nenhum curso ou unidade cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</x-app-layout>
