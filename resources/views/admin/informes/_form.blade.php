<link rel="stylesheet" href="/assets/css/informe.css">

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            @if($podeImagem)
            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Tipo de informe</label>
                <div class="btn-group w-100">
                    <input type="radio" class="btn-check" name="formato" id="formato-texto" value="texto" @checked(old('formato', $informe->formato) === 'texto')>
                    <label class="btn btn-outline-secondary" for="formato-texto">Logo + texto</label>
                    <input type="radio" class="btn-check" name="formato" id="formato-imagem" value="imagem" @checked(old('formato', $informe->formato) === 'imagem')>
                    <label class="btn btn-outline-secondary" for="formato-imagem">Com imagem</label>
                </div>
                @error('formato')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            @else
            <input type="hidden" name="formato" value="texto">
            @endif

            <div class="mb-3">
                <label for="titulo" class="form-label fw-semibold">Título</label>
                <input type="text" id="titulo" name="titulo" maxlength="70" required
                       class="form-control @error('titulo') is-invalid @enderror"
                       value="{{ old('titulo', $informe->titulo) }}">
                <div class="form-text text-end" id="contador-titulo"></div>
                @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="texto" class="form-label fw-semibold">Texto</label>
                <textarea id="texto" name="texto" maxlength="160" rows="3"
                          class="form-control @error('texto') is-invalid @enderror">{{ old('texto', $informe->texto) }}</textarea>
                <div class="form-text text-end" id="contador-texto"></div>
                @error('texto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if($podeImagem)
            <div class="mb-3" id="campo-imagem">
                <label for="imagem" class="form-label fw-semibold">Imagem</label>
                <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp"
                       class="form-control @error('imagem') is-invalid @enderror">
                <div class="form-text">JPG, PNG ou WebP, até 4 MB.</div>
                @error('imagem')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endif

            <div class="mb-3">
                <label for="urgencia" class="form-label fw-semibold">Urgência</label>
                <select id="urgencia" name="urgencia" class="form-select @error('urgencia') is-invalid @enderror">
                    <option value="normal" @selected(old('urgencia', $informe->urgencia) === 'normal')>Normal</option>
                    <option value="importante" @selected(old('urgencia', $informe->urgencia) === 'importante')>Importante</option>
                    <option value="urgente" @selected(old('urgencia', $informe->urgencia) === 'urgente')>Urgente (até 7 dias)</option>
                </select>
                @error('urgencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label for="inicio" class="form-label fw-semibold">Início</label>
                    <input type="date" id="inicio" name="inicio" required
                           class="form-control @error('inicio') is-invalid @enderror"
                           value="{{ old('inicio', $informe->inicio?->toDateString() ?? now()->toDateString()) }}">
                    @error('inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="fim" class="form-label fw-semibold">Fim</label>
                    <input type="date" id="fim" name="fim" required
                           class="form-control @error('fim') is-invalid @enderror"
                           value="{{ old('fim', $informe->fim?->toDateString() ?? now()->addDays(7)->toDateString()) }}">
                    @error('fim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.informes.index') }}" class="btn btn-outline-secondary">Voltar</a>
                <button class="btn text-white" style="background-color:#1a3369;">Publicar</button>
            </div>
        </div></div>
    </div>

    <div class="col-lg-7">
        <div class="informe-previa" id="previa">
            <div class="informe-previa-topo">{{ $cabecalho }}</div>
            <div class="informe-previa-tela" id="previa-tela"></div>
            <div class="informe-previa-base"></div>
        </div>
        <p class="text-muted small mt-2">Assim o informe vai aparecer no monitor.</p>
    </div>
</div>

@push('scripts')
<script src="/assets/js/temas.js"></script>
<script src="/assets/js/informeRender.js"></script>
<script>
(function () {
    var imagemAtual = @json($informe->imagem);
    var logoUrl = @json($logoUrl);
    var origem = @json($origem);
    var tela = document.getElementById('previa-tela');
    var titulo = document.getElementById('titulo');
    var texto = document.getElementById('texto');
    var urgencia = document.getElementById('urgencia');
    var arquivo = document.getElementById('imagem');
    var campoImagem = document.getElementById('campo-imagem');
    var imagemUrl = null;

    window.aplicarTemaInforme(document.getElementById('previa'), @json($tema));

    function formato() {
        var marcado = document.querySelector('input[name="formato"]:checked');
        return marcado ? marcado.value : 'texto';
    }

    function contar(campo, id) {
        document.getElementById(id).textContent = campo.value.length + ' / ' + campo.maxLength;
    }

    function desenhar() {
        if (campoImagem) campoImagem.hidden = formato() !== 'imagem';
        contar(titulo, 'contador-titulo');
        contar(texto, 'contador-texto');
        window.renderInforme(tela, {
            formato: formato(),
            titulo: titulo.value,
            texto: texto.value,
            urgencia: urgencia.value,
            origem: origem,
            imagem: imagemAtual,
            imagemUrl: imagemUrl,
        }, logoUrl);
        window.aplicarTemaInforme(tela.firstChild, @json($tema));
    }

    [titulo, texto].forEach(function (c) { c.addEventListener('input', desenhar); });
    urgencia.addEventListener('change', desenhar);
    document.querySelectorAll('input[name="formato"]').forEach(function (r) { r.addEventListener('change', desenhar); });
    if (arquivo) {
        arquivo.addEventListener('change', function () {
            if (imagemUrl) URL.revokeObjectURL(imagemUrl);
            imagemUrl = arquivo.files[0] ? URL.createObjectURL(arquivo.files[0]) : null;
            desenhar();
        });
    }

    desenhar();
})();
</script>
@endpush
