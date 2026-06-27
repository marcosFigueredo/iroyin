<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#1a3a6b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Horários">
    <link rel="manifest" href="/acessivel-manifest.json">
    <title>Horários de Aulas</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --azul:       #1a3a6b;
            --azul-claro: #e8eef7;
            --texto:      #111111;
            --secundario: #444444;
            --borda:      #cccccc;
            --fundo-aula: #f5f7fa;
            --fonte:      1rem;
        }

        html { font-size: var(--fonte); }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            color: var(--texto);
            font-size: 1.05rem;
            line-height: 1.6;
            min-height: 100dvh;
        }

        /* Skip link */
        .skip-link {
            position: absolute;
            top: -100%;
            left: 1rem;
            background: #ffdd00;
            color: #000000;
            padding: 0.5rem 1rem;
            font-weight: 700;
            z-index: 999;
            border-radius: 0 0 4px 4px;
            text-decoration: none;
        }
        .skip-link:focus { top: 0; }

        /* Header */
        header {
            background: var(--azul);
            color: #ffffff;
            padding: 1.1rem 1rem 0.9rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        header h1 { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
        header p  { font-size: 0.9rem; opacity: 0.8; margin-top: 0.2rem; }

        /* Barra de controles */
        .controles {
            background: var(--azul-claro);
            border-bottom: 1px solid var(--borda);
            padding: 0.55rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .controles-fonte {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .btn-fonte {
            background: #ffffff;
            color: var(--azul);
            border: 2px solid var(--azul);
            border-radius: 6px;
            padding: 0.3rem 0.65rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
            min-width: 38px;
        }
        .btn-fonte:focus {
            outline: 3px solid #ffdd00;
            outline-offset: 2px;
        }
        .btn-fonte:disabled {
            opacity: 0.35;
            cursor: default;
        }
        #btn-menor { font-size: 0.8rem; }
        #btn-maior { font-size: 1rem; }

        .separador {
            width: 1px;
            height: 28px;
            background: var(--borda);
        }

        /* Botão ouvir/parar */
        #btn-ouvir {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--azul);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 0.4rem 0.9rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
        }
        #btn-ouvir:focus {
            outline: 3px solid #ffdd00;
            outline-offset: 2px;
        }
        #btn-ouvir.pausado {
            background: #b91c1c;
        }

        /* Navegação por turno */
        nav {
            background: #ffffff;
            border-bottom: 1px solid var(--borda);
            padding: 0.45rem 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        nav a {
            color: var(--azul);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.3rem 0.75rem;
            border: 2px solid var(--azul);
            border-radius: 20px;
        }
        nav a:focus {
            outline: 3px solid #ffdd00;
            outline-offset: 2px;
        }

        /* Conteúdo principal */
        main {
            padding: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Turno */
        .turno {
            margin-bottom: 2rem;
            scroll-margin-top: 160px;
        }
        .turno h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--azul);
            border-bottom: 2px solid var(--azul);
            padding-bottom: 0.35rem;
            margin-bottom: 0.75rem;
        }

        /* Aulas */
        .aulas-lista { list-style: none; display: flex; flex-direction: column; gap: 0.65rem; }

        .aula-card {
            background: var(--fundo-aula);
            border-left: 4px solid var(--azul);
            border-radius: 0 8px 8px 0;
            padding: 0.8rem 1rem;
        }
        .aula-hora {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--secundario);
            letter-spacing: 0.04em;
        }
        .aula-disciplina {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--texto);
            margin: 0.2rem 0 0.3rem;
        }
        .aula-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
            color: var(--secundario);
        }
        .sala {
            background: var(--azul);
            color: #ffffff;
            padding: 0.1rem 0.5rem;
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        /* Aula sendo lida */
        .aula-card.lendo {
            border-left-color: #e67e22;
            background: #fef9f0;
        }

        /* Estado vazio */
        .sem-aulas {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--secundario);
        }
        .sem-aulas strong { display: block; font-size: 1.1rem; margin-bottom: 0.5rem; }

        /* Footer */
        footer {
            text-align: center;
            padding: 1.25rem 1rem;
            color: var(--secundario);
            font-size: 0.85rem;
            border-top: 1px solid var(--borda);
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>

    <header role="banner">
        <h1>Horários de Aulas</h1>
        <p>
            {{ $diaNome }}
            @if($semestre)
                &mdash; {{ $semestre->nome }}
            @endif
        </p>
    </header>

    <!-- Barra de controles de acessibilidade -->
    <div class="controles" role="toolbar" aria-label="Controles de acessibilidade">
        <div class="controles-fonte" role="group" aria-label="Tamanho do texto">
            <button id="btn-menor" class="btn-fonte" aria-label="Diminuir tamanho do texto">A−</button>
            <button id="btn-maior" class="btn-fonte" aria-label="Aumentar tamanho do texto">A+</button>
        </div>

        <div class="separador" aria-hidden="true"></div>

        @if($totalAulas > 0)
            <button id="btn-ouvir" aria-label="Ouvir horários em voz alta">
                <span id="ouvir-icone" aria-hidden="true">🔊</span>
                <span id="ouvir-texto">Ouvir</span>
            </button>
        @endif
    </div>

    @if($totalAulas > 0)
        <nav aria-label="Ir para o turno">
            @foreach($turnos as $nomeTurno => $aulas)
                @if($aulas->isNotEmpty())
                    <a href="#turno-{{ Str::slug($nomeTurno) }}">{{ $nomeTurno }}</a>
                @endif
            @endforeach
        </nav>
    @endif

    <main id="conteudo" role="main">

        @if($totalAulas === 0)
            <div class="sem-aulas" role="status">
                <strong>Nenhuma aula hoje.</strong>
                @if(!$semestre)
                    <span>Nenhum semestre ativo no momento.</span>
                @endif
            </div>
        @else
            @foreach($turnos as $nomeTurno => $aulas)
                @if($aulas->isNotEmpty())
                    <section
                        class="turno"
                        id="turno-{{ Str::slug($nomeTurno) }}"
                        aria-labelledby="titulo-{{ Str::slug($nomeTurno) }}"
                    >
                        <h2 id="titulo-{{ Str::slug($nomeTurno) }}">{{ $nomeTurno }}</h2>

                        <ul class="aulas-lista" role="list">
                            @foreach($aulas as $aula)
                                <li class="aula-card" data-disciplina="{{ $aula->disciplina }}"
                                    data-professor="{{ $aula->professor }}"
                                    data-hora="das {{ $aula->inicio }} às {{ $aula->fim }}"
                                    data-sala="{{ $aula->sala ?? '' }}"
                                    data-curso="{{ $aula->curso ?? '' }}">

                                    <div class="aula-hora"
                                         aria-label="Horário: das {{ $aula->inicio }} às {{ $aula->fim }}">
                                        {{ $aula->inicio }} – {{ $aula->fim }}
                                    </div>

                                    <div class="aula-disciplina">{{ $aula->disciplina }}</div>

                                    <div class="aula-meta">
                                        <span aria-label="Professor: {{ $aula->professor }}">
                                            {{ $aula->professor }}
                                        </span>
                                        @if($aula->curso)
                                            <span aria-label="Curso: {{ $aula->curso }}">
                                                · {{ $aula->curso }}
                                            </span>
                                        @endif
                                        @if($aula->sala)
                                            <span class="sala" aria-label="Sala: {{ $aula->sala }}">
                                                {{ $aula->sala }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endforeach
        @endif

    </main>

    <footer role="contentinfo">
        <p>Atualizado às {{ now()->format('H:i') }}</p>
    </footer>

    <script>
    (function () {

        // ── Tamanho de fonte ──────────────────────────────────────────────────
        var NIVEIS = [14, 16, 19, 22];
        var nivel  = parseInt(localStorage.getItem('iroyin-fonte') || '1', 10);

        function aplicarFonte() {
            document.documentElement.style.fontSize = NIVEIS[nivel] + 'px';
            document.getElementById('btn-menor').disabled = (nivel === 0);
            document.getElementById('btn-maior').disabled = (nivel === NIVEIS.length - 1);
            localStorage.setItem('iroyin-fonte', nivel);
        }

        document.getElementById('btn-menor').addEventListener('click', function () {
            if (nivel > 0) { nivel--; aplicarFonte(); }
        });

        document.getElementById('btn-maior').addEventListener('click', function () {
            if (nivel < NIVEIS.length - 1) { nivel++; aplicarFonte(); }
        });

        aplicarFonte();

        // ── Leitura em voz alta (Web Speech API) ─────────────────────────────
        var btnOuvir = document.getElementById('btn-ouvir');
        if (!btnOuvir) return; // sem aulas, botão não existe

        var lendo       = false;
        var utterances  = [];
        var indiceAtual = 0;
        var cards       = Array.from(document.querySelectorAll('.aula-card'));

        function buildUtterances() {
            var lista = [];

            // Introdução
            var intro = new SpeechSynthesisUtterance('Horários de hoje.');
            intro.lang = 'pt-BR';
            lista.push({ u: intro, card: null });

            document.querySelectorAll('.turno').forEach(function (turno) {
                var nomeTurno = turno.querySelector('h2').textContent.trim();
                var cabecalho = new SpeechSynthesisUtterance('Turno da ' + nomeTurno + '.');
                cabecalho.lang = 'pt-BR';
                lista.push({ u: cabecalho, card: null });

                turno.querySelectorAll('.aula-card').forEach(function (card) {
                    var disciplina = card.dataset.disciplina;
                    var professor  = card.dataset.professor;
                    var hora       = card.dataset.hora;
                    var sala       = card.dataset.sala ? ', sala ' + card.dataset.sala : '';
                    var curso      = card.dataset.curso ? ', curso ' + card.dataset.curso : '';

                    var texto = disciplina + '. ' + professor + curso + ', ' + hora + sala + '.';
                    var u     = new SpeechSynthesisUtterance(texto);
                    u.lang    = 'pt-BR';
                    lista.push({ u: u, card: card });
                });
            });

            return lista;
        }

        function pararLeitura() {
            window.speechSynthesis.cancel();
            lendo = false;
            document.querySelectorAll('.aula-card.lendo').forEach(function (c) {
                c.classList.remove('lendo');
            });
            btnOuvir.classList.remove('pausado');
            document.getElementById('ouvir-icone').textContent = '🔊';
            document.getElementById('ouvir-texto').textContent = 'Ouvir';
            btnOuvir.setAttribute('aria-label', 'Ouvir horários em voz alta');
        }

        function lerProximo(lista, idx) {
            if (idx >= lista.length) {
                pararLeitura();
                return;
            }

            var item = lista[idx];

            // Destaca o card atual
            document.querySelectorAll('.aula-card.lendo').forEach(function (c) {
                c.classList.remove('lendo');
            });
            if (item.card) {
                item.card.classList.add('lendo');
                item.card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            item.u.onend = function () { lerProximo(lista, idx + 1); };
            item.u.onerror = function () { lerProximo(lista, idx + 1); };
            window.speechSynthesis.speak(item.u);
        }

        if (btnOuvir) {
            btnOuvir.addEventListener('click', function () {
                if (lendo) {
                    pararLeitura();
                    return;
                }

                if (!window.speechSynthesis) {
                    alert('Seu navegador não suporta leitura em voz alta.');
                    return;
                }

                lendo = true;
                btnOuvir.classList.add('pausado');
                document.getElementById('ouvir-icone').textContent = '⏹';
                document.getElementById('ouvir-texto').textContent = 'Parar';
                btnOuvir.setAttribute('aria-label', 'Parar leitura em voz alta');

                var lista = buildUtterances();
                window.speechSynthesis.cancel();
                lerProximo(lista, 0);
            });
        }

        // ── Service Worker ────────────────────────────────────────────────────
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/acessivel-sw.js');
        }

    })();
    </script>
</body>
</html>
