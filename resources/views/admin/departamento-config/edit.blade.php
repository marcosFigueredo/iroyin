<x-app-layout>
    <x-slot name="header">Aparência e tempos · {{ strtoupper($departamento->slug) }}</x-slot>
    <div class="row justify-content-center"><div class="col-lg-6">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <form method="POST" action="{{ route('admin.departamento-config.update') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label fw-semibold">Tema do monitor</label>
                    <select name="tema" class="form-select @error('tema') is-invalid @enderror">
                        @foreach(\App\Models\Departamento::TEMAS as $tema)
                            <option value="{{ $tema }}" @selected(old('tema', $departamento->tema) === $tema)>{{ ucfirst($tema) }}</option>
                        @endforeach
                    </select>
                    @error('tema')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-3"><label class="form-label fw-semibold">Tempo do quadro de horários (segundos)</label>
                    <input type="number" name="duracao_horarios" min="10" max="600" class="form-control @error('duracao_horarios') is-invalid @enderror" value="{{ old('duracao_horarios', $departamento->duracao_horarios) }}" required>
                    @error('duracao_horarios')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-4"><label class="form-label fw-semibold">Tempo máximo de notícias e editais (segundos)</label>
                    <input type="number" name="duracao_noticia" min="10" max="30" class="form-control @error('duracao_noticia') is-invalid @enderror" value="{{ old('duracao_noticia', $departamento->duracao_noticia) }}" required>
                    @error('duracao_noticia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Cada mensagem fica o tempo necessário para sua leitura, respeitando este limite.</div></div>
                <div class="text-end"><button class="btn text-white" style="background-color:#1a3369;">Salvar</button></div>
            </form>
        </div></div>
    </div></div>
</x-app-layout>
