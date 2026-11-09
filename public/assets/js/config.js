var _TEMAS = window.KIOSK_TEMAS;

function _applyTema(nome) {
    var t = _TEMAS[nome] || _TEMAS['azul'];
    var css = [
        '.news-container{background:' + t.header + ';}',
        '.info-footer{background:' + t.header + ';}',
        '.news-banner{background:' + t.bannerBg + ';}',
        '.banner{background:' + t.banner + ';}',
        '.news-section{background:' + t.newsBg + ';}',
        '.news-card-body{background:' + t.card + ';}',
        '#schedule-table thead{background-color:' + t.thead + ';color:' + t.theadTxt + ';}',
        '#schedule-table tbody tr:nth-child(odd){background-color:' + t.odd + ';}',
        '#schedule-table tbody tr:nth-child(even){background-color:' + t.even + ';}',
        '#schedule-table td{color:' + t.cellTxt + ';}',
        '.news-progress-fill{background:' + t.accent + ';}',
        '.info-footer .info-temp #weather-temp{color:' + t.accent2 + ';}',
        '.info-footer .info-time .hora{color:' + t.accent2 + ';}',
        '.news-data{color:' + t.accent2 + ';}',
        '.news-qr-label{color:' + t.accent2 + ';}',
        '.boa-noite-titulo{color:' + t.accent2 + ';}',
    ].join('');

    var el = document.getElementById('kiosk-tema') || document.createElement('style');
    el.id  = 'kiosk-tema';
    el.textContent = css;
    if (!el.parentNode) document.head.appendChild(el);
}

// Carrega a configuração da instituição antes de inicializar o kiosk.
// Todos os outros scripts aguardam window._configReady antes de iniciar.
window.KIOSK_SLUG = location.pathname.split('/').filter(Boolean)[0] || '';

window.kioskApi = function (recurso) {
    return '/api/' + window.KIOSK_SLUG + '/' + recurso + '?_=' + Date.now();
};

window._configReady = window.Dados.buscar('config')
    .then(function (cfg) {
        window.KIOSK_CONFIG = cfg;

        var header = document.getElementById('tipoInformacao');
        if (header) header.textContent = cfg.cabecalho || cfg.departamento || '';

        var muted = document.querySelector('.muted-text');
        if (muted) muted.textContent = cfg.texto_banner || '';

        _applyTema(cfg.tema || 'azul');

        // QR code fixo de acessibilidade
        if (cfg.url_acessivel && window.QRCode) {
            var qrEl = document.getElementById('acessivel-qr');
            if (qrEl) {
                new QRCode(qrEl, { text: cfg.url_acessivel, width: 78, height: 78 });
            }
        }

        return cfg;
    })
    .catch(function () {
        window.KIOSK_CONFIG = {
            nome:             '',
            sigla:            'IROYIN',
            departamento:     '',
            cidade:           '',
            estado:           '',
            texto_banner:     'NOTÍCIAS',
            duracao_horarios: 120,
            duracao_noticia:  30,
            tema:             'azul',
        };

        var header = document.getElementById('tipoInformacao');
        if (header) header.textContent = window.KIOSK_CONFIG.departamento;

        var muted = document.querySelector('.muted-text');
        if (muted) muted.textContent = window.KIOSK_CONFIG.texto_banner;

        _applyTema('azul');

        return window.KIOSK_CONFIG;
    });
