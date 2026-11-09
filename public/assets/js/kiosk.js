let SCHEDULE_DURATION = 2 * 60 * 1000;   // padrão: 2 min (sobrescrito pela config)
let _config = { duracao_horarios: 120, duracao_noticia: 30, tema: 'azul', logo_url: '' };

let _cycleTimer = null;
let _newsTimer  = null;
let _pausado    = false;
let _emHorarios = false;
let _itens      = [];

const CHAVE_ULTIMA_VEZ      = 'iroyin_ultima_vez';
const INTERVALO_URGENTES_MS = 60 * 1000;
const _urgentesVistos = new Set();
const _ultimaVez      = _lerUltimaVez();

// ── Memória do rodízio (sobrevive ao recarregamento a cada 4 h) ───────────────

function _lerUltimaVez() {
    try {
        return JSON.parse(localStorage.getItem(CHAVE_ULTIMA_VEZ)) || {};
    } catch (e) {
        return {};
    }
}

function _registrarExibicao(item) {
    _ultimaVez[Criterios.chaveItem(item)] = Date.now();
    if (item.urgencia === 'urgente') _urgentesVistos.add(item.id);
    try {
        localStorage.setItem(CHAVE_ULTIMA_VEZ, JSON.stringify(_ultimaVez));
    } catch (e) { /* sem armazenamento: o rodízio vale até recarregar */ }
}

// ── Controle de timers ────────────────────────────────────────────────────────

function _limparTimers() {
    clearTimeout(_cycleTimer);
    clearTimeout(_newsTimer);
    _cycleTimer = null;
    _newsTimer  = null;
}

// API mínima usada pelo monitor de conectividade.
function pauseKioskCycle() {
    _pausado = true;
    _emHorarios = false;
    _limparTimers();
}

function resumeKioskCycle() {
    _pausado = false;
    _limparTimers();
    if (window.isBoaNoite) {
        _newsOnlyLoop();
        return;
    }

    _showSchedules();
    _runCycle();
}

window.pauseKioskCycle  = pauseKioskCycle;
window.resumeKioskCycle = resumeKioskCycle;

// ── Visibilidade ─────────────────────────────────────────────────────────────

function _esconderMensagens() {
    document.getElementById('news-section').style.display    = 'none';
    document.getElementById('informe-section').style.display = 'none';
    hideEditaisSection();
}

function _showSchedules() {
    if (window.isBoaNoite) return;
    _esconderMensagens();
    document.getElementById('news-banner').style.display = 'none';
    document.getElementById('horarios').style.display    = 'block';
    _emHorarios = true;
}

function _hideSchedules() {
    _emHorarios = false;
    document.getElementById('horarios').style.display = 'none';
}

// ── Busca de conteúdo ─────────────────────────────────────────────────────────

function _buscar(recurso, campo, tipo) {
    return fetch(window.kioskApi(recurso))
        .then(function (r) { return r.ok ? r.json() : {}; })
        .then(function (data) {
            return (data[campo] || []).map(function (item) {
                return Object.assign({}, item, { tipo: tipo });
            });
        })
        .catch(function () { return []; });
}

function _buscarInformes() {
    return _buscar('informes', 'informes', 'informe');
}

function _buscarTudo() {
    return Promise.all([
        _buscarInformes(),
        _buscar('noticias', 'noticias', 'noticia'),
        new Promise(function (resolve) { fetchEditais(resolve); }),
    ]).then(function (partes) {
        var editais = partes[2].map(function (e) { return Object.assign({}, e, { tipo: 'edital' }); });
        return partes[0].concat(partes[1], editais);
    });
}

// ── Exibição de mensagens ─────────────────────────────────────────────────────

