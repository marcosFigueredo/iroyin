// Gerencia o painel de Editais Abertos no kiosk.
// Expõe: renderEdital(item, duration), fetchEditais(callback)

function fetchEditais(callback) {
    fetch('/api/editais?_=' + Date.now())
        .then(function (r) { return r.json(); })
        .then(function (data) { callback(data.editais || []); })
        .catch(function () { callback([]); });
}

function renderEdital(item, duration) {
    var section = document.getElementById('editais-section');

    // Cabeçalho — sigla e nome da agência com cor de destaque
    var siglaEl = document.getElementById('editais-agencia-sigla');
    siglaEl.textContent = item.agencia_sigla || '';
    siglaEl.style.background = item.agencia_cor || '#1a3369';

    document.getElementById('editais-agencia-nome').textContent = item.agencia_nome || '';

    // Conteúdo
    document.getElementById('editais-titulo').textContent   = item.titulo    || '';
    document.getElementById('editais-objetivo').textContent = item.objetivo  || '';

    // Prazo
    var prazoWrap = document.getElementById('editais-prazo-wrap');
    if (item.data_fechamento) {
        document.getElementById('editais-prazo').textContent = item.data_fechamento;
        prazoWrap.style.display = 'block';
    } else {
        prazoWrap.style.display = 'none';
    }

    // Dias restantes
    var diasWrap = document.getElementById('editais-dias-wrap');
    var diasEl   = document.getElementById('editais-dias');
    if (item.dias_restantes !== null && item.dias_restantes !== undefined) {
        var d = item.dias_restantes;
        if (d < 0) {
            diasEl.textContent = 'ENCERRADO';
            diasEl.style.color = '#e74c3c';
            diasEl.style.fontSize = '1.8rem';
        } else if (d === 0) {
            diasEl.textContent = 'HOJE';
            diasEl.style.color = '#e74c3c';
            diasEl.style.fontSize = '3rem';
        } else {
            diasEl.textContent    = d + ' dias';
            diasEl.style.fontSize = '3rem';
            diasEl.style.color    = d <= 7 ? '#f39c12' : '#7eb8d4';
        }
        diasWrap.style.display = 'block';
    } else {
        diasWrap.style.display = 'none';
    }

    // QR Code
    var qrDiv = document.getElementById('editais-qr');
    qrDiv.innerHTML = '';
    if (item.link) {
        new QRCode(qrDiv, {
            text:       item.link,
            width:      160,
            height:     160,
            colorDark:  '#ffffff',
            colorLight: '#080f18'
        });
    }

    // Barra de progresso
    var fill = document.getElementById('editais-progress-fill');
    fill.style.transition = 'none';
    fill.style.width      = '0%';
    // Force reflow so transition restarts
    void fill.offsetWidth;
    fill.style.transition = 'width ' + (duration / 1000) + 's linear';
    fill.style.width      = '100%';

    section.style.display = 'flex';
}

function hideEditaisSection() {
    document.getElementById('editais-section').style.display = 'none';
    document.getElementById('editais-qr').innerHTML = '';
}
