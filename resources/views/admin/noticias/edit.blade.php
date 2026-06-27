<x-app-layout>
    <x-slot name="header">Editar Notícia</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.noticias.update', $noticia) }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Título *</label>
                            <input type="text" name="titulo" value="{{ old('titulo', $noticia->titulo) }}"
                                   class="form-control @error('titulo') is-invalid @enderror"
                                   required maxlength="255">
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Link (URL da notícia)</label>
                                <input type="url" name="link" id="linkInput"
                                       value="{{ old('link', $noticia->link) }}"
                                       class="form-control @error('link') is-invalid @enderror"
                                       maxlength="500" placeholder="https://...">
                                @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fonte *</label>
                                <input type="text" name="fonte" value="{{ old('fonte', $noticia->fonte) }}"
                                       class="form-control @error('fonte') is-invalid @enderror"
                                       required maxlength="80">
                                @error('fonte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- QR Code preview --}}
                        <div id="qrWrap" class="mb-3" style="{{ $noticia->link ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold">QR Code do link</label>
                            <div id="qrDiv" class="p-2 d-inline-block bg-white border rounded"></div>
                            <div class="form-text">Será exibido no painel ao lado do texto.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Imagem</label>
                            @if($noticia->imagem)
                                <div class="mb-2">
                                    <img src="/{{ $noticia->imagem }}" alt="" style="max-height:100px;border-radius:4px;">
                                    <div class="form-text">Envie uma nova imagem para substituir.</div>
                                </div>
                            @endif
                            <div class="input-group">
                                <label class="btn btn-outline-secondary mb-0" for="imagemFile">Arquivo</label>
                                <input type="file" id="imagemFile" name="imagem"
                                       class="d-none @error('imagem') is-invalid @enderror"
                                       accept="image/*">
                                <span class="form-control text-muted small d-flex align-items-center"
                                      id="imagemNome">Escolha uma imagem para a notícia</span>
                            </div>
                            @error('imagem')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Início *</label>
                                <input type="datetime-local" name="inicio"
                                       value="{{ old('inicio', $noticia->inicio->format('Y-m-d\TH:i')) }}"
                                       class="form-control @error('inicio') is-invalid @enderror" required>
                                @error('inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fim *</label>
                                <input type="datetime-local" name="fim"
                                       value="{{ old('fim', $noticia->fim->format('Y-m-d\TH:i')) }}"
                                       class="form-control @error('fim') is-invalid @enderror" required>
                                @error('fim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Atualizar
                            </button>
                            <a href="{{ route('admin.noticias.index') }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    const linkInput = document.getElementById('linkInput');
    const qrWrap    = document.getElementById('qrWrap');
    const qrDiv     = document.getElementById('qrDiv');

    function gerarQr(url) {
        qrDiv.innerHTML = '';
        if (url.trim()) {
            qrWrap.style.display = 'block';
            new QRCode(qrDiv, { text: url.trim(), width: 160, height: 160,
                                colorDark: '#1a2f4e', colorLight: '#ffffff' });
        } else {
            qrWrap.style.display = 'none';
        }
    }

    linkInput.addEventListener('input', () => gerarQr(linkInput.value));
    gerarQr(linkInput.value); // gera ao carregar se já tiver link

    document.getElementById('imagemFile').addEventListener('change', function () {
        document.getElementById('imagemNome').textContent =
            this.files[0] ? this.files[0].name : 'Escolha uma imagem para a notícia';
    });
    </script>
    @endpush
</x-app-layout>