function _mostrarItem(item, duracaoMs) {
    _esconderMensagens();

    if (item.tipo === 'informe') {
        var area = document.getElementById('informe-section');
        renderInforme(area, item, _config.logo_url);
        aplicarTemaInforme(area.firstChild, _config.tema);
        area.style.display = 'block';
    } else if (item.tipo === 'edital') {
        renderEdital(item, duracaoMs);
    } else {
        renderNewsItem(item, duracaoMs);
        document.getElementById('news-section').style.display = 'flex';
    }
}

function _playItems(items, idx, onDone) {
    if (idx >= items.length) {
        _esconderMensagens();
        onDone();
        return;
    }

    var duracaoMs = Criterios.duracaoItem(items[idx], _config.duracao_noticia) * 1000;
    _mostrarItem(items[idx], duracaoMs);
    _registrarExibicao(items[idx]);
    _newsTimer = setTimeout(function () { _playItems(items, idx + 1, onDone); }, duracaoMs);
}

function _showBlock(items, onDone) {
    if (items.length === 0) { onDone(); return; }
    _hideSchedules();
    showNewsBanner(function () {
        hideNewsBanner();
        _playItems(items, 0, onDone);
    });
}

// ── Ciclo normal: horários → banner → mensagens → horários ────────────────────

function _runCycle() {
    if (window.isBoaNoite || _pausado) return;

    _buscarTudo().then(function (itens) {
        if (window.isBoaNoite || _pausado) return;
        _itens = itens;
        _showSchedules();

        _cycleTimer = setTimeout(function () {
            if (window.isBoaNoite) return;
            var bloco = Criterios.montarBloco(_itens, _ultimaVez, Criterios.limiteBloco(_config), _config.duracao_noticia);
            _showBlock(bloco, _runCycle);
        }, SCHEDULE_DURATION);
    });
}

// ── Urgentes: interrompem o quadro de horários ────────────────────────────────

function verificarUrgentes() {
    return _buscarInformes().then(function (informes) {
        if (!_emHorarios || _pausado || window.isBoaNoite) return;

        var novos = informes.filter(function (i) {
            return i.urgencia === 'urgente' && !_urgentesVistos.has(i.id);
        });
        if (novos.length === 0) return;

        _limparTimers();
        _hideSchedules();
        _playItems(Criterios.selecionarUrgentes(novos), 0, _runCycle);
    });
}

window.verificarUrgentes = verificarUrgentes;

// ── Modo sem aulas: loop contínuo de mensagens ────────────────────────────────

function _newsOnlyLoop() {
    if (!window.isBoaNoite || _pausado) return;

    _buscarTudo().then(function (itens) {
        if (!window.isBoaNoite || _pausado) return;

        if (itens.length === 0) {
            document.getElementById('boa-noite').style.display = 'flex';
            _cycleTimer = setTimeout(_newsOnlyLoop, 60 * 1000);
            return;
        }

        document.getElementById('boa-noite').style.display = 'none';
        var bloco = Criterios.montarBloco(itens, _ultimaVez, Infinity, _config.duracao_noticia);
        showNewsBanner(function () {
            hideNewsBanner();
            _playItems(bloco, 0, _newsOnlyLoop);
        });
    });
}

// ── Eventos de Boa Noite / retomada ──────────────────────────────────────────

window.addEventListener('kioskBoaNoite', function () {
    _limparTimers();
    _emHorarios = false;
    _esconderMensagens();
    document.getElementById('news-banner').style.display = 'none';
    _newsOnlyLoop();
});

window.addEventListener('kioskResumeMode', function () {
    _limparTimers();
    _esconderMensagens();
    _showSchedules();
    _runCycle();
});

// ── Inicialização — aguarda config antes de começar ───────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    window._configReady.then(function (cfg) {
        _config = Object.assign(_config, cfg);
        if (cfg.duracao_horarios) SCHEDULE_DURATION = cfg.duracao_horarios * 1000;

        window.isBoaNoite = false;
        _showSchedules();
        _runCycle();
        setInterval(verificarUrgentes, INTERVALO_URGENTES_MS);
    });
});
