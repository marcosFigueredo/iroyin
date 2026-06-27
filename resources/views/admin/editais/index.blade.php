<x-app-layout>
    <x-slot name="header">Editais Abertos</x-slot>

    <div class="row g-4">

        {{-- COLUNA ESQUERDA: Formulário --}}
        <div class="col-lg-4">

            {{-- Cadastrar Edital --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold" style="color:#1a3369;">
                    <i class="bi bi-plus-circle me-1"></i> Novo Edital
                </div>
                <div class="card-body p-3">
                    <form method="POST" action="{{ route('admin.editais.store') }}">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Agência</label>
                            <select name="agencia_id" class="form-select form-select-sm">
                                <option value="">— Selecione —</option>
                                @foreach($agencias as $ag)
                                    <option value="{{ $ag->id }}" {{ old('agencia_id') == $ag->id ? 'selected' : '' }}>
                                        {{ $ag->sigla }} — {{ $ag->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Título da chamada <span class="text-danger">*</span></label>
                            <input type="text" name="titulo"
                                   class="form-control form-control-sm @error('titulo') is-invalid @enderror"
                                   value="{{ old('titulo') }}" placeholder="Ex: Chamada CNPq/MCTI nº 06/2026"
                                   required maxlength="300">
                            @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Objetivo <span class="text-danger">*</span></label>
                            <textarea name="objetivo" rows="3"
                                      class="form-control form-control-sm @error('objetivo') is-invalid @enderror"
                                      placeholder="Descrição resumida da chamada..." required>{{ old('objetivo') }}</textarea>
                            @error('objetivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Link da chamada <span class="text-danger">*</span></label>
                            <input type="url" name="link"
                                   class="form-control form-control-sm @error('link') is-invalid @enderror"
                                   value="{{ old('link') }}" placeholder="https://..." required maxlength="500">
                            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Prazo de submissão</label>
                            <input type="date" name="data_fechamento"
                                   class="form-control form-control-sm @error('data_fechamento') is-invalid @enderror"
                                   value="{{ old('data_fechamento') }}">
                            @error('data_fechamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-sm text-white" style="background:#1a3369;">
                                <i class="bi bi-plus me-1"></i> Cadastrar edital
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Agências --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center" style="color:#1a3369;">
                    <span><i class="bi bi-buildings me-1"></i> Agências</span>
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseAgencias">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse" id="collapseAgencias">
                    <div class="card-body p-3">

                        {{-- Lista de agências --}}
                        @forelse($agencias as $ag)
                        <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded overflow-hidden" style="background:#f8f9fa;">
                            <div class="rounded-circle flex-shrink-0 mt-1"
                                 style="width:10px;height:10px;background:{{ $ag->cor_hex }};"></div>
                            <div class="flex-grow-1" style="min-width:0;word-break:break-word;">
                                <div class="fw-semibold small">{{ $ag->sigla }} — {{ $ag->nome }}</div>
                                @if($ag->url_editais)
                                <a href="{{ $ag->url_editais }}" target="_blank" rel="noopener"
                                   class="small text-muted d-block" style="font-size:0.7rem;">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Ver editais
                                </a>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.agencias.destroy', $ag) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0"
                                        onclick="return confirm('Remover {{ $ag->sigla }}?')" title="Remover">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @empty
                        <p class="small text-muted mb-3">Nenhuma agência cadastrada.</p>
                        @endforelse

                        {{-- Formulário nova agência --}}
                        <hr class="my-3">
                        <p class="small fw-semibold mb-2" style="color:#1a3369;">Nova agência</p>
                        <form method="POST" action="{{ route('admin.agencias.store') }}">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-8">
                                    <input type="text" name="nome" class="form-control form-control-sm"
                                           placeholder="Nome completo" required maxlength="100">
                                </div>
                                <div class="col-4">
                                    <input type="text" name="sigla" class="form-control form-control-sm"
                                           placeholder="Sigla" required maxlength="20">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-3">
                                    <label class="form-label" style="font-size:0.7rem;">Cor</label>
                                    <input type="color" name="cor_hex" value="#1a3369"
                                           class="form-control form-control-color form-control-sm w-100">
                                </div>
                                <div class="col-9">
                                    <label class="form-label" style="font-size:0.7rem;">RSS de notícias</label>
                                    <input type="url" name="url_noticias_rss" class="form-control form-control-sm"
                                           placeholder="https://.../feed" maxlength="500">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" style="font-size:0.7rem;">Página de editais</label>
                                <input type="url" name="url_editais" class="form-control form-control-sm"
                                       placeholder="https://.../editais" maxlength="500">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-plus me-1"></i> Adicionar agência
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUNA DIREITA: Lista de Editais --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold" style="color:#1a3369;">
                        <i class="bi bi-list-check me-1"></i> Editais cadastrados
                    </span>
                    <span class="badge bg-secondary">{{ $editais->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @forelse($editais as $edital)
                    <div class="p-3 border-bottom d-flex gap-3 align-items-start
                                {{ $edital->ativo ? '' : 'opacity-50' }}">

                        {{-- Badge agência --}}
                        <div class="flex-shrink-0 text-center" style="min-width:56px;">
                            <div class="rounded-2 px-2 py-1 text-white fw-bold small"
                                 style="background:{{ $edital->agencia?->cor_hex ?? '#6c757d' }};font-size:0.7rem;letter-spacing:1px;">
                                {{ $edital->agencia?->sigla ?? '—' }}
                            </div>
                            @if($edital->dias_restantes !== null)
                                @if($edital->dias_restantes < 0)
                                    <div class="text-danger small mt-1" style="font-size:0.65rem;">ENCERRADO</div>
                                @elseif($edital->dias_restantes <= 7)
                                    <div class="text-warning fw-bold small mt-1" style="font-size:0.65rem;">{{ $edital->dias_restantes }}d</div>
                                @else
                                    <div class="text-muted small mt-1" style="font-size:0.65rem;">{{ $edital->dias_restantes }}d</div>
                                @endif
                            @endif
                        </div>

                        {{-- Conteúdo --}}
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold text-truncate small" style="color:#1a3369;">{{ $edital->titulo }}</div>
                            <div class="text-muted small text-truncate">{{ $edital->objetivo }}</div>
                            @if($edital->data_fechamento)
                            <div class="text-muted mt-1" style="font-size:0.7rem;">
                                <i class="bi bi-calendar3 me-1"></i>Prazo: {{ $edital->data_fechamento->format('d/m/Y') }}
                            </div>
                            @endif
                        </div>

                        {{-- Ações --}}
                        <div class="d-flex gap-1 flex-shrink-0">
                            <form method="POST" action="{{ route('admin.editais.toggle', $edital) }}">
                                @csrf
                                <button class="btn btn-sm {{ $edital->ativo ? 'btn-success' : 'btn-outline-secondary' }}"
                                        title="{{ $edital->ativo ? 'Desativar' : 'Ativar' }}">
                                    <i class="bi bi-{{ $edital->ativo ? 'eye' : 'eye-slash' }}"></i>
                                </button>
                            </form>

                            <button class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar{{ $edital->id }}"
                                    title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form method="POST" action="{{ route('admin.editais.destroy', $edital) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Remover este edital?')" title="Remover">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Modal editar --}}
                    <div class="modal fade" id="modalEditar{{ $edital->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold" style="color:#1a3369;">Editar Edital</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('admin.editais.update', $edital) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Agência</label>
                                            <select name="agencia_id" class="form-select form-select-sm">
                                                <option value="">— Nenhuma —</option>
                                                @foreach($agencias as $ag)
                                                    <option value="{{ $ag->id }}" {{ $edital->agencia_id == $ag->id ? 'selected' : '' }}>
                                                        {{ $ag->sigla }} — {{ $ag->nome }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Título</label>
                                            <input type="text" name="titulo" class="form-control form-control-sm"
                                                   value="{{ $edital->titulo }}" required maxlength="300">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Objetivo</label>
                                            <textarea name="objetivo" rows="3" class="form-control form-control-sm"
                                                      required>{{ $edital->objetivo }}</textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Link</label>
                                            <input type="url" name="link" class="form-control form-control-sm"
                                                   value="{{ $edital->link }}" required maxlength="500">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold">Prazo</label>
                                            <input type="date" name="data_fechamento" class="form-control form-control-sm"
                                                   value="{{ $edital->data_fechamento?->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-sm text-white" style="background:#1a3369;">Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Nenhum edital cadastrado ainda.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
