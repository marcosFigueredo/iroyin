function updateTime() {
    const now = new Date();
    const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };

    document.getElementById('date').textContent = now.toLocaleDateString('pt-BR', optionsDate);
    document.getElementById('time').textContent = now.toLocaleTimeString('pt-BR', optionsTime);
}

setInterval(updateTime, 1000);
updateTime();
