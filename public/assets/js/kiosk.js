let SCHEDULE_DURATION  = 2 * 60 * 1000;  // padrão: 2 min (sobrescrito pela config)
let NEWS_ITEM_DURATION = 30 * 1000;       // padrão: 30s  (sobrescrito pela config)

let _cycleTimer  = null;
let _newsTimer   = null;
let _newsItems   = [];
let _editaisItems = [];
let _currentHalf = 0;   // 0 = primeira metade, 1 = segunda metade

// ── Visibilidade ─────────────────────────────────────────────────────────────

function _showSchedules() {
    if (window.isBoaNoite) return;
    document.getElementById('horarios').style.display     = 'block';
    document.getElementById('news-section').style.display = 'none';
    document.getElementById('news-banner').style.display  = 'none';
    hideEditaisSection();
}

function _hideSchedules() {
    document.getElementById('horarios').style.display = 'none';
}

// ── Fetch ─────────────────────────────────────────────────────────────────────

function _fetchNoticias(callback) {
    fetch('/api/noticias?_=' + Date.now())
        .then(function (r) { return r.json(); })
        .then(function (data) { callback(data.noticias || []); })
        .catch(function () { callback([]); });
}

// ── Divide itens em duas metades ──────────────────────────────────────────────

function _getHalf(items, half) {
    if (!items || items.length === 0) return [];
    var mid = Math.ceil(items.length / 2);
    return half === 0 ? items.slice(0, mid) : items.slice(mid);
}

// ── Toca uma sequência de notícias e chama onDone ao terminar ─────────────────

function _playItems(items, idx, onDone) {
    if (idx >= items.length) {
        document.getElementById('news-section').style.display = 'none';
        onDone();
        return;
    }
    renderNewsItem(items[idx], NEWS_ITEM_DURATION);
    _newsTimer = setTimeout(function () { _playItems(items, idx + 1, onDone); }, NEWS_ITEM_DURATION);
}

// ── Exibe bloco de notícias (banner + sequência) ──────────────────────────────

function _showBlock(items, onDone) {
    if (!items || items.length === 0) { onDone(); return; }
    _hideSchedules();
    showNewsBanner(function () {
        hideNewsBanner();
        document.getElementById('news-section').style.display = 'flex';
        _playItems(items, 0, onDone);
    });
}

// ── Exibe bloco de editais (um edital por vez) ────────────────────────────────

function _playEditais(items, idx, onDone) {
    if (idx >= items.length) {
        hideEditaisSection();
        onDone();
        return;
    }
    renderEdital(items[idx], NEWS_ITEM_DURATION);
    _newsTimer = setTimeout(function () { _playEditais(items, idx + 1, onDone); }, NEWS_ITEM_DURATION);
}

function _showEditaisBlock(onDone) {
    if (!_editaisItems || _editaisItems.length === 0) { onDone(); return; }
    _hideSchedules();
    _playEditais(_editaisItems, 0, onDone);
}

// ── Ciclo normal: horários → metade A → editais → horários → metade B ─────────

function _runCycle() {
    if (window.isBoaNoite) return;

    if (_currentHalf === 0) {
        // Refresca notícias e editais a cada ciclo completo
        _fetchNoticias(function (noticias) {
            _newsItems = noticias;
            fetchEditais(function (editais) {
                _editaisItems = editais;
                _scheduleStep();
            });
        });
    } else {
        _scheduleStep();
    }
}

function _scheduleStep() {
    if (window.isBoaNoite) return;

    var halfItems = _getHalf(_newsItems, _currentHalf);

    if (halfItems.length === 0 && _editaisItems.length === 0) {
        _currentHalf = _currentHalf === 0 ? 1 : 0;
        _cycleTimer  = setTimeout(_runCycle, 500);
        return;
    }

    _showSchedules();

    _cycleTimer = setTimeout(function () {
        if (window.isBoaNoite) return;

        _showBlock(halfItems, function () {
            // Após notícias → editais (se existirem) → próximo meio-ciclo
            _showEditaisBlock(function () {
                _currentHalf = _currentHalf === 0 ? 1 : 0;
                _runCycle();
            });
        });
    }, SCHEDULE_DURATION);
}

// ── Modo sem aulas: loop contínuo de notícias + editais ───────────────────────

function _newsOnlyLoop() {
    if (!window.isBoaNoite) return;

    _fetchNoticias(function (noticias) {
        fetchEditais(function (editais) {
            _editaisItems = editais;

            if ((!noticias || noticias.length === 0) && editais.length === 0) {
                document.getElementById('boa-noite').style.display = 'flex';
                _cycleTimer = setTimeout(_newsOnlyLoop, 60 * 1000);
                return;
            }

            document.getElementById('boa-noite').style.display = 'none';

            if (noticias && noticias.length > 0) {
                showNewsBanner(function () {
                    hideNewsBanner();
                    document.getElementById('news-section').style.display = 'flex';
                    _playItems(noticias, 0, function () {
                        _showEditaisBlock(function () {
                            _newsOnlyLoop();
                        });
                    });
                });
            } else {
                _showEditaisBlock(function () {
                    _newsOnlyLoop();
                });
            }
        });
    });
}

// ── Eventos de Boa Noite / retomada ──────────────────────────────────────────

window.addEventListener('kioskBoaNoite', function () {
    clearTimeout(_cycleTimer);
    clearTimeout(_newsTimer);
    document.getElementById('news-section').style.display = 'none';
    document.getElementById('news-banner').style.display  = 'none';
    hideEditaisSection();
    _newsOnlyLoop();
});

window.addEventListener('kioskResumeMode', function () {
    clearTimeout(_cycleTimer);
    clearTimeout(_newsTimer);
    document.getElementById('news-section').style.display = 'none';
    hideEditaisSection();
    _currentHalf = 0;
    _showSchedules();
    _runCycle();
});

// ── Inicialização — aguarda config antes de começar ───────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    window._configReady.then(function (cfg) {
        if (cfg.duracao_horarios) SCHEDULE_DURATION  = cfg.duracao_horarios * 1000;
        if (cfg.duracao_noticia)  NEWS_ITEM_DURATION = cfg.duracao_noticia  * 1000;

        window.isBoaNoite = false;
        _showSchedules();
        _runCycle();
    });
});
