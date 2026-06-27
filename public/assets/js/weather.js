window._configReady.then(function () {
    function updateWeather() {
        fetch('/api/clima?_=' + Date.now())
            .then(function (r) {
                if (r.status === 204) {
                    var weatherEl = document.querySelector('.info-weather');
                    if (weatherEl) weatherEl.style.display = 'none';
                    return null;
                }
                return r.json();
            })
            .then(function (data) {
                if (!data || data.erro) return;

                document.getElementById('weather-temp').textContent     = data.temp + '°C';
                document.getElementById('weather-city').textContent     = data.cidade;
                document.getElementById('weather-icon').src             = 'https://openweathermap.org/img/wn/' + data.icone + '.png';
                document.getElementById('weather-icon').alt             = data.descricao;
                document.getElementById('weather-humidity').textContent = data.umidade + '%';
                document.getElementById('weather-wind').textContent     = data.vento + ' m/s';
            })
            .catch(function (err) { console.error('Erro ao obter dados do clima:', err); });
    }

    setInterval(updateWeather, 600000);
    updateWeather();
});
