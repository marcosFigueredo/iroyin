<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Semestre;
use App\Models\Unidade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    private const COLUNAS_CSV = ['dia', 'disciplina', 'professor', 'curso', 'inicio', 'fim', 'sala'];

    public function index(Request $request, Semestre $semestre)
    {
        $ordem = array_flip(Horario::diasOrdem());

        $horarios = $this->visiveis($request, $semestre)
            ->with('unidade')
            ->orderBy('inicio')
            ->get()
            ->sortBy(fn (Horario $h) => $ordem[$h->dia_semana] ?? 99)
            ->groupBy('dia_semana');

        return view('admin.horarios.index', compact('semestre', 'horarios'));
    }

    public function create(Request $request, Semestre $semestre)
    {
        $this->exigirCurso($request);

        return view('admin.horarios.create', ['semestre' => $semestre, 'cursos' => $this->cursos($request)->get()]);
    }

    public function store(Request $request, Semestre $semestre)
    {
        $this->exigirCurso($request);

        $semestre->horarios()->create($this->validar($request));

        return redirect()->route('admin.semestres.horarios.index', $semestre)
            ->with('success', 'Aula adicionada com sucesso.');
    }

    public function edit(Request $request, Semestre $semestre, int $horario)
    {
        return view('admin.horarios.edit', [
            'semestre' => $semestre,
            'horario'  => $this->buscar($request, $semestre, $horario),
            'cursos'   => $this->cursos($request)->get(),
        ]);
    }

    public function update(Request $request, Semestre $semestre, int $horario)
    {
        $this->buscar($request, $semestre, $horario)->update($this->validar($request));

        return redirect()->route('admin.semestres.horarios.index', $semestre)
            ->with('success', 'Aula atualizada com sucesso.');
    }

    public function destroy(Request $request, Semestre $semestre, int $horario)
    {
        $this->buscar($request, $semestre, $horario)->delete();

        return back()->with('success', 'Aula removida.');
    }

    public function importar(Request $request, Semestre $semestre)
    {
        $this->exigirCurso($request);

        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $usuario = $request->user();

        if (! $usuario->isUnidade() && ! $usuario->escopoDepartamentoId()) {
            return back()->with('error', 'Escolha um departamento em "Operar como" antes de importar.');
        }

        // O arquivo só é lido aqui: o PHP o descarta ao terminar a requisição.
        $linhas = $this->lerCsv(file_get_contents($request->file('csv')->getRealPath()));
        $cabecalho = array_map(fn ($c) => strtolower(trim($c)), $linhas[0] ?? []);

        $faltando = array_diff(self::COLUNAS_CSV, $cabecalho);
        if ($faltando) {
            return back()->with('error', 'CSV inválido. Colunas faltando: '.implode(', ', $faltando).'. Nada foi salvo.');
        }

        $cursos = $this->cursos($request)->get()->keyBy(fn (Unidade $u) => mb_strtolower($u->nome));
        $aulas  = [];
        $erros  = [];

        foreach (array_slice($linhas, 1) as $i => $linha) {
            $numero = $i + 2;

            if (count($linha) !== count($cabecalho)) {
                $erros[] = "Linha $numero: número de colunas diferente do cabeçalho.";
                continue;
            }

            $row  = array_combine($cabecalho, array_map('trim', $linha));
            $erro = $this->erroDaLinha($row, $cursos);

            if ($erro) {
                $erros[] = "Linha $numero: $erro.";
                continue;
            }

            $aulas[] = [
                'semestre_id' => $semestre->id,
                'unidade_id'  => $cursos->get(mb_strtolower($row['curso']))->id,
                'dia_semana'  => strtolower($row['dia']),
                'disciplina'  => $row['disciplina'],
                'professor'   => $row['professor'],
                'inicio'      => substr($row['inicio'], 0, 5),
                'fim'         => substr($row['fim'], 0, 5),
                'sala'        => $row['sala'],
            ];
        }

        if ($erros) {
            return back()->with('error', 'Nada foi salvo. Corrija o arquivo: '.implode(' | ', array_slice($erros, 0, 20)));
        }

        if (! $aulas) {
            return back()->with('error', 'O arquivo não tem nenhuma aula. Nada foi salvo.');
        }

        DB::transaction(function () use ($aulas) {
            foreach ($aulas as $aula) {
                Horario::firstOrCreate($aula);
            }
        });

        return redirect()->route('admin.semestres.horarios.index', $semestre)
            ->with('success', count($aulas).' aula(s) importada(s) com sucesso.');
    }

    /** @return array<int, array<int, string>> */
    private function lerCsv(string $conteudo): array
    {
        // Remove o BOM que o Excel adiciona e aceita arquivos salvos em Latin-1.
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
        if (! mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
        }

        $brutas = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($conteudo)),
            fn ($l) => trim($l) !== ''
        ));

        // O Excel em português separa as colunas com ponto e vírgula.
        $separador = str_contains($brutas[0] ?? '', ';') && ! str_contains($brutas[0] ?? '', ',') ? ';' : ',';

        return array_map(fn ($l) => str_getcsv($l, $separador, '"', ''), $brutas);
    }

    private function erroDaLinha(array $row, $cursos): ?string
    {
        $vazios = array_filter(self::COLUNAS_CSV, fn ($c) => $row[$c] === '');
        if ($vazios) {
            return 'faltam os dados de '.implode(', ', $vazios);
        }

        if (! in_array(strtolower($row['dia']), Horario::diasOrdem(), true)) {
            return "dia '{$row['dia']}' inválido";
        }

        if (! $cursos->has(mb_strtolower($row['curso']))) {
            return "curso '{$row['curso']}' não cadastrado ou fora do seu acesso";
        }

        foreach (['inicio', 'fim'] as $campo) {
            if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $row[$campo])) {
                return "horário de $campo '{$row[$campo]}' inválido (use HH:MM)";
            }
        }

        if (substr($row['fim'], 0, 5) <= substr($row['inicio'], 0, 5)) {
            return 'o fim deve ser depois do início';
        }

        if (mb_strlen($row['sala']) > 20 || mb_strlen($row['disciplina']) > 255 || mb_strlen($row['professor']) > 255) {
            return 'texto maior que o permitido (sala até 20 caracteres)';
        }

        return null;
    }

    private function exigirCurso(Request $request): void
    {
        $usuario = $request->user();

        abort_if($usuario->isUnidade() && ! $usuario->unidade?->temHorarios(), 403, 'Esta unidade não tem horários.');
    }

    /** Cursos em que o usuário pode lançar aulas. */
    private function cursos(Request $request): Builder
    {
        $usuario = $request->user();

        return Unidade::where('tipo', 'curso')
            ->when($usuario->isUnidade(), fn ($q) => $q->whereKey($usuario->unidade_id))
            ->when(! $usuario->isUnidade() && $usuario->escopoDepartamentoId(), fn ($q) => $q->where('departamento_id', $usuario->escopoDepartamentoId()))
            ->orderBy('nome');
    }

    private function visiveis(Request $request, Semestre $semestre): Builder
    {
        $usuario = $request->user();

        return Horario::where('semestre_id', $semestre->id)
            ->when($usuario->isUnidade(), fn ($q) => $q->where('unidade_id', $usuario->unidade_id))
            ->when(! $usuario->isUnidade() && $usuario->escopoDepartamentoId(), fn ($q) => $q->whereHas(
                'unidade', fn ($u) => $u->where('departamento_id', $usuario->escopoDepartamentoId())
            ));
    }

    private function buscar(Request $request, Semestre $semestre, int $id): Horario
    {
        return $this->visiveis($request, $semestre)->findOrFail($id);
    }

    private function validar(Request $request): array
    {
        $usuario = $request->user();

        $data = $request->validate([
            'unidade_id'  => [
                Rule::requiredIf(! $usuario->isUnidade()),
                'nullable',
                Rule::when(! $usuario->isUnidade(), [Rule::in($this->cursos($request)->pluck('id')->all())]),
            ],
            'dia_semana'  => ['required', 'in:segunda,terca,quarta,quinta,sexta,sabado'],
            'disciplina'  => ['required', 'string', 'max:255'],
            'professor'   => ['required', 'string', 'max:255'],
            'inicio'      => ['required', 'date_format:H:i'],
            'fim'         => ['required', 'date_format:H:i', 'after:inicio'],
            'sala'        => ['required', 'string', 'max:20'],
        ]);

        if ($usuario->isUnidade()) {
            $data['unidade_id'] = $usuario->unidade_id;
        }

        return $data;
    }
}
