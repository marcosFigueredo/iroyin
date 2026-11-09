import { readFileSync } from 'node:fs';
import { expect, test } from '@playwright/test';

const monitorHtml = readFileSync('public/monitor.html', 'utf8');

const aula = {
    disciplina: 'Teste de Software',
    professor: 'Prof. E2',
    curso: 'Computação',
    inicio: '09:00:00',
    fim: '12:20:00',
    sala: 'LAB 01',
};

const noticia = {
    titulo: 'Notícia usada pelo teste controlado',
    fonte: 'E2',
    link: 'https://example.test/noticia',
    imagem: null,
    inicio: '2026-07-20T09:00:00-03:00',
    fim: '2026-07-21T09:00:00-03:00',
};

const edital = {
    id: 1,
    agencia_nome: 'Agência de Teste',
    agencia_sigla: 'E2',
    agencia_cor: '#1a3369',
    titulo: 'Edital usado pelo teste controlado',
    objetivo: 'Validar a ausência isolada de conteúdo.',
    link: 'https://example.test/edital',
    data_fechamento: '31/07/2026',
    dias_restantes: 11,
};

function state(overrides = {}) {
    return {
        config: {
            nome: 'Instituição E2',
            sigla: 'E2',
            departamento: 'Teste de falhas controladas',
            cidade: 'Salvador',
            estado: 'BA',
            texto_banner: 'NOTÍCIAS',
            duracao_horarios: 0.15,
            duracao_noticia: 1,
            tema: 'azul',
            url_acessivel: '',
        },
        aulas: [aula],
        noticias: [noticia],
        editais: [edital],
        informes: [],
        clima: {
            temp: 27,
            cidade: 'Salvador',
            icone: '01d',
            descricao: 'céu limpo',
            umidade: 70,
            vento: 2,
        },
        ...overrides,
    };
}

async function prepareKiosk(page, scenario, counters = {}) {
    await page.addInitScript(() => {
        const NativeDate = Date;
        const instant = new NativeDate('2026-07-20T12:00:00-03:00').getTime();

        class FixedDate extends NativeDate {
            constructor(...args) {
                super(...(args.length ? args : [instant]));
            }

            static now() {
                return instant;
            }
        }

        window.Date = FixedDate;
    });

    await page.route('**/*', async (route) => {
        const request = route.request();
        const url = new URL(request.url());

        if (url.origin === 'http://127.0.0.1:4173') {
            // O banco do teste é vazio: o monitor é servido direto do arquivo.
            if (url.pathname === '/dcet2') {
                if (scenario.monitorIndisponivel) {
                    return route.fulfill({ status: 500, contentType: 'text/html', body: 'erro' });
                }
                return route.fulfill({ contentType: 'text/html', body: monitorHtml });
            }
            if (url.pathname === '/api/dcet2/informes') {
                return scenario.departamentoInativo
                    ? route.fulfill({ status: 404, json: { message: 'Not found' } })
                    : route.fulfill({ json: { informes: scenario.informes } });
            }
            if (scenario.departamentoInativo && url.pathname.startsWith('/api/dcet2/')) {
                return route.fulfill({ status: 404, json: { message: 'Not found' } });
            }
            if (url.pathname === '/api/dcet2/config') {
                counters.config = (counters.config || 0) + 1;
                return route.fulfill({ json: scenario.config });
            }
            if (url.pathname === '/api/dcet2/horarios') {
                counters.horarios = (counters.horarios || 0) + 1;
                return route.fulfill({ json: { aulas: scenario.aulas } });
            }
            if (url.pathname === '/api/dcet2/noticias') {
                counters.noticias = (counters.noticias || 0) + 1;
                if (scenario.atrasoNoticias) {
                    await new Promise((resolve) => setTimeout(resolve, scenario.atrasoNoticias));
                }
                return route.fulfill({ json: { noticias: scenario.noticias } });
            }
            if (url.pathname === '/api/dcet2/editais') {
                counters.editais = (counters.editais || 0) + 1;
                return route.fulfill({ json: { editais: scenario.editais } });
            }
            if (url.pathname === '/api/dcet2/clima') {
                counters.clima = (counters.clima || 0) + 1;
                return scenario.clima === null
                    ? route.fulfill({ status: 204, body: '' })
                    : route.fulfill({ json: scenario.clima });
            }
            if (url.pathname === '/ping.txt') {
                counters.ping = (counters.ping || 0) + 1;
                return route.fulfill({ status: 200, body: 'ok' });
            }

            return route.continue();
        }

        if (scenario.semCdn && (url.pathname.endsWith('/anime.min.js') || url.pathname.endsWith('/qrcode.min.js'))) {
            return route.fulfill({ contentType: 'application/javascript', body: '' });
        }

        if (url.pathname.endsWith('/anime.min.js')) {
            return route.fulfill({
                contentType: 'application/javascript',
                body: `window.anime={
                    set:function(){},
                    stagger:function(){return 0;},
                    timeline:function(options){
                        var timeline={add:function(){return timeline;}};
                        setTimeout(function(){if(options.complete)options.complete();},0);
                        return timeline;
                    }
                };`,
            });
        }

        if (url.pathname.endsWith('/qrcode.min.js')) {
            return route.fulfill({
                contentType: 'application/javascript',
                body: 'window.QRCode=function(element){element.dataset.qrcode="rendered";};',
            });
        }

        return route.fulfill({
            status: 200,
            contentType: request.resourceType() === 'script'
                ? 'application/javascript'
                : 'text/css',
            body: '',
        });
    });
}

