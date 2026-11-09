<x-app-layout>
    <x-slot name="header">Configurações</x-slot>

    <form method="POST" action="{{ route('admin.configuracoes.update') }}">
        @csrf
        @method('PUT')

        <div class="row"><div class="col-lg-6">
            <div class="card border-0 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1a3369;">
                    <i class="bi bi-cloud-sun me-1"></i> Clima
                </h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Chave API — OpenWeatherMap</label>
                    <input type="text" name="weather_api_key"
                           class="form-control font-monospace @error('weather_api_key') is-invalid @enderror"
                           value="{{ old('weather_api_key', $cfg->weather_api_key) }}" maxlength="100">
                    <div class="form-text">
                        Chave gratuita em
                        <a href="https://openweathermap.org/api" target="_blank" rel="noopener">openweathermap.org</a>.
                        A cidade vem do campus de cada departamento.
                    </div>
                    @error('weather_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="text-end"><button class="btn text-white" style="background-color:#1a3369;">Salvar</button></div>
            </div></div>
        </div></div>
    </form>
</x-app-layout>
