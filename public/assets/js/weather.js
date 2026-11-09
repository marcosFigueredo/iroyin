window._configReady.then(function () {
    var bloco = document.querySelector('.info-weather');
    var icone = document.getElementById('weather-icon');

    // O ícone vem de fora: sem rede, some em vez de aparecer quebrado.
    icone.onerror = function () { icone.style.visibility = 'hidden'; };
    icone.onload  = function () { icone.style.visibility = 'visible'; };

    function updateWeather() {
        window.Dados.buscar('clima')
            .then(function (data) {
                bloco.style.visibility = data ? 'visible' : 'hidden';
                if (!data) return;

                document.getElementById('weather-temp').textContent     = data.temp + '°C';
                document.getElementById('weather-city').textContent     = data.cidade;
                document.getElementById('weather-icon').src             = 'https://openweathermap.org/img/wn/' + data.icone + '.png';
                document.getElementById('weather-icon').alt             = data.descricao;
                document.getElementById('weather-humidity').textContent = data.umidade + '%';
                document.getElementById('weather-wind').textContent     = data.vento + ' m/s';
            })
            .catch(function (err) {
                // Sem dado novo nem guardado: melhor ocultar do que mostrar vazio.
                bloco.style.visibility = 'hidden';
                console.error('Erro ao obter dados do clima:', err);
            });
    }

    setInterval(updateWeather, 600000);
    updateWeather();
});
