// Cores por fonte — adicione novas fontes aqui
const FONTE_CORES = {
    'CAPES':     '#6f42c1',
    'CNPq':      '#0d6efd',
    'SBC':       '#c62828',
    'IMPA':      '#2e7d32',
    'IEEE':      '#0277bd',
    'FAPESB':    '#e65100',
    'SECTI/BA':  '#00695c',
    'MEC':       '#1565c0',
    'MCTI':      '#283593',
    'ABES':      '#558b2f',
    'SBEM':      '#ad1457',
    'SEC/BA':    '#4527a0',
};

function _corFonte(fonte) {
    return FONTE_CORES[fonte] || '#4d7fa8';
}

function _formatarData(iso) {
    if (!iso) return '';
    try {
        const d = new Date(iso);
        return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' });
    } catch { return ''; }
}

/**
 * Renderiza um item de notícia no #news-section.
 * @param {Object} item  — objeto do noticias.json
 * @param {number} duration — duração em ms da barra de progresso
 */
function renderNewsItem(item, duration) {
    const img     = document.getElementById('news-img');
    const noImg   = document.getElementById('news-no-img');
    const fonte   = document.getElementById('news-fonte');
    const titulo  = document.getElementById('news-titulo');
    const data    = document.getElementById('news-data');
    const fill    = document.getElementById('news-progress-fill');

    // Imagem
    if (item.imagem) {
        img.src = '/' + item.imagem;
        img.style.display = 'block';
        noImg.style.display = 'none';
    } else {
        img.src = '';
        img.style.display = 'none';
        noImg.style.display = 'flex';
    }

    // Texto
    fonte.textContent         = item.fonte;
    fonte.style.background    = _corFonte(item.fonte);
    titulo.textContent        = item.titulo;
    data.textContent          = _formatarData(item.inicio);

    // QR Code
    const qrWrap = document.getElementById('news-qr-wrap');
    const qrDiv  = document.getElementById('news-qr');
    qrDiv.innerHTML = '';
    if (item.link) {
        qrWrap.style.display = 'block';
        new QRCode(qrDiv, {
            text:       item.link,
            width:      160,
            height:     160,
            colorDark:  '#ffffff',
            colorLight: 'transparent',
        });
    } else {
        qrWrap.style.display = 'none';
    }

    // Barra de progresso — reset e anima
    fill.style.transition = 'none';
    fill.style.width = '0%';
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            fill.style.transition = `width ${duration}ms linear`;
            fill.style.width = '100%';
        });
    });
}
