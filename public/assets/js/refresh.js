function startAutoRefresh() {
    setInterval(function () {
        location.reload();
    }, 4 * 60 * 60 * 1000); // 4 horas
}
