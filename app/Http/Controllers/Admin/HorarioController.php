<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Semestre;
use App\Models\Unidade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
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

        $conteudo = file_get_contents($request->file('csv')->getRealPath());
        // Remove BOM UTF-8 que o Excel adiciona
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);

        $linhas = array_map('str_getcsv', explode("\n", trim($conteudo)));
        $cabecalho = array_map('strtolower', array_map('trim', $linhas[0]));

        $campos = ['dia','disciplina','professor','curso','inicio','fim','sala'];
        $faltando = array_diff($campos, $cabecalho);
        if ($faltando) {
            return back()->with('error', 'CSV inválido. Colunas faltando: ' . implode(', ', $faltando));
        }

        $cursos      = $this->cursos($request)->get()->keyBy(fn (Unidade $u) => mb_strtolower($u->nome));
        $diasValidos = Horario::diasOrdem();
        $importados  = 0;
        $erros       = [];

        foreach (array_slice($linhas, 1) as $i => $linha) {
            if (count($linha) < count($cabecalho)) continue;
            $row = array_combine($cabecalho, array_map('trim', $linha));

            $dia = strtolower($row['dia'] ?? '');
            if (! in_array($dia, $diasValidos)) {
                $erros[] = "Linha " . ($i + 2) . ": dia '$dia' inválido.";
                continue;
            }

            $curso = $usuario->isUnidade() ? $usuario->unidade : $cursos->get(mb_strtolower($row['curso']));
            if (! $curso) {
                $erros[] = "Linha " . ($i + 2) . ": curso '{$row['curso']}' não cadastrado.";
                continue;
            }

            $semestre->horarios()->create([
                'unidade_id'  => $curso->id,
                'dia_semana'  => $dia,
                'disciplina'  => $row['disciplina'],
                'professor'   => $row['professor'],
                'inicio'      => $row['inicio'],
                'fim'         => $row['fim'],
                'sala'        => $row['sala'],
            ]);
            $importados++;
        }

        $msg = "$importados aula(s) importada(s) com sucesso.";
        if ($erros) $msg .= ' Erros: ' . implode(' | ', $erros);

        return redirect()->route('admin.semestres.horarios.index', $semestre)
            ->with($erros ? 'error' : 'success', $msg);
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
