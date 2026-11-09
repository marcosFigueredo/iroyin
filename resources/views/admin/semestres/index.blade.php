<x-app-layout>
    <x-slot name="header">Semestres</x-slot>

    <div class="row g-4">

        @if(auth()->user()->isGestor())
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
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Início</label>
                            <input type="date" name="inicio" class="form-control @error('inicio') is-invalid @enderror" value="{{ old('inicio') }}" required>
                            @error('inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fim</label>
                            <input type="date" name="fim" class="form-control @error('fim') is-invalid @enderror" value="{{ old('fim') }}" required>
                            @error('fim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn text-white w-100" style="background-color:#1a3369;">
                            <i class="bi bi-plus-lg me-1"></i> Criar semestre
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @endif

        {{-- Lista de semestres --}}
        <div class="col-lg-{{ auth()->user()->isGestor() ? 8 : 12 }}">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Semestre</th>
                                <th>Período</th>
                                <th class="text-center">Aulas</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($semestres as $semestre)
                            <tr>
                                <td class="fw-semibold">{{ $semestre->nome }}</td>
                                <td class="text-muted">{{ $semestre->inicio?->format('d/m/Y') }} a {{ $semestre->fim?->format('d/m/Y') }}</td>
                                <td class="text-center text-muted">{{ $semestre->horarios_count }}</td>
                                <td class="text-center">
                                    @if($semestre->estaVigente())
                                        <span class="badge bg-success">Vigente</span>
                                    @else
                                        <span class="badge bg-secondary">Fora do período</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(auth()->user()->isGestor())
                                    <details class="d-inline-block text-start me-1">
                                        <summary class="btn btn-sm btn-outline-secondary">Datas</summary>
                                        <form method="POST" action="{{ route('admin.semestres.update', $semestre) }}" class="mt-2 d-flex gap-1">
                                            @csrf @method('PUT')
                                            <input type="date" name="inicio" class="form-control form-control-sm" value="{{ $semestre->inicio?->toDateString() }}" required aria-label="Início">
                                            <input type="date" name="fim" class="form-control form-control-sm" value="{{ $semestre->fim?->toDateString() }}" required aria-label="Fim">
                                            <button class="btn btn-sm text-white" style="background-color:#1a3369;">Salvar</button>
                                        </form>
                                    </details>
                                    @endif
                                    <a href="{{ route('admin.semestres.horarios.index', $semestre) }}"
                                       class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-list-ul"></i> Horários
                                    </a>
                                    @if(auth()->user()->isGestor() && !$semestre->estaVigente() && $semestre->horarios_count === 0)
                                    <form method="POST" action="{{ route('admin.semestres.destroy', $semestre) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Excluir semestre {{ $semestre->nome }}?')">
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
                                <td colspan="5" class="text-center text-muted py-4">Nenhum semestre cadastrado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