test('tudo vazio mantém uma tela informativa, sem clima e sem área em branco', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));

    await prepareKiosk(page, state({
        aulas: [],
        noticias: [],
        editais: [],
        clima: null,
    }));
    await page.goto('/dcet2');

    await expect(page.getByText('Não há aulas no momento.')).toBeVisible();
    await expect(page.locator('.info-weather')).toBeHidden();
    await expect(page.locator('#horarios')).toBeVisible();
    await expect(page.locator('#news-section')).toBeHidden();
    await expect(page.locator('#editais-section')).toBeHidden();
    await expect(page.locator('#offline-overlay')).toBeHidden();

    await page.waitForTimeout(700);
    await expect(page.getByText('Não há aulas no momento.')).toBeVisible();
    expect(errors).toEqual([]);
});

test('sem notícias, aulas e editais continuam sendo exibidos', async ({ page }) => {
    await prepareKiosk(page, state({ noticias: [] }));
    await page.goto('/dcet2');

    await expect(page.getByText('Teste de Software')).toBeVisible();
    await expect(page.getByText(edital.titulo)).toBeVisible();
    await expect(page.locator('#news-section')).toBeHidden();
});

test('sem editais, aulas e notícias continuam sendo exibidas', async ({ page }) => {
    await prepareKiosk(page, state({ editais: [] }));
    await page.goto('/dcet2');

    await expect(page.getByText('Teste de Software')).toBeVisible();
    await expect(page.getByText(noticia.titulo)).toBeVisible();
    await expect(page.locator('#editais-section')).toBeHidden();
});

test('sem aulas, a mensagem vazia aparece e o ciclo de conteúdo continua', async ({ page }) => {
    const scenario = state({ aulas: [] });
    scenario.config.duracao_horarios = 1;
    await prepareKiosk(page, scenario);
    await page.goto('/dcet2');

    await expect(page.getByText('Não há aulas no momento.')).toBeVisible();
    await expect(page.getByText(noticia.titulo)).toBeVisible({ timeout: 3_000 });
});

test('perda e retorno da rede exibem o overlay e retomam o ciclo', async ({ context, page }) => {
    const counters = {};
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));

    const scenario = state();
    scenario.config.duracao_horarios = 5;
    await prepareKiosk(page, scenario, counters);
    await page.goto('/dcet2');
    await expect(page.getByText('Teste de Software')).toBeVisible();

    await context.setOffline(true);
    await expect(page.locator('#offline-overlay')).toBeVisible();
    await expect(page.getByText('Estamos sem conexão')).toBeVisible();

    const requestsBeforeRecovery = counters.noticias || 0;
    await context.setOffline(false);

    await expect(page.locator('#offline-overlay')).toBeHidden();
    await expect(page.locator('#horarios')).toBeVisible();
    await expect.poll(() => counters.noticias || 0).toBeGreaterThan(requestsBeforeRecovery);
    expect(errors).toEqual([]);
});

const urgente = {
    id: 7,
    formato: 'texto',
    titulo: 'Aula de hoje cancelada',
    texto: 'Sem aula no período da noite.',
    imagem: null,
    urgencia: 'urgente',
    origem: 'Secretaria · DCET2',
    escopo: 'departamento',
    criado_em: '2026-07-20T11:00:00Z',
};

