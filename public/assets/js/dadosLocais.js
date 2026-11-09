// Guarda a última resposta de cada dado do monitor. Sem rede, entrega a cópia
// ajustada ao dia e à hora atuais, para o painel continuar correto.
(function (raiz) {
    var DIAS = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
    var RECURSOS = ['config', 'horarios', 'informes', 'noticias', 'editais', 'clima'];
    var VALIDADE_CLIMA_MS = 3 * 60 * 60 * 1000;
    var TEMPO_LIMITE_MS = 8000;
    var DIA_MS = 24 * 60 * 60 * 1000;
    var NAO_ENCONTRADO = new Error('recurso não encontrado');

    function doisDigitos(n) {
        return String(n).padStart(2, '0');
    }

    function dataLocal(d) {
        return d.getFullYear() + '-' + doisDigitos(d.getMonth() + 1) + '-' + doisDigitos(d.getDate());
    }

    function meiaNoite(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    }

    function semVencidos(campo) {
        return function (dados, agora) {
            var copia = Object.assign({}, dados);
            copia[campo] = (dados[campo] || []).filter(function (item) {
                return !item.fim || new Date(item.fim) >= agora;
            });
            return copia;
        };
    }

    var AJUSTES = {
        informes: semVencidos('informes'),
        noticias: semVencidos('noticias'),

        horarios: function (dados, agora) {
            if (!dados.semana) return dados;
            var dia = DIAS[agora.getDay()];
            var encerrado = dados.semestre_fim && dataLocal(agora) > dados.semestre_fim;
            return Object.assign({}, dados, { dia: dia, aulas: encerrado ? [] : (dados.semana[dia] || []) });
        },

        editais: function (dados, agora, salvoEm) {
            var passados = Math.round((meiaNoite(agora) - meiaNoite(new Date(salvoEm))) / DIA_MS);
            var editais = (dados.editais || []).map(function (e) {
                if (e.dias_restantes === null || e.dias_restantes === undefined) return e;
                return Object.assign({}, e, { dias_restantes: e.dias_restantes - passados });
            }).filter(function (e) {
                return e.dias_restantes === null || e.dias_restantes === undefined || e.dias_restantes >= 0;
            });
            return Object.assign({}, dados, { editais: editais });
        },

        clima: function (dados, agora, salvoEm) {
            return agora.getTime() - salvoEm > VALIDADE_CLIMA_MS ? null : dados;
        },
    };

    function criar(opcoes) {
        var storage = opcoes.storage;
        var limite = opcoes.tempoLimiteMs || TEMPO_LIMITE_MS;

        function agora() {
            return opcoes.agora ? opcoes.agora() : new Date();
        }

        function chave(recurso) {
            return 'iroyin:' + opcoes.escopo + ':' + recurso;
        }

        function ler(recurso) {
            try {
                return JSON.parse(storage.getItem(chave(recurso)));
            } catch (e) {
                return null;
            }
        }

        function gravar(recurso, dados) {
            try {
                storage.setItem(chave(recurso), JSON.stringify({ salvoEm: agora().getTime(), dados: dados }));
            } catch (e) { /* sem espaço: segue só com a rede */ }
        }

        function apagar(recurso) {
            try {
                storage.removeItem(chave(recurso));
            } catch (e) { /* nada a apagar */ }
        }

        function baixar(recurso) {
            var controle = new AbortController();
            var timer = setTimeout(function () { controle.abort(); }, limite);

            return opcoes.fetch(opcoes.url(recurso), { signal: controle.signal })
                .then(function (r) {
                    if (r.status === 204) {
                        apagar(recurso);
                        return null;
                    }
                    if (r.status === 404) {
                        apagar(recurso);
                        throw NAO_ENCONTRADO;
                    }
                    if (!r.ok) throw new Error('resposta ' + r.status);

                    return r.json().then(function (dados) {
                        if (dados && dados.erro) throw new Error(dados.erro);
                        gravar(recurso, dados);
                        return dados;
                    });
                })
                .finally(function () { clearTimeout(timer); });
        }

        function buscar(recurso) {
            return baixar(recurso).catch(function (erro) {
                var guardado = erro === NAO_ENCONTRADO ? null : ler(recurso);
                if (!guardado) throw erro;

                var ajuste = AJUSTES[recurso];
                return ajuste ? ajuste(guardado.dados, agora(), guardado.salvoEm) : guardado.dados;
            });
        }

        function textoAviso() {
            var salvoEm = RECURSOS.reduce(function (maior, recurso) {
                var guardado = ler(recurso);
                return guardado && guardado.salvoEm > maior ? guardado.salvoEm : maior;
            }, 0);
            if (!salvoEm) return 'Sem conexão';

            var d = new Date(salvoEm);
            var hora = doisDigitos(d.getHours()) + ':' + doisDigitos(d.getMinutes());
            var dia = dataLocal(d) === dataLocal(agora()) ? '' : doisDigitos(d.getDate()) + '/' + doisDigitos(d.getMonth() + 1) + ', ';

            return 'Sem conexão · dados de ' + dia + hora;
        }

        return { buscar: buscar, textoAviso: textoAviso };
    }

    raiz.DadosLocais = { criar: criar };

    if (typeof document !== 'undefined') {
        var armazenamento = null;
        try {
            armazenamento = raiz.localStorage;
        } catch (e) { /* navegador bloqueou o armazenamento */ }

        raiz.Dados = criar({
            fetch: function (url, op) { return fetch(url, op); },
            storage: armazenamento,
            url: function (recurso) { return raiz.kioskApi(recurso); },
            escopo: location.pathname.split('/').filter(Boolean)[0] || '',
        });
    }
})(typeof window !== 'undefined' ? window : globalThis);
