const test = require('node:test');
const assert = require('node:assert');
const fs = require('node:fs');
const path = require('node:path');
require('../../public/assets/js/dadosLocais.js');
require('../../public/assets/js/cacheMonitor.js');

const { criar } = globalThis.DadosLocais;
const publico = path.join(__dirname, '..', '..', 'public');

const SEGUNDA = new Date(2027, 4, 10, 10, 0);
const TERCA = new Date(2027, 4, 11, 10, 0);

function memoria() {
    const m = new Map();
    return {
        getItem: (k) => (m.has(k) ? m.get(k) : null),
        setItem: (k, v) => m.set(k, v),
        removeItem: (k) => m.delete(k),
    };
}

function resposta(status, corpo) {
    return { status, ok: status >= 200 && status < 300, json: async () => corpo };
}

// Monta um cliente cuja rede e relógio o teste controla.
function cliente(inicial = SEGUNDA) {
    const c = { rede: null, hoje: inicial, storage: memoria() };
    c.dados = criar({
        fetch: (url, op) => c.rede(url, op),
        storage: c.storage,
        url: (r) => '/api/dcet2/' + r,
        escopo: 'dcet2',
        agora: () => c.hoje,
        tempoLimiteMs: 20,
    });
    c.online = (corpo, status = 200) => { c.rede = async () => resposta(status, corpo); };
    c.offline = () => { c.rede = async () => { throw new TypeError('sem rede'); }; };
    return c;
}

test('com rede devolve a resposta e guarda a cópia', async () => {
    const c = cliente();
    c.online({ aulas: [1] });
    assert.deepStrictEqual(await c.dados.buscar('config'), { aulas: [1] });

    c.offline();
    assert.deepStrictEqual(await c.dados.buscar('config'), { aulas: [1] });
});

test('sem rede e sem cópia a busca falha', async () => {
    const c = cliente();
    c.offline();
    await assert.rejects(c.dados.buscar('config'));
});

test('erro do servidor usa a cópia guardada', async () => {
    const c = cliente();
    c.online({ a: 1 });
    await c.dados.buscar('config');

    c.online({}, 500);
    assert.deepStrictEqual(await c.dados.buscar('config'), { a: 1 });
});

test('departamento removido (404) não usa a cópia', async () => {
    const c = cliente();
    c.online({ a: 1 });
    await c.dados.buscar('config');

    c.online({}, 404);
    await assert.rejects(c.dados.buscar('config'));
});

test('rede travada desiste no tempo limite e usa a cópia', async () => {
    const c = cliente();
    c.online({ a: 1 });
    await c.dados.buscar('config');

    c.rede = (url, op) => new Promise((_, rejeita) => {
        op.signal.addEventListener('abort', () => rejeita(new Error('abortado')));
    });
    assert.deepStrictEqual(await c.dados.buscar('config'), { a: 1 });
});

test('cópias de departamentos diferentes não se misturam', async () => {
    const storage = memoria();
    const de = (escopo, rede) => criar({ fetch: rede, storage, url: (r) => r, escopo, agora: () => SEGUNDA });
    await de('dcet1', async () => resposta(200, { nome: 'um' })).buscar('config');

    await assert.rejects(de('dcet2', async () => { throw new Error('off'); }).buscar('config'));
});

test('armazenamento cheio não derruba a busca', async () => {
    const dados = criar({
        fetch: async () => resposta(200, { a: 1 }),
        storage: { getItem: () => null, setItem: () => { throw new Error('cheio'); }, removeItem: () => {} },
        url: (r) => r,
        escopo: 'x',
    });
    assert.deepStrictEqual(await dados.buscar('config'), { a: 1 });
});

test('offline no dia seguinte mostra as aulas do dia certo', async () => {
    const c = cliente(SEGUNDA);
    c.online({
        aulas: [{ disciplina: 'Cálculo' }],
        semana: { segunda: [{ disciplina: 'Cálculo' }], terca: [{ disciplina: 'Redes' }] },
        semestre_fim: '2027-07-30',
    });
    await c.dados.buscar('horarios');

    c.offline();
    c.hoje = TERCA;
    assert.deepStrictEqual((await c.dados.buscar('horarios')).aulas, [{ disciplina: 'Redes' }]);
});

