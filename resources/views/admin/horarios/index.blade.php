<x-app-layout>
    <x-slot name="header">Horários — {{ $semestre->nome }}{{ $semestre->ativo ? ' (ativo no painel)' : '' }}</x-slot>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.semestres.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Semestres
        </a>
        <a href="{{ route('admin.semestres.horarios.create', $semestre) }}" class="btn btn-sm text-white" style="background-color:#1a3369;">
            <i class="bi bi-plus-lg me-1"></i> Adicionar aula
        </a>

        {{-- Importar CSV --}}
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalImportar">
            <i class="bi bi-upload me-1"></i> Importar CSV
        </button>
    </div>

    {{-- Tabela por dia --}}
    @php
        $dias = ['segunda'=>'Segunda','terca'=>'Terça','quarta'=>'Quarta','quinta'=>'Quinta','sexta'=>'Sexta','sabado'=>'Sábado'];
    @endphp

    @forelse($horarios as $dia => $aulas)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header fw-bold" style="background-color:#e4e7ec; color:#1a3369;">
            {{ $dias[$dia] ?? $dia }}
            <span class="badge bg-secondary ms-2">{{ $aulas->count() }} aula(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Disciplina</th>
                        <th>Professor</th>
                        <th>Curso</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Sala</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aulas as $aula)
                    <tr>
                        <td>{{ $aula->disciplina }}</td>
                        <td class="text-muted">{{ $aula->professor }}</td>
                        <td class="text-muted">{{ $aula->curso }}</td>
                        <td>{{ $aula->inicio }}</td>
                        <td>{{ $aula->fim }}</td>
                        <td>{{ $aula->sala }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.semestres.horarios.edit', [$semestre, $aula]) }}"
                               class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.semestres.horarios.destroy', [$semestre, $aula]) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Remover esta aula?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
            Nenhum horário cadastrado neste semestre.<br>
            <a href="{{ route('admin.semestres.horarios.create', $semestre) }}" class="mt-3 btn btn-sm text-white" style="background-color:#1a3369;">
                Adicionar primeira aula
            </a>
        </div>
    </div>
    @endforelse

    {{-- Modal importar CSV --}}
    <div class="modal fade" id="modalImportar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color:#1a3369;">Importar CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST"
                      action="{{ route('admin.semestres.horarios.importar', $semestre) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            O arquivo CSV deve conter as colunas (na primeira linha):<br>
                            <code>dia, disciplina, professor, curso, inicio, fim, sala</code>
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Arquivo CSV</label>
                            <input type="file" name="csv" class="form-control" accept=".csv,.txt" required>
                        </div>
                        <div class="alert alert-warning small mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Os registros serão <strong>adicionados</strong> aos existentes, não substituídos.
                            Para substituir, exclua as aulas do dia antes de importar.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color:#1a3369;">
                            <i class="bi bi-upload me-1"></i> Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
