<x-app-layout>
    <x-slot name="header">Campi</x-slot>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1a3369;">Novo campus</h6>
                <form method="POST" action="{{ route('admin.campi.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Número</label>
                        <input type="number" name="numero" class="form-control @error('numero') is-invalid @enderror" value="{{ old('numero') }}">
                        @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Compõe o endereço do monitor. Deixe vazio se o campus não tiver número.</div></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Nome</label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label fw-semibold">Cidade</label>
                        <input type="text" name="cidade" class="form-control @error('cidade') is-invalid @enderror" value="{{ old('cidade') }}" required>
                        @error('cidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Usada na previsão do tempo do monitor.</div></div>
                    <div class="mb-3"><label class="form-label fw-semibold">UF</label>
                        <input type="text" name="estado" maxlength="2" class="form-control @error('estado') is-invalid @enderror" value="{{ old('estado', 'BA') }}" required>
                        @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <button class="btn text-white w-100" style="background-color:#1a3369;">Cadastrar</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm"><div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Nº</th><th>Campus</th><th>Cidade</th><th class="text-center">Departamentos</th><th></th></tr></thead>
                    <tbody>
                        @forelse($campi as $campus)
                        <tr>
                            <td>{{ $campus->numero ?? '—' }}</td>
                            <td class="fw-semibold">{{ $campus->nome }}</td>
                            <td class="text-muted">{{ $campus->cidade }}/{{ $campus->estado }}</td>
                            <td class="text-center">{{ $campus->departamentos_count }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.campi.destroy', $campus) }}" class="d-inline" onsubmit="return confirm('Excluir este campus?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum campus cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</x-app-layout>
