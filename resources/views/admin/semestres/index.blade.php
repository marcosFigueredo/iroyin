<x-app-layout>
    <x-slot name="header">Semestres</x-slot>

    <div class="row g-4">

        {{-- Formulário novo semestre --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1a3369;">Novo Semestre</h6>
                    <form method="POST" action="{{ route('admin.semestres.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Semestre</label>
                            <input type="text" name="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   placeholder="Ex: 2026.2"
                                   value="{{ old('nome') }}" required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Formato: 2026.1 ou 2026.2</div>
                        </div>
                        <button type="submit" class="btn text-white w-100" style="background-color:#1a3369;">
                            <i class="bi bi-plus-lg me-1"></i> Criar semestre
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lista de semestres --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Semestre</th>
                                <th class="text-center">Aulas</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($semestres as $semestre)
                            <tr>
                                <td class="fw-semibold">{{ $semestre->nome }}</td>
                                <td class="text-center text-muted">{{ $semestre->horarios_count }}</td>
                                <td class="text-center">
                                    @if($semestre->ativo)
                                        <span class="badge bg-success">Ativo no painel</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.semestres.horarios.index', $semestre) }}"
                                       class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-list-ul"></i> Horários
                                    </a>
                                    @if(!$semestre->ativo)
                                    <form method="POST" action="{{ route('admin.semestres.ativar', $semestre) }}"
                                          class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success me-1"
                                                title="Ativar no painel">
                                            <i class="bi bi-play-circle"></i> Ativar
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.semestres.destroy', $semestre) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Excluir semestre {{ $semestre->nome }} e todos os horários?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum semestre cadastrado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
