<x-app-layout>
    <x-slot name="header">Notícias</x-slot>

    <div class="row g-4">
        {{-- COLUNA ESQUERDA: Busca de Feeds --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold" style="color:#1a3369;">
                    <i class="bi bi-rss me-1"></i> Buscar nos Feeds
                </div>
                <div class="card-body">
                    {{-- Seletor de feed --}}
                    <div class="mb-3">
                        <label class="form-label small text-muted">Fonte</label>
                        <select id="feedSelect" class="form-select form-select-sm">
                            @foreach($feeds as $f)
                                <option value="{{ $f['url'] }}">{{ $f['nome'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button id="btnBuscar" class="btn btn-sm btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i> Buscar feed
                        </button>
                        <button id="btnBuscarTodos" class="btn btn-sm btn-success flex-fill">
                            <i class="bi bi-broadcast me-1"></i> Buscar Todos
                        </button>
                    </div>

                    {{-- Indicadores de loading --}}
                    <div id="feedLoading" class="text-center text-muted py-3" style="display:none;">
                        <div class="spinner-border spinner-border-sm"></div> Buscando…
                    </div>
                    <div id="feedErro" class="alert alert-warning mt-3 py-2 small" style="display:none;"></div>

                    <div id="feedResultados" class="mt-3" style="display:none;">
                        <p class="small text-muted mb-1" id="feedResultadosTitulo">Clique numa notícia para importar:</p>
                        <div id="feedLegenda" class="mb-2 small" style="display:none;">
                            <span class="badge" style="background:#198754;">Com imagem</span>
                            <span class="badge ms-1" style="background:#0d6efd;">Sem imagem</span>
                        </div>
                        <div class="list-group list-group-flush" id="feedLista"></div>
                    </div>
                </div>
            </div>

            {{-- Gerenciar Feeds (apenas admins) --}}
            @if(auth()->user()->isAdmin())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center" style="color:#1a3369;">
                    <span class="fw-bold"><i class="bi bi-rss-fill me-1"></i> Gerenciar Feeds</span>
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseFeeds">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse" id="collapseFeeds">
                    <div class="card-body p-0">
                        {{-- Lista de feeds --}}
                        <ul class="list-group list-group-flush">
                            @foreach($feedsGerenciar as $feed)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <div style="min-width:0;">
                                    <div class="small fw-semibold text-truncate">{{ $feed->nome }}</div>
                                    <div class="text-muted text-truncate" style="font-size:0.7rem;">{{ $feed->url }}</div>
                                </div>
                                <div class="d-flex gap-1 ms-2 flex-shrink-0">
                                    <form method="POST" action="{{ route('admin.feeds.toggle', $feed) }}">
                                        @csrf
                                        <button class="btn btn-sm {{ $feed->ativo ? 'btn-success' : 'btn-outline-secondary' }}"
                                                title="{{ $feed->ativo ? 'Desativar' : 'Ativar' }}">
                                            <i class="bi bi-{{ $feed->ativo ? 'eye' : 'eye-slash' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.feeds.destroy', $feed) }}"
                                          onsubmit="return confirm('Remover feed {{ addslashes($feed->nome) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Remover">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        {{-- Formulário novo feed --}}
                        <div class="p-3 border-top">
                            <p class="small fw-semibold mb-2" style="color:#1a3369;">Adicionar feed</p>
                            <form method="POST" action="{{ route('admin.feeds.store') }}">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="nome" class="form-control form-control-sm"
                                           placeholder="Nome do feed" required maxlength="100">
                                </div>
                                <div class="mb-2">
                                    <input type="url" name="url" class="form-control form-control-sm"
                                           placeholder="URL do feed RSS" required maxlength="500">
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-plus me-1"></i> Adicionar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Formulário de notícia manual --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-bold" style="color:#1a3369;">
                    <i class="bi bi-pencil-square me-1"></i> Cadastrar Manualmente
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.noticias.create') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-plus-circle me-1"></i> Nova Notícia
                    </a>
                </div>
            </div>
        </div>

        {{-- COLUNA DIREITA: Lista de notícias --}}
        <div class="col-lg-7">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center" style="color:#1a3369;">
                    <span><i class="bi bi-newspaper me-1"></i> Notícias cadastradas</span>
                    <span class="badge bg-secondary" id="contadorNoticias">{{ $noticias->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Fonte</th>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyNoticias">
                                @if($noticias->isEmpty())
                                <tr id="trVazio">
                                    <td colspan="6" class="text-muted text-center py-4">Nenhuma notícia cadastrada.</td>
                                </tr>
                                @else
                                @foreach($noticias as $n)
                                <tr>
                                    <td>
                                        {{ Str::limit($n->titulo, 50) }}
                                        @if($n->link)
                                            <a href="{{ $n->link }}" target="_blank" class="ms-1 text-muted">
                                                <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $n->fonte }}</span></td>
                                    <td>{{ $n->inicio->format('d/m H:i') }}</td>
                                    <td>{{ $n->fim->format('d/m H:i') }}</td>
                                    <td>
                                        @if($n->status === 'ativa')
                                            <span class="badge bg-success">Ativa</span>
                                        @elseif($n->status === 'futura')
                                            <span class="badge bg-info text-dark">Futura</span>
                                        @else
                                            <span class="badge bg-secondary">Expirada</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.noticias.edit', $n) }}"
                                           class="btn btn-sm btn-outline-secondary py-0 px-2">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.noticias.destroy', $n) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Remover esta notícia?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast de sucesso --}}
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100;">
        <div id="toastSucesso" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-semibold" id="toastMsg">Notícia salva!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    {{-- Modal de importação do feed --}}
    <div class="modal fade" id="modalImportar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importar Notícia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formImportar" action="{{ route('admin.noticias.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Título *</label>
                            <input type="text" name="titulo" id="importTitulo" class="form-control" required maxlength="255">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Link</label>
                                <input type="url" name="link" id="importLink" class="form-control" maxlength="500">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fonte *</label>
                                <input type="text" name="fonte" id="importFonte" class="form-control" required maxlength="80">
                            </div>
                        </div>

                        {{-- Imagem vinda do feed (preview) --}}
                        <div id="importImgFeedWrap" class="mb-3" style="display:none;">
                            <label class="form-label fw-semibold">Imagem do feed</label>
                            <div>
                                <img id="importImgFeed" src="" alt=""
                                     style="max-height:130px; border-radius:6px; border:1px solid #dee2e6;">
                            </div>
                            <input type="hidden" name="imagem_url" id="importImagemUrl">
                            <div class="form-text">Esta imagem será salva automaticamente. Envie outra para substituir.</div>
                        </div>

                        {{-- Upload manual --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" id="importImgLabel">Imagem (opcional)</label>
                            <div class="input-group">
                                <label class="btn btn-outline-secondary mb-0" for="importImagemFile">Arquivo</label>
                                <input type="file" id="importImagemFile" name="imagem" class="d-none" accept="image/*">
                                <span class="form-control text-muted small d-flex align-items-center"
                                      id="importImagemNome">Escolha uma imagem para a notícia</span>
                            </div>
                        </div>

                        {{-- QR Code preview --}}
                        <div id="qrPreviewWrap" class="mb-3" style="display:none;">
                            <label class="form-label fw-semibold">QR Code do link</label>
                            <div id="qrPreview"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Início *</label>
                                <input type="datetime-local" name="inicio" id="importInicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fim *</label>
                                <input type="date" name="fim" id="importFim" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Salvar Notícia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    const feedSelect       = document.getElementById('feedSelect');
    const btnBuscar        = document.getElementById('btnBuscar');
    const btnBuscarTodos   = document.getElementById('btnBuscarTodos');
    const feedLoading      = document.getElementById('feedLoading');
    const feedErro         = document.getElementById('feedErro');
    const feedResultados   = document.getElementById('feedResultados');
    const feedLista        = document.getElementById('feedLista');
    const feedLegenda      = document.getElementById('feedLegenda');
    const feedResultadosTitulo = document.getElementById('feedResultadosTitulo');
    const modalEl          = document.getElementById('modalImportar');
    const modal            = new bootstrap.Modal(modalEl);
    const toast            = new bootstrap.Toast(document.getElementById('toastSucesso'), { delay: 3000 });
    const CSRF             = '{{ csrf_token() }}';
    const ADMIN_NOTICIAS   = '{{ url("admin/noticias") }}';

    let _btnAtivo = null; // botão da lista que abriu o modal

    // ── Datas padrão: agora → +7 dias ─────────────────────────────────────────
    function defaultDates() {
        const now = new Date();
        const fim = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
        document.getElementById('importInicio').value = now.toISOString().slice(0, 16);   // datetime-local
        document.getElementById('importFim').value    = fim.toISOString().slice(0, 10);   // date (YYYY-MM-DD)
    }

    // ── Nome do arquivo selecionado ────────────────────────────────────────────
    document.getElementById('importImagemFile').addEventListener('change', function () {
        document.getElementById('importImagemNome').textContent =
            this.files[0] ? this.files[0].name : 'Escolha uma imagem para a notícia';
    });

    // ── QR code preview ao digitar/colar o link ────────────────────────────────
    document.getElementById('importLink').addEventListener('input', function () {
        const wrap = document.getElementById('qrPreviewWrap');
        const div  = document.getElementById('qrPreview');
        div.innerHTML = '';
        if (this.value.trim()) {
            wrap.style.display = 'block';
            new QRCode(div, { text: this.value.trim(), width: 128, height: 128,
                              colorDark: '#1a2f4e', colorLight: '#ffffff' });
        } else {
            wrap.style.display = 'none';
        }
    });

    // ── Helpers de UI ──────────────────────────────────────────────────────────
    function _mostrarLoading() {
        feedLoading.style.display    = 'block';
        feedErro.style.display       = 'none';
        feedResultados.style.display = 'none';
        feedLista.innerHTML          = '';
        feedLegenda.style.display    = 'none';
    }
    function _esconderLoading() {
        feedLoading.style.display = 'none';
    }
    function _mostrarErro(msg) {
        feedErro.textContent    = msg;
        feedErro.style.display  = 'block';
    }

    function _popularLista(items, mostrarLegenda) {
        if (!items || items.length === 0) {
            _mostrarErro('Nenhum artigo encontrado.');
            return;
        }
        items.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            if (mostrarLegenda) {
                btn.className = 'list-group-item list-group-item-action py-2 border-start border-3';
                btn.style.borderColor = item.tem_imagem ? '#198754' : '#0d6efd';
                btn.style.background  = item.tem_imagem ? '#f0fff4' : '#f0f4ff';
            } else {
                btn.className = 'list-group-item list-group-item-action py-2';
            }
            const icone = item.tem_imagem ? '🖼️ ' : '';
            btn.innerHTML = `<div class="fw-semibold small">${icone}${item.titulo}</div>
                             <div class="text-muted" style="font-size:.75rem;">${item.fonte} · ${item.data}</div>`;
            btn.addEventListener('click', () => abrirModal(item, btn));
            feedLista.appendChild(btn);
        });
        if (mostrarLegenda) feedLegenda.style.display = 'block';
        feedResultados.style.display = 'block';
    }

    // ── Buscar feed único ──────────────────────────────────────────────────────
    btnBuscar.addEventListener('click', function () {
        _mostrarLoading();
        feedResultadosTitulo.textContent = 'Clique numa notícia para importar:';

        fetch("{{ route('admin.noticias.buscar-feed') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ url: feedSelect.value })
        })
        .then(r => r.json())
        .then(data => {
            _esconderLoading();
            if (data.erro) { _mostrarErro(data.erro); return; }
            _popularLista(data.items, false);
        })
        .catch(() => { _esconderLoading(); _mostrarErro('Erro ao contactar o servidor.'); });
    });

    // ── Buscar Todos ───────────────────────────────────────────────────────────
    btnBuscarTodos.addEventListener('click', function () {
        _mostrarLoading();
        feedResultadosTitulo.textContent = 'Notícias dos últimos 7 dias — clique para importar:';

        fetch("{{ route('admin.noticias.buscar-todos') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            _esconderLoading();
            if (data.erro) { _mostrarErro(data.erro); return; }
            _popularLista(data.items, true);
        })
        .catch(() => { _esconderLoading(); _mostrarErro('Erro ao contactar o servidor.'); });
    });

    // ── Submit AJAX do modal ───────────────────────────────────────────────────
    document.getElementById('formImportar').addEventListener('submit', function (e) {
        e.preventDefault();
        const btnSalvar = this.querySelector('[type=submit]');
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando…';

        fetch(this.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-save me-1"></i> Salvar Notícia';
            if (data.success) {
                modal.hide();
                if (_btnAtivo) { _btnAtivo.remove(); _btnAtivo = null; }
                adicionarNovaNoticia(data.noticia);
                document.getElementById('toastMsg').textContent = 'Notícia salva com sucesso!';
                toast.show();
            }
        })
        .catch(() => {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-save me-1"></i> Salvar Notícia';
        });
    });

    // ── Inserir nova linha na tabela ───────────────────────────────────────────
    function adicionarNovaNoticia(n) {
        const trVazio = document.getElementById('trVazio');
        if (trVazio) trVazio.remove();

        const badge = n.status === 'ativa'
            ? '<span class="badge bg-success">Ativa</span>'
            : '<span class="badge bg-info text-dark">Futura</span>';

        const linkIcon = n.link
            ? ` <a href="${n.link}" target="_blank" class="ms-1 text-muted"><i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i></a>`
            : '';

        const titulo = n.titulo.length > 50 ? n.titulo.substring(0, 50) + '…' : n.titulo;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${titulo}${linkIcon}</td>
            <td><span class="badge bg-secondary">${n.fonte}</span></td>
            <td>${n.inicio}</td>
            <td>${n.fim}</td>
            <td>${badge}</td>
            <td class="text-end text-nowrap">
                <a href="${ADMIN_NOTICIAS}/${n.id}/edit" class="btn btn-sm btn-outline-secondary py-0 px-2">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="${ADMIN_NOTICIAS}/${n.id}" method="POST" class="d-inline"
                      onsubmit="return confirm('Remover esta notícia?')">
                    <input type="hidden" name="_token" value="${CSRF}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                </form>
            </td>`;

        document.getElementById('tbodyNoticias').prepend(tr);

        const contador = document.getElementById('contadorNoticias');
        contador.textContent = parseInt(contador.textContent || '0') + 1;
    }

    // ── Abrir modal com dados do item ──────────────────────────────────────────
    function abrirModal(item, btnEl) {
        _btnAtivo = btnEl || null;
        document.getElementById('importTitulo').value = item.titulo;
        document.getElementById('importLink').value   = item.link || '';
        document.getElementById('importFonte').value  = item.fonte || feedSelect.options[feedSelect.selectedIndex].text;

        // Imagem do feed
        const imgWrap  = document.getElementById('importImgFeedWrap');
        const imgEl    = document.getElementById('importImgFeed');
        const imgUrlEl = document.getElementById('importImagemUrl');
        const imgLabel = document.getElementById('importImgLabel');

        if (item.imagem_url) {
            imgEl.src           = item.imagem_url;
            imgUrlEl.value      = item.imagem_url;
            imgWrap.style.display = 'block';
            imgLabel.textContent  = 'Substituir imagem (opcional)';
        } else {
            imgEl.src           = '';
            imgUrlEl.value      = '';
            imgWrap.style.display = 'none';
            imgLabel.textContent  = 'Imagem (opcional)';
        }

        // Reset file input
        document.getElementById('importImagemFile').value = '';
        document.getElementById('importImagemNome').textContent = 'Escolha uma imagem para a notícia';

        // QR code
        const wrap = document.getElementById('qrPreviewWrap');
        const div  = document.getElementById('qrPreview');
        div.innerHTML = '';
        if (item.link) {
            wrap.style.display = 'block';
            new QRCode(div, { text: item.link, width: 128, height: 128,
                              colorDark: '#1a2f4e', colorLight: '#ffffff' });
        } else {
            wrap.style.display = 'none';
        }

        defaultDates();
        modal.show();
    }
    </script>
    @endpush
</x-app-layout>
