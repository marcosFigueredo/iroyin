// Monta o quadro de um informe. Usado pelo monitor e pela pré-visualização,
// para que o que a secretaria vê seja o que sai na tela.
// Todo texto entra por textContent: conteúdo digitado nunca vira HTML.
(function () {
    var SELOS = { normal: 'INFORME', importante: 'IMPORTANTE', urgente: 'URGENTE' };

    function el(tag, classe, texto) {
        var n = document.createElement(tag);
        n.className = classe;
        if (texto !== undefined && texto !== null) n.textContent = texto;
        return n;
    }

    function fonteImagem(item) {
        if (item.imagemUrl) return item.imagemUrl;
        return item.imagem ? '/' + item.imagem : '';
    }

    window.renderInforme = function (container, item, logoUrl) {
        var urgencia = SELOS[item.urgencia] ? item.urgencia : 'normal';
        var tela = el('div', 'informe-tela ' + urgencia);

        if (item.formato === 'imagem') {
            var areaImagem = el('div', 'informe-imagem');
            var src = fonteImagem(item);
            if (src) {
                var img = el('img', '');
                img.alt = '';
                img.src = src;
                areaImagem.appendChild(img);
            } else {
                areaImagem.textContent = 'Escolha uma imagem';
            }
            tela.appendChild(areaImagem);
        } else {
            var areaLogo = el('div', 'informe-logo');
            if (logoUrl) {
                var logo = el('img', '');
                logo.alt = '';
                logo.src = logoUrl;
                areaLogo.appendChild(logo);
            }
            tela.appendChild(areaLogo);
        }

        var corpo = el('div', 'informe-corpo');
        corpo.appendChild(el('span', 'informe-selo', SELOS[urgencia]));
        corpo.appendChild(el('div', 'informe-titulo', item.titulo));
        if (item.texto) corpo.appendChild(el('div', 'informe-texto', item.texto));
        corpo.appendChild(el('div', 'informe-origem', item.origem));
        tela.appendChild(corpo);

        container.replaceChildren(tela);
    };
})();
