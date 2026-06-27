// Paleta de cores por tema
var _TEMAS = {
    azul: {
        header:    '#1a2f4e',
        bannerBg:  '#1a2f4e',
        banner:    '#FF1744',
        newsBg:    '#0f1e30',
        card:      '#1a2f4e',
        thead:     '#d1dce8',
        theadTxt:  '#1a2f4e',
        odd:       '#ebebeb',
        even:      '#d8d8d8',
        cellTxt:   '#2c3e50',
        accent:    '#4d7fa8',
        accent2:   '#7eb8d4',
    },
    verde: {
        header:    '#1a3d2a',
        bannerBg:  '#1a3d2a',
        banner:    '#e6b800',
        newsBg:    '#0f2218',
        card:      '#1a3d2a',
        thead:     '#c8e6d0',
        theadTxt:  '#1a3d2a',
        odd:       '#ebebeb',
        even:      '#d8d8d8',
        cellTxt:   '#1a3d2a',
        accent:    '#4d9960',
        accent2:   '#7eb894',
    },
    vermelho: {
        header:    '#6b0000',
        bannerBg:  '#6b0000',
        banner:    '#ff6f00',
        newsBg:    '#3b0000',
        card:      '#6b0000',
        thead:     '#f5c6c6',
        theadTxt:  '#6b0000',
        odd:       '#ebebeb',
        even:      '#d8d8d8',
        cellTxt:   '#3b0000',
        accent:    '#c0392b',
        accent2:   '#e07070',
    },
    escuro: {
        header:    '#1e1e1e',
        bannerBg:  '#2d2d2d',
        banner:    '#6f42c1',
        newsBg:    '#121212',
        card:      '#2d2d2d',
        thead:     '#3d3d3d',
        theadTxt:  '#e0e0e0',
        odd:       '#2a2a2a',
        even:      '#242424',
        cellTxt:   '#e0e0e0',
        accent:    '#9d7dcc',
        accent2:   '#b59ae0',
    },
    roxo: {
        header:    '#3d1a78',
        bannerBg:  '#3d1a78',
        banner:    '#e91e63',
        newsBg:    '#200d3f',
        card:      '#3d1a78',
        thead:     '#e2d9f3',
        theadTxt:  '#3d1a78',
        odd:       '#ebebeb',
        even:      '#d8d8d8',
        cellTxt:   '#3d1a78',
        accent:    '#9d7dcc',
        accent2:   '#c9a8f0',
    },
};

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
window._configReady = fetch('/api/config?_=' + Date.now())
    .then(function (r) { return r.json(); })
    .then(function (cfg) {
        window.KIOSK_CONFIG = cfg;

        var header = document.getElementById('tipoInformacao');
        if (header) header.textContent = cfg.departamento || '';

        var muted = document.querySelector('.muted-text');
        if (muted) muted.textContent = cfg.texto_banner || '';

        _applyTema(cfg.tema || 'azul');

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
