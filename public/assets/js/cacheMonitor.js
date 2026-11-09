// Pede ao service worker que guarde tudo o que o monitor precisa para abrir sem rede.
(function (raiz) {
    var ARQUIVOS = [
        '/assets/vendor/bootstrap/bootstrap.min.css',
        '/assets/vendor/bootstrap-icons/bootstrap-icons.min.css',
        '/assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
        '/assets/vendor/fontawesome/css/all.min.css',
        '/assets/vendor/fontawesome/webfonts/fa-solid-900.woff2',
        '/assets/vendor/roboto/roboto.css',
        '/assets/vendor/roboto/roboto-400.woff2',
        '/assets/vendor/roboto/roboto-500.woff2',
        '/assets/vendor/roboto/roboto-700.woff2',
        '/assets/vendor/anime.min.js',
        '/assets/vendor/qrcode.min.js',
        '/assets/css/style.css',
        '/assets/css/informe.css',
        '/assets/image/humidy.png',
        '/assets/image/wind.png',
        '/assets/js/temas.js',
        '/assets/js/criterios.js',
        '/assets/js/informeRender.js',
        '/assets/js/dadosLocais.js',
        '/assets/js/config.js',
        '/assets/js/cacheMonitor.js',
        '/assets/js/internetCheck.js',
        '/assets/js/dataHora.js',
        '/assets/js/weather.js',
        '/assets/js/classTime.js',
        '/assets/js/newsBanner.js',
        '/assets/js/newsDisplay.js',
        '/assets/js/editais.js',
        '/assets/js/kiosk.js',
        '/assets/js/refresh.js',
    ];

    raiz.ArquivosMonitor = ARQUIVOS;

    if (typeof document === 'undefined' || !navigator.serviceWorker) return;

    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/iroyin-sw.js').catch(function (e) {
            console.error('Cache offline indisponível:', e);
        });

        Promise.all([navigator.serviceWorker.ready, window._configReady]).then(function (r) {
            var logo = r[1].logo_url;
            r[0].active.postMessage({
                tipo: 'guardar',
                urls: [location.pathname].concat(ARQUIVOS, logo ? [logo] : []),
            });
        });
    });
})(typeof window !== 'undefined' ? window : globalThis);
