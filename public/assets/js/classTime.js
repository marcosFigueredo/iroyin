document.addEventListener("DOMContentLoaded", function () {

    const TURNOS = {
        manha:  { label: 'Turno — Manhã',   inicio: [7,  0],  fim: [12, 30] },
        tarde:  { label: 'Turno — Tarde',   inicio: [12, 31], fim: [18, 20] },
        noite:  { label: 'Turno — Noite',   inicio: [18, 21], fim: [22, 30] },
    };

    function toDate(h, m) {
        const d = new Date();
        d.setHours(h, m, 0, 0);
        return d;
    }

    function timeStrToDate(str) {
        if (!str) return null;
        const parts = str.split(':').map(Number);
        const d = new Date();
        d.setHours(parts[0], parts[1], 0, 0);
        return d;
    }

    function detectTurno(now) {
        for (const [key, t] of Object.entries(TURNOS)) {
            if (now >= toDate(...t.inicio) && now <= toDate(...t.fim)) {
                return { key, label: t.label };
            }
        }
        return null;
    }

    function filterByTurno(aulas, turno, now) {
        const ini = toDate(...TURNOS[turno].inicio);
        const fim = toDate(...TURNOS[turno].fim);
        return aulas.filter(a => {
            const s = timeStrToDate(a.inicio);
            const e = timeStrToDate(a.fim);
            return s && e && s >= ini && s <= fim && e > now;
        });
    }

    // Paleta de cores para aulas em andamento — cada linha recebe uma cor diferente
    const CORES_ANDAMENTO = [
        { bg: '#fff3cd', border: '#e6a817', text: '#5a3e00' }, // âmbar
        { bg: '#d4edda', border: '#28a745', text: '#155724' }, // verde
        { bg: '#cfe2ff', border: '#0d6efd', text: '#08306b' }, // azul
        { bg: '#f8d7da', border: '#dc3545', text: '#721c24' }, // vermelho
        { bg: '#e2d9f3', border: '#6f42c1', text: '#3d1a78' }, // roxo
        { bg: '#d1ecf1', border: '#0dcaf0', text: '#0c5460' }, // teal
    ];

    function iconSala(sala) {
        if (!sala || sala === '—') return sala || '—';
        if (/lab/i.test(sala)) {
            return `<i class="bi bi-pc-display-horizontal"></i> ${sala}`;
        }
        return `<i class="fa-solid fa-chalkboard-user"></i> ${sala}`;
    }

    function renderTable(aulas, now) {
        const tableBody = document.getElementById('table-horario');
        tableBody.innerHTML = '';

        if (!aulas || aulas.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:#888;">Não há aulas no momento.</td></tr>';
            return;
        }

        let corIdx = 0; // contador para ciclar a paleta de "em andamento"

        aulas.forEach((row, i) => {
            const startTime = timeStrToDate(row.inicio);
            const endTime   = timeStrToDate(row.fim);
            const isNow     = startTime && endTime && startTime <= now && endTime > now;
            const tr        = document.createElement('tr');
            const sala      = row.sala || '—';
            const ini       = row.inicio ? row.inicio.substring(0, 5) : '';
            const fim       = row.fim    ? row.fim.substring(0, 5)    : '';

            // cor precisa ser declarada fora do if para estar disponível após o innerHTML
            let cor = null;
            if (isNow) {
                cor = CORES_ANDAMENTO[corIdx % CORES_ANDAMENTO.length];
                corIdx++;
                tr.style.backgroundColor = cor.bg;
                tr.style.borderLeft      = `8px solid ${cor.border}`;
                tr.style.fontWeight      = '700';
            }

            tr.innerHTML = `<td>${row.disciplina}</td><td>${row.professor}</td>`
                         + `<td>${row.curso}</td><td class="col-inicio">${ini}</td>`
                         + `<td class="col-fim">${fim}</td><td class="col-sala">${iconSala(sala)}</td>`;

            if (cor) {
                // aplica a cor do texto nos <td> (CSS do tema seria sobrescrito pela herança)
                tr.querySelectorAll('td').forEach(td => {
                    td.style.color      = cor.text;
                    td.style.fontWeight = '700';
                });
            }

            tableBody.appendChild(tr);
        });

        initAutoScroll();
    }

    function showBoaNoite() {
        document.getElementById('horarios').style.display      = 'none';
        document.getElementById('boa-noite').style.display     = 'flex';
        if (!window.isBoaNoite) {
            window.isBoaNoite = true;
            window.dispatchEvent(new CustomEvent('kioskBoaNoite'));
        }
    }

    function hideBoaNoite() {
        document.getElementById('boa-noite').style.display = 'none';
        if (window.isBoaNoite) {
            window.isBoaNoite = false;
            window.dispatchEvent(new CustomEvent('kioskResumeMode'));
        }
    }

    function updateTurnoLabel(turnoKey) {
        const el = document.getElementById('turnoIndicador');
        if (!el) return;
        if (turnoKey) {
            el.textContent = TURNOS[turnoKey].label;
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    function updateSchedule() {
        const now = new Date();
        const day = now.getDay(); // 0=domingo

        const startDay = new Date(); startDay.setHours(6, 50, 0, 0);
        const endDay   = new Date(); endDay.setHours(22, 31, 0, 0);

        if (day === 0 || now < startDay || now > endDay) {
            showBoaNoite();
            updateTurnoLabel(null);
            return;
        }

        hideBoaNoite();
        document.getElementById('horarios').style.display = 'block';

        fetch('/api/horarios')
            .then(r => r.json())
            .then(data => {
                const turno = detectTurno(now);
                updateTurnoLabel(turno ? turno.key : null);

                if (!data.aulas || data.aulas.length === 0) {
                    renderTable([], now);
                    return;
                }

                const aulasFiltradas = turno
                    ? filterByTurno(data.aulas, turno.key, now)
                    : [];

                renderTable(aulasFiltradas, now);
            })
            .catch(err => {
                console.error('Erro ao carregar horários:', err);
                document.getElementById('table-horario').innerHTML =
                    '<tr><td colspan="6" style="text-align:center;padding:40px;color:#c00;">Erro ao carregar dados.</td></tr>';
            });
    }

    function initAutoScroll() {
        const scrollTable = document.querySelector('.scroll-table');
        if (!scrollTable) return;
        let scrollAmount = 0;
        let direction = 1;
        const step = 0.3;

        function smoothScroll() {
            const maxScroll = scrollTable.scrollHeight - scrollTable.clientHeight;
            if (maxScroll <= 0) return; // nothing to scroll
            if      (scrollAmount >= maxScroll) direction = -1;
            else if (scrollAmount <= 0)         direction =  1;
            scrollAmount += step * direction;
            scrollTable.scrollTo(0, scrollAmount);
            requestAnimationFrame(smoothScroll);
        }
        requestAnimationFrame(smoothScroll);
    }

    updateSchedule();
    setInterval(updateSchedule, 50000);

});