test('offline depois do fim do semestre não mostra aulas', async () => {
    const c = cliente(SEGUNDA);
    c.online({ aulas: [{}], semana: { terca: [{}] }, semestre_fim: '2027-05-10' });
    await c.dados.buscar('horarios');

    c.offline();
    c.hoje = TERCA;
    assert.deepStrictEqual((await c.dados.buscar('horarios')).aulas, []);
});

for (const [recurso, campo] of [['informes', 'informes'], ['noticias', 'noticias']]) {
    test(`offline ${recurso} vencidos saem da tela`, async () => {
        const c = cliente(SEGUNDA);
        c.online({ [campo]: [
            { id: 1, fim: new Date(2027, 4, 10, 18, 0).toISOString() },
            { id: 2, fim: new Date(2027, 4, 12, 18, 0).toISOString() },
        ] });
        await c.dados.buscar(recurso);

        c.offline();
        c.hoje = TERCA;
        assert.deepStrictEqual((await c.dados.buscar(recurso))[campo].map((i) => i.id), [2]);
    });
}

test('offline editais descontam os dias e os encerrados saem', async () => {
    const c = cliente(SEGUNDA);
    c.online({ editais: [{ id: 1, dias_restantes: 0 }, { id: 2, dias_restantes: 5 }, { id: 3, dias_restantes: null }] });
    await c.dados.buscar('editais');

    c.offline();
    c.hoje = TERCA;
    assert.deepStrictEqual((await c.dados.buscar('editais')).editais, [
        { id: 2, dias_restantes: 4 },
        { id: 3, dias_restantes: null },
    ]);
});

test('clima guardado vale por 3 horas e depois some', async () => {
    const c = cliente(SEGUNDA);
    c.online({ temp: 27 });
    await c.dados.buscar('clima');

    c.offline();
    c.hoje = new Date(2027, 4, 10, 12, 59);
    assert.deepStrictEqual(await c.dados.buscar('clima'), { temp: 27 });

    c.hoje = new Date(2027, 4, 10, 13, 1);
    assert.strictEqual(await c.dados.buscar('clima'), null);
});

test('clima não configurado (204) devolve nulo e apaga a cópia', async () => {
    const c = cliente();
    c.online({ temp: 27 });
    await c.dados.buscar('clima');

    c.online(null, 204);
    assert.strictEqual(await c.dados.buscar('clima'), null);

    c.offline();
    await assert.rejects(c.dados.buscar('clima'));
});

test('resposta com erro não substitui a cópia boa', async () => {
    const c = cliente();
    c.online({ temp: 27 });
    await c.dados.buscar('clima');

    c.online({ erro: 'falha' });
    assert.deepStrictEqual(await c.dados.buscar('clima'), { temp: 27 });
});

test('aviso informa o horário dos dados guardados', async () => {
    const c = cliente(new Date(2027, 4, 10, 14, 32));
    assert.strictEqual(c.dados.textoAviso(), 'Sem conexão');

    c.online({ a: 1 });
    await c.dados.buscar('config');
    assert.strictEqual(c.dados.textoAviso(), 'Sem conexão · dados de 14:32');

    c.hoje = new Date(2027, 4, 11, 8, 0);
    assert.strictEqual(c.dados.textoAviso(), 'Sem conexão · dados de 10/05, 14:32');
});

// ── O monitor precisa abrir sem depender de nada fora do servidor ─────────────

const html = fs.readFileSync(path.join(publico, 'monitor.html'), 'utf8');

test('monitor não carrega nada de servidores externos', () => {
    assert.doesNotMatch(html, /(src|href)="https?:/);
    for (const css of ['style.css', 'informe.css']) {
        assert.doesNotMatch(fs.readFileSync(path.join(publico, 'assets/css', css), 'utf8'), /@import\s+url\(["']?https?:/);
    }
});

test('todo arquivo local do monitor está na lista do cache', () => {
    const usados = [...html.matchAll(/(?:src|href)="(\/assets\/[^"]+)"/g)].map((m) => m[1]);
    assert.ok(usados.length > 10);
    for (const arquivo of usados) {
        assert.ok(globalThis.ArquivosMonitor.includes(arquivo), arquivo + ' fora da lista');
    }
});

test('todo arquivo da lista do cache existe', () => {
    for (const arquivo of globalThis.ArquivosMonitor) {
        assert.ok(fs.existsSync(path.join(publico, arquivo)), arquivo + ' não existe');
    }
});