test('urgente interrompe os horários e pulsa', async ({ page }) => {
    const scenario = state();
    scenario.config.duracao_horarios = 30;
    await prepareKiosk(page, scenario);
    await page.goto('/dcet2');
    await expect(page.getByText('Teste de Software')).toBeVisible();

    scenario.informes = [urgente];
    await page.evaluate(() => window.verificarUrgentes());

    const tela = page.locator('#informe-section .informe-tela.urgente');
    await expect(tela).toBeVisible();
    await expect(page.locator('#horarios')).toBeHidden();
    await expect(tela.getByText('Aula de hoje cancelada')).toBeVisible();

    const animacao = await tela.evaluate((el) => getComputedStyle(el, '::after').animationName);
    expect(animacao).toBe('informe-pulsa');
});

test('título com HTML aparece como texto no monitor', async ({ page }) => {
    const scenario = state();
    scenario.config.duracao_horarios = 30;
    await prepareKiosk(page, scenario);
    await page.goto('/dcet2');
    await expect(page.getByText('Teste de Software')).toBeVisible();

    scenario.informes = [{ ...urgente, titulo: '<img src=x onerror="window.invadido=1">' }];
    await page.evaluate(() => window.verificarUrgentes());

    await expect(page.locator('.informe-titulo')).toHaveText('<img src=x onerror="window.invadido=1">');
    expect(await page.evaluate(() => window.invadido)).toBeUndefined();
});

test('departamento desativado mantém a tela com fallback', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));

    await prepareKiosk(page, state({ departamentoInativo: true }));
    await page.goto('/dcet2');

    await expect(page.locator('#horarios')).toBeVisible();
    await expect(page.locator('#offline-overlay')).toBeHidden();
    await page.waitForTimeout(500);
    expect(errors).toEqual([]);
});

test('sem mensagens os horários continuam', async ({ page }) => {
    await prepareKiosk(page, state({ noticias: [], editais: [], informes: [] }));
    await page.goto('/dcet2');

    await expect(page.getByText('Teste de Software')).toBeVisible();
    await page.waitForTimeout(700);
    await expect(page.locator('#horarios')).toBeVisible();
    await expect(page.locator('#news-banner')).toBeHidden();
});

test('HTML digitado em horários aparece como texto', async ({ page }) => {
    const carga = '<img src=x onerror="window.invadido=1">';
    await prepareKiosk(page, state({
        aulas: [{ ...aula, disciplina: carga, professor: carga, curso: carga, sala: carga }],
        noticias: [],
        editais: [],
    }));
    await page.goto('/dcet2');

    await expect(page.locator('#table-horario td').first()).toHaveText(carga);
    expect(await page.evaluate(() => window.invadido)).toBeUndefined();
});

test('queda de rede com busca pendente não duplica o ciclo', async ({ page }) => {
    const scenario = state({ editais: [], atrasoNoticias: 500 });
    scenario.config.duracao_horarios = 1;
    await prepareKiosk(page, scenario);
    await page.goto('/dcet2');
    await expect(page.getByText('Teste de Software')).toBeVisible();

    await page.evaluate(() => {
        window.chamadas = 0;
        const original = window.renderNewsItem;
        window.renderNewsItem = function (...args) {
            window.chamadas += 1;
            return original.apply(this, args);
        };
        window.pauseKioskCycle();
        window.resumeKioskCycle();
    });

    await page.waitForTimeout(3000);
    expect(await page.evaluate(() => window.chamadas)).toBe(1);
});

test('sem as bibliotecas externas o ciclo continua', async ({ page }) => {
    const scenario = state({ editais: [], semCdn: true });
    await prepareKiosk(page, scenario);
    await page.goto('/dcet2');

    await expect(page.locator('#tipoInformacao')).toHaveText(scenario.config.departamento);
    await expect(page.getByText(noticia.titulo)).toBeVisible();
});

test('não recarrega quando o servidor responde com erro', async ({ page }) => {
    const scenario = state({ noticias: [], editais: [] });
    await prepareKiosk(page, scenario);
    await page.goto('/dcet2');
    await expect(page.getByText('Teste de Software')).toBeVisible();

    await page.evaluate(() => { window.mesmaPagina = true; });
    scenario.monitorIndisponivel = true;
    await page.evaluate(() => window.recarregarSeDisponivel());
    await page.waitForTimeout(300);

    expect(await page.evaluate(() => window.mesmaPagina)).toBe(true);
});
