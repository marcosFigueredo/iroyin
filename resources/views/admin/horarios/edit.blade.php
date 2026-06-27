<x-app-layout>
    <x-slot name="header">Editar Aula — {{ $semestre->nome }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.semestres.horarios.update', [$semestre, $horario]) }}">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dia da Semana</label>
                                <select name="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                                    @foreach(['segunda'=>'Segunda','terca'=>'Terça','quarta'=>'Quarta','quinta'=>'Quinta','sexta'=>'Sexta','sabado'=>'Sábado'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('dia_semana', $horario->dia_semana) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('dia_semana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sala</label>
                                <input type="text" name="sala" class="form-control @error('sala') is-invalid @enderror"
                                       value="{{ old('sala', $horario->sala) }}" required maxlength="20">
                                @error('sala') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Disciplina</label>
                                <input type="text" name="disciplina" class="form-control @error('disciplina') is-invalid @enderror"
                                       value="{{ old('disciplina', $horario->disciplina) }}" required>
                                @error('disciplina') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Professor(a)</label>
                                <input type="text" name="professor" class="form-control @error('professor') is-invalid @enderror"
                                       value="{{ old('professor', $horario->professor) }}" required>
                                @error('professor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Curso</label>
                                <input type="text" name="curso" class="form-control @error('curso') is-invalid @enderror"
                                       value="{{ old('curso', $horario->curso) }}" required>
                                @error('curso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Início</label>
                                <input type="time" name="inicio" class="form-control @error('inicio') is-invalid @enderror"
                                       value="{{ old('inicio', substr($horario->inicio, 0, 5)) }}" required>
                                @error('inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fim</label>
                                <input type="time" name="fim" class="form-control @error('fim') is-invalid @enderror"
                                       value="{{ old('fim', substr($horario->fim, 0, 5)) }}" required>
                                @error('fim') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.semestres.horarios.index', $semestre) }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Voltar
                            </a>
                            <button type="submit" class="btn text-white" style="background-color:#1a3369;">
                                <i class="bi bi-check-lg me-1"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
