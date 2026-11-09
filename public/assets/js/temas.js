// Paleta de cores por tema. Compartilhada pelo monitor e pelo painel.
window.KIOSK_TEMAS = {
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

// Aplica o tema nas variáveis CSS usadas pelo quadro de informes.
window.aplicarTemaInforme = function (el, nomeTema) {
    var t = window.KIOSK_TEMAS[nomeTema] || window.KIOSK_TEMAS.azul;
    el.style.setProperty('--informe-fundo', t.card);
    el.style.setProperty('--informe-cabecalho', t.header);
    el.style.setProperty('--informe-destaque', t.accent);
    el.style.setProperty('--informe-secundaria', t.accent2);
};
