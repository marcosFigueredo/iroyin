<x-app-layout>
    <x-slot name="header">Instituição</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <p class="text-muted mb-4">
                        Esses dados identificam a instituição e são exibidos no painel.
                    </p>

                    <form method="POST" action="{{ route('admin.instituicao.update') }}"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome da instituição</label>
                            <input type="text" name="nome"
                                   class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $inst->nome) }}"
                                   placeholder="Ex: Universidade do Estado da Bahia"
                                   required maxlength="255">
                            <div class="form-text">Nome completo da universidade ou instituição.</div>
                            @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sigla</label>
                            <input type="text" name="sigla"
                                   class="form-control @error('sigla') is-invalid @enderror"
                                   value="{{ old('sigla', $inst->sigla) }}"
                                   placeholder="Ex: USP, UFBA, IFBA"
                                   required maxlength="20">
                            <div class="form-text">Abreviação da instituição.</div>
                            @error('sigla') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Departamento / Unidade</label>
                            <input type="text" name="departamento"
                                   class="form-control @error('departamento') is-invalid @enderror"
                                   value="{{ old('departamento', $inst->departamento) }}"
                                   placeholder="Ex: DEPARTAMENTO DE COMPUTAÇÃO"
                                   required maxlength="255">
                            <div class="form-text">Exibido como título na tela de aulas do painel.</div>
                            @error('departamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-7">
                                <label class="form-label fw-semibold">Cidade</label>
                                <input type="text" name="cidade"
                                       class="form-control @error('cidade') is-invalid @enderror"
                                       value="{{ old('cidade', $inst->cidade) }}"
                                       placeholder="Ex: Salvador"
                                       required maxlength="100">
                                @error('cidade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fw-semibold">Estado</label>
                                <input type="text" name="estado"
                                       class="form-control @error('estado') is-invalid @enderror"
                                       value="{{ old('estado', $inst->estado) }}"
                                       placeholder="Ex: Bahia"
                                       required maxlength="100">
                                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Texto do banner de notícias</label>
                            <input type="text" name="texto_banner"
                                   class="form-control @error('texto_banner') is-invalid @enderror"
                                   value="{{ old('texto_banner', $inst->texto_banner) }}"
                                   placeholder="Ex: NOTÍCIAS"
                                   required maxlength="100">
                            <div class="form-text">Exibido no banner animado entre horários e notícias.</div>
                            @error('texto_banner') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Logo da instituição <span class="text-muted fw-normal">(opcional)</span></label>

                            {{-- Preview atual --}}
                            @if($inst->logo_url)
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img id="logo-preview" src="{{ asset($inst->logo_url) }}" alt="Logo atual"
                                     style="max-height:72px;max-width:200px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:6px;background:#fff;">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remover_logo" value="1" id="remover_logo">
                                        <label class="form-check-label text-danger small" for="remover_logo">Remover logo</label>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="mb-2">
                                <img id="logo-preview" src="" alt=""
                                     style="display:none;max-height:72px;max-width:200px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:6px;background:#fff;">
                            </div>
                            @endif

                            <input type="file" name="logo" id="logo-input"
                                   class="form-control @error('logo') is-invalid @enderror"
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml">
                            <div class="form-text">PNG, JPG, WEBP ou SVG. Máx 2 MB. Exibido no admin e na tela de login.</div>
                            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn text-white px-4" style="background-color:#1a3369;">
                                <i class="bi bi-floppy me-1"></i> Salvar
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.getElementById('logo-input').addEventListener('change', function () {
        var file    = this.files[0];
        var preview = document.getElementById('logo-preview');
        if (!file || !preview) return;
        var reader  = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush

</x-app-layout>
