<x-app-layout>
    <x-slot name="header">Fontes RSS</x-slot>

    <div class="row g-4">

        {{-- Formulário adicionar feed --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#1a3369;">Adicionar fonte</h6>
                    <form method="POST" action="{{ route('admin.feeds.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome</label>
                            <input type="text" name="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome') }}"
                                   placeholder="Ex: Agência FAPESP" required maxlength="100">
                            @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">URL do feed RSS</label>
                            <input type="url" name="url"
                                   class="form-control @error('url') is-invalid @enderror"
                                   value="{{ old('url') }}"
                                   placeholder="https://exemplo.com/feed/" required maxlength="500">
                            @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn text-white w-100" style="background-color:#1a3369;">
                            <i class="bi bi-plus-lg me-1"></i> Adicionar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lista de feeds --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>URL</th>
                                <th class="text-center">Ativo</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feeds as $feed)
                            <tr>
                                <td class="fw-semibold" style="white-space:nowrap;">{{ $feed->nome }}</td>
                                <td class="text-muted" style="font-size:.8rem;word-break:break-all;">{{ $feed->url }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.feeds.toggle', $feed) }}">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm {{ $feed->ativo ? 'btn-success' : 'btn-outline-secondary' }}"
                                                title="{{ $feed->ativo ? 'Desativar' : 'Ativar' }}">
                                            <i class="bi bi-{{ $feed->ativo ? 'toggle-on' : 'toggle-off' }}"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    {{-- Editar inline via modal --}}
                                    <button class="btn btn-sm btn-outline-secondary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-edit-{{ $feed->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.feeds.destroy', $feed) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Remover o feed {{ $feed->nome }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Modal editar --}}
                            <div class="modal fade" id="modal-edit-{{ $feed->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.feeds.update', $feed) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar feed</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nome</label>
                                                    <input type="text" name="nome" class="form-control"
                                                           value="{{ $feed->nome }}" required maxlength="100">
                                                </div>
                                                <div class="mb-1">
                                                    <label class="form-label fw-semibold">URL do feed RSS</label>
                                                    <input type="url" name="url" class="form-control"
                                                           value="{{ $feed->url }}" required maxlength="500">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn text-white" style="background-color:#1a3369;">Salvar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhuma fonte cadastrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
