<x-app-layout>
    <x-slot name="header">Configurações</x-slot>

    <form method="POST" action="{{ route('admin.configuracoes.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- Clima --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color:#1a3369;">
                            <i class="bi bi-cloud-sun me-1"></i> Clima
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cidade</label>
                            <input type="text" name="cidade_clima"
                                   class="form-control @error('cidade_clima') is-invalid @enderror"
                                   value="{{ old('cidade_clima', $cfg->cidade_clima) }}"
                                   placeholder="Ex: Salvador"
                                   required maxlength="100">
                            <div class="form-text">Nome da cidade para buscar o clima no painel.</div>
                            @error('cidade_clima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold">Chave API — OpenWeatherMap</label>
                            <input type="text" name="weather_api_key"
                                   class="form-control font-monospace @error('weather_api_key') is-invalid @enderror"
                                   value="{{ old('weather_api_key', $cfg->weather_api_key) }}"
                                   placeholder="Ex: a1b2c3d4e5f6..."
                                   maxlength="100">
                            <div class="form-text">
                                Chave gratuita em
                                <a href="https://openweathermap.org/api" target="_blank" rel="noopener">openweathermap.org</a>.
                            </div>
                            @error('weather_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ciclo de exibição --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color:#1a3369;">
                            <i class="bi bi-stopwatch me-1"></i> Ciclo de exibição
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Duração dos horários <span class="text-muted fw-normal">(segundos)</span></label>
                            <input type="number" name="duracao_horarios"
                                   class="form-control @error('duracao_horarios') is-invalid @enderror"
                                   value="{{ old('duracao_horarios', $cfg->duracao_horarios) }}"
                                   min="10" max="600" required>
                            <div class="form-text">Tempo que o quadro de horários fica visível entre blocos de notícias. Padrão: 120s.</div>
                            @error('duracao_horarios') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-1">
                            <label class="form-label fw-semibold">Duração de cada notícia <span class="text-muted fw-normal">(segundos)</span></label>
                            <input type="number" name="duracao_noticia"
                                   class="form-control @error('duracao_noticia') is-invalid @enderror"
                                   value="{{ old('duracao_noticia', $cfg->duracao_noticia) }}"
                                   min="5" max="120" required>
                            <div class="form-text">Tempo que cada notícia fica em tela. Padrão: 30s.</div>
                            @error('duracao_noticia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tema visual --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color:#1a3369;">
                            <i class="bi bi-palette me-1"></i> Tema visual
                        </h6>
                        <p class="text-muted small mb-3">Escolha o esquema de cores do painel.</p>

                        <div class="d-flex flex-wrap gap-3">
                            @foreach($temas as $slug => $tema)
                            <label class="tema-opcao" style="cursor:pointer;">
                                <input type="radio" name="tema" value="{{ $slug }}" class="d-none tema-radio"
                                       {{ old('tema', $cfg->tema) === $slug ? 'checked' : '' }}>
                                <div class="tema-card rounded-3 border-2 p-3 d-flex flex-column align-items-center gap-2"
                                     style="width:130px; border: 3px solid transparent; transition: border-color .15s;">
                                    {{-- Prévia de cores --}}
                                    <div class="rounded-2 w-100" style="height:48px; background:{{ $tema['fundo'] }}; border:1px solid #dee2e6; position:relative; overflow:hidden;">
                                        <div style="position:absolute;inset:0;background:{{ $tema['primaria'] }};clip-path:polygon(0 0,60% 0,40% 100%,0 100%);opacity:.9;"></div>
                                        <div style="position:absolute;bottom:6px;right:8px;width:24px;height:6px;background:{{ $tema['primaria'] }};border-radius:3px;opacity:.6;"></div>
                                    </div>
                                    <span class="small fw-semibold" style="color:{{ $tema['primaria'] }}">{{ $tema['label'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('tema') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn text-white px-4" style="background-color:#1a3369;">
                <i class="bi bi-floppy me-1"></i> Salvar configurações
            </button>
        </div>

    </form>

</x-app-layout>

@push('scripts')
<script>
    // Destaca o tema selecionado
    function syncTemas() {
        document.querySelectorAll('.tema-radio').forEach(radio => {
            const card = radio.closest('.tema-opcao').querySelector('.tema-card');
            card.style.borderColor = radio.checked ? '#1a3369' : 'transparent';
            card.style.boxShadow   = radio.checked ? '0 0 0 2px #1a336966' : 'none';
        });
    }
    document.querySelectorAll('.tema-radio').forEach(r => r.addEventListener('change', syncTemas));
    syncTemas();
</script>
@endpush
