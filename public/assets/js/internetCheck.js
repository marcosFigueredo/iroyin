// Sem rede o painel segue com os dados guardados; aqui só se avisa de onde eles vêm.
function setOffline(offline) {
    const aviso = document.getElementById('offline-aviso');
    if (!aviso) return;

    if (offline) aviso.textContent = window.Dados.textoAviso();
    aviso.style.display = offline ? 'block' : 'none';
}

function testServerConnection() {
    const opcoes = { cache: 'no-store' };
    if (AbortSignal.timeout) opcoes.signal = AbortSignal.timeout(8000);

    fetch('/ping.txt', opcoes)
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
