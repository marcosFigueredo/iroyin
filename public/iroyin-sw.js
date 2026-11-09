// Cache offline do monitor e da página acessível. A API não passa por aqui:
// os dados são guardados por assets/js/dadosLocais.js.
const CACHE = 'iroyin-v3';
const CACHE_IMAGENS = 'iroyin-imagens-v1';
const MAX_IMAGENS = 150;
const TEMPO_LIMITE_MS = 5000;

const PAGINA = /^\/[a-z0-9-]+\/?$/;
const ACESSIVEL = /^\/[a-z0-9-]+\/acessivel(\/|$)/;
const ARQUIVO = /^\/assets\//;
const IMAGEM = /^\/(images|noticias)\//;

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
            .then(nomes => Promise.all(
                nomes.filter(n => n !== CACHE && n !== CACHE_IMAGENS).map(n => caches.delete(n))
            ))
            .then(() => self.clients.claim())
    );
});

// O monitor informa o que precisa. Um arquivo que falhe não impede os demais.
self.addEventListener('message', e => {
    if (!e.data || e.data.tipo !== 'guardar') return;

    e.waitUntil(caches.open(CACHE).then(cache => Promise.all(
        e.data.urls
            .filter(u => new URL(u, self.location.origin).origin === self.location.origin)
            .map(u => cache.add(new Request(u, { cache: 'no-cache' })).catch(() => null))
    )));
});

self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);
    if (e.request.method !== 'GET' || url.origin !== self.location.origin) return;

    if (ACESSIVEL.test(url.pathname)) {
        e.respondWith(redePrimeiro(e.request, true));
    } else if (PAGINA.test(url.pathname)) {
        e.respondWith(redePrimeiro(e.request, false));
    } else if (ARQUIVO.test(url.pathname)) {
        e.respondWith(copiaPrimeiro(e, CACHE));
    } else if (IMAGEM.test(url.pathname)) {
        e.respondWith(copiaPrimeiro(e, CACHE_IMAGENS));
    }
});

// Páginas: a versão do servidor vence; a cópia cobre rede fora, travada ou servidor com erro.
// Login e demais telas nunca têm cópia, pois só se guarda o que o monitor pediu.
async function redePrimeiro(request, guardarSempre) {
    const cache = await caches.open(CACHE);
    const copia = await cache.match(request, { ignoreSearch: true });

    const rede = fetch(request).then(res => {
        if (res.ok && (guardarSempre || copia)) cache.put(request, res.clone()).catch(() => null);
        return res;
    });

    if (!copia) return rede;

    const limite = new Promise(ok => setTimeout(() => ok(null), TEMPO_LIMITE_MS));
    const res = await Promise.race([rede.catch(() => null), limite]);

    return res && res.status < 500 ? res : copia;
}

// Arquivos e imagens: abre na hora com a cópia e atualiza para a próxima vez.
async function copiaPrimeiro(evento, nomeCache) {
    const cache = await caches.open(nomeCache);
    const copia = await cache.match(evento.request, { ignoreSearch: true });

    const rede = fetch(evento.request)
        .then(async res => {
            if (res.ok) {
                await cache.put(evento.request, res.clone());
                if (nomeCache === CACHE_IMAGENS) await limitar(cache);
            }
            return res;
        })
        .catch(() => null);

    evento.waitUntil(rede);

    return copia || (await rede) || Response.error();
}

async function limitar(cache) {
    const chaves = await cache.keys();
    await Promise.all(chaves.slice(0, Math.max(0, chaves.length - MAX_IMAGENS)).map(c => cache.delete(c)));
}
