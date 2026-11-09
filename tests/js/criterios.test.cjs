const test = require('node:test');
const assert = require('node:assert');
// O projeto usa "type": "module"; o script clássico registra a API em globalThis.
require('../../public/assets/js/criterios.js');
const {
    tempoLeitura, prioridade, montarBloco, selecionarUrgentes, limiteBloco, chaveItem, duracaoItem,
} = globalThis.Criterios;

const palavras = (n) => Array(n).fill('palavra').join(' ');
const informe = (id, extra = {}) => ({ tipo: 'informe', id, titulo: 'Título', texto: 'Texto', urgencia: 'normal', escopo: 'departamento', ...extra });
const noticia = (id) => ({ tipo: 'noticia', id, titulo: 'Notícia' });
const ids = (lista) => lista.map((i) => i.id);

test('tempoLeitura respeita mínimo, regra de 15 palavras a cada 5 s e máximo', () => {
    assert.strictEqual(tempoLeitura(palavras(3)), 10);
    assert.strictEqual(tempoLeitura(palavras(45)), 15);
    assert.strictEqual(tempoLeitura(palavras(46)), 16);
    assert.strictEqual(tempoLeitura(palavras(200)), 30);
});

test('tempoLeitura usa o teto, mas nunca abaixo de 10 s', () => {
    assert.strictEqual(tempoLeitura(palavras(90), 20), 20);
    assert.strictEqual(tempoLeitura(palavras(3), 5), 10);
});

test('tempoLeitura tolera texto vazio', () => {
    assert.strictEqual(tempoLeitura(''), 10);
    assert.strictEqual(tempoLeitura(undefined), 10);
});

test('prioridade segue urgência, departamento, Reitoria e conteúdo comum', () => {
    assert.strictEqual(prioridade(informe(1, { urgencia: 'urgente' })), 0);
    assert.strictEqual(prioridade(informe(1, { urgencia: 'importante' })), 1);
    assert.strictEqual(prioridade(informe(1)), 2);
    assert.strictEqual(prioridade(informe(1, { escopo: 'reitoria' })), 3);
    assert.strictEqual(prioridade(noticia(1)), 4);
    assert.strictEqual(prioridade({ tipo: 'edital', id: 1 }), 4);
});

test('montarBloco põe urgente antes de importante', () => {
    const r = montarBloco([informe(1, { urgencia: 'importante' }), informe(2, { urgencia: 'urgente' })], {}, 120, 30);
    assert.deepStrictEqual(ids(r), [2, 1]);
});

test('montarBloco põe departamento antes da Reitoria', () => {
    const r = montarBloco([informe(1, { escopo: 'reitoria' }), informe(2)], {}, 120, 30);
    assert.deepStrictEqual(ids(r), [2, 1]);
});

test('montarBloco põe primeiro o que apareceu há mais tempo', () => {
    const r = montarBloco([informe(1), informe(2)], { 'informe:1': 2000, 'informe:2': 1000 }, 120, 30);
    assert.deepStrictEqual(ids(r), [2, 1]);
});

test('montarBloco deixa de fora o que não cabe, sem encurtar', () => {
    const r = montarBloco([informe(1), informe(2), informe(3)], {}, 25, 30);
    assert.deepStrictEqual(ids(r), [1, 2]);
    assert.strictEqual(r.reduce((s, i) => s + duracaoItem(i, 30), 0), 20);
});

test('montarBloco sempre inclui ao menos um item, mesmo maior que o limite', () => {
    const r = montarBloco([informe(1, { texto: palavras(200) })], {}, 5, 30);
    assert.deepStrictEqual(ids(r), [1]);
});

test('montarBloco com lista vazia retorna vazio', () => {
    assert.deepStrictEqual(montarBloco([], {}, 120, 30), []);
});

test('montarBloco diferencia itens de tipos diferentes com o mesmo id', () => {
    assert.notStrictEqual(chaveItem(noticia(1)), chaveItem(informe(1)));
});

test('selecionarUrgentes limita a 3 e prioriza o departamento', () => {
    const u = (id, escopo, criado_em) => informe(id, { urgencia: 'urgente', escopo, criado_em });
    const r = selecionarUrgentes([
        u(1, 'reitoria', '2027-01-05T00:00:00Z'),
        u(2, 'departamento', '2027-01-01T00:00:00Z'),
        u(3, 'departamento', '2027-01-03T00:00:00Z'),
        u(4, 'departamento', '2027-01-02T00:00:00Z'),
        u(5, 'reitoria', '2027-01-06T00:00:00Z'),
        informe(6),
    ]);
    assert.deepStrictEqual(ids(r), [3, 4, 2]);
});

test('limiteBloco usa o maior entre horários e quatro notícias', () => {
    assert.strictEqual(limiteBloco({ duracao_horarios: 120, duracao_noticia: 30 }), 120);
    assert.strictEqual(limiteBloco({ duracao_horarios: 60, duracao_noticia: 30 }), 120);
    assert.strictEqual(limiteBloco({ duracao_horarios: 300, duracao_noticia: 30 }), 300);
});
