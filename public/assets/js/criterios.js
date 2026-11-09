// Critérios de exibição: o que entra em cada volta do monitor e por quanto tempo.
// Só contém funções puras, testadas em tests/js/criterios.test.cjs.
(function (raiz) {
    var MINIMO = 10;
    var MAXIMO = 30;
    var MAX_URGENTES = 3;

    function tempoLeitura(texto, tetoS) {
        var teto = tetoS === undefined ? MAXIMO : tetoS;
        var palavras = String(texto || '').trim().split(/\s+/).filter(Boolean).length;
        var limite = Math.max(MINIMO, Math.min(MAXIMO, teto));
        return Math.max(MINIMO, Math.min(limite, Math.ceil(palavras / 3)));
    }

    function chaveItem(item) {
        return item.tipo + ':' + item.id;
    }

    function textoItem(item) {
        return [item.titulo, item.texto, item.objetivo].filter(Boolean).join(' ');
    }

    // Informes usam o máximo geral; notícias e editais obedecem ao teto do departamento.
    function duracaoItem(item, tetoNoticiaS) {
        return item.tipo === 'informe'
            ? tempoLeitura(textoItem(item))
            : tempoLeitura(textoItem(item), tetoNoticiaS);
    }

    function prioridade(item) {
        if (item.tipo !== 'informe') return 4;
        if (item.urgencia === 'urgente') return 0;
        if (item.urgencia === 'importante') return 1;
        return item.escopo === 'reitoria' ? 3 : 2;
    }

    function montarBloco(itens, ultimaVez, limiteS, tetoNoticiaS) {
        var ordenados = itens.slice().sort(function (a, b) {
            var p = prioridade(a) - prioridade(b);
            if (p !== 0) return p;
            return (ultimaVez[chaveItem(a)] || 0) - (ultimaVez[chaveItem(b)] || 0);
        });

        var bloco = [];
        var total = 0;
        ordenados.forEach(function (item) {
            var duracao = duracaoItem(item, tetoNoticiaS);
            if (bloco.length === 0 || total + duracao <= limiteS) {
                bloco.push(item);
                total += duracao;
            }
        });
        return bloco;
    }

    function selecionarUrgentes(itens) {
        return itens
            .filter(function (i) { return i.urgencia === 'urgente'; })
            .sort(function (a, b) {
                var escopo = (a.escopo === 'reitoria' ? 1 : 0) - (b.escopo === 'reitoria' ? 1 : 0);
                if (escopo !== 0) return escopo;
                return String(b.criado_em || '').localeCompare(String(a.criado_em || ''));
            })
            .slice(0, MAX_URGENTES);
    }

    function limiteBloco(cfg) {
        return Math.max(cfg.duracao_horarios, 4 * cfg.duracao_noticia);
    }

    var api = {
        tempoLeitura: tempoLeitura,
        chaveItem: chaveItem,
        duracaoItem: duracaoItem,
        prioridade: prioridade,
        montarBloco: montarBloco,
        selecionarUrgentes: selecionarUrgentes,
        limiteBloco: limiteBloco,
    };

    raiz.Criterios = api;
})(typeof window !== 'undefined' ? window : globalThis);
