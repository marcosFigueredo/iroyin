// Só recarrega se a página estiver respondendo: uma página de erro não tem
// scripts e deixaria o monitor preso até alguém reiniciar o aparelho.
window.recarregarSeDisponivel = function () {
    return fetch(location.href, { cache: 'no-store' })
        .then(function (r) {
            if (r.ok) location.reload();
        })
        .catch(function () { /* tenta de novo na próxima volta */ });
};

function startAutoRefresh() {
    setInterval(window.recarregarSeDisponivel, 4 * 60 * 60 * 1000); // 4 horas
}
