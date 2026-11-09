let _offlineAtivo = false;

function setOffline(offline) {
    const overlay = document.getElementById('offline-overlay');
    if (!overlay) return;

    if (offline && !_offlineAtivo) {
        _offlineAtivo = true;
        overlay.style.display = 'flex';
        // pausa o ciclo de notícias enquanto estiver offline
        if (typeof window.pauseKioskCycle === 'function') {
            window.pauseKioskCycle();
        }
        const ns = document.getElementById('news-section');
        const nb = document.getElementById('news-banner');
        if (ns) ns.style.display = 'none';
        if (nb) nb.style.display = 'none';
    }

    if (!offline && _offlineAtivo) {
        _offlineAtivo = false;
        overlay.style.display = 'none';
        // retoma o ciclo ao reconectar (se não estiver em boa noite)
        if (typeof window.resumeKioskCycle === 'function') {
            window.resumeKioskCycle();
        }
    }
}

function testServerConnection() {
    fetch('/ping.txt', { cache: 'no-store' })
        .then(r => setOffline(!r.ok))
        .catch(() => setOffline(true));
}

window.addEventListener('load', () => {
    if (!navigator.onLine) {
        setOffline(true);
    } else {
        testServerConnection();
    }

    window.addEventListener('online',  testServerConnection);
    window.addEventListener('offline', () => setOffline(true));

    setInterval(testServerConnection, 20000); // verifica a cada 20 s
});
