<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Semestre;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index(Semestre $semestre)
    {
        $horarios = $semestre->horarios()
            ->orderByRaw("FIELD(dia_semana,'segunda','terca','quarta','quinta','sexta','sabado')")
            ->orderBy('inicio')
            ->get()
            ->groupBy('dia_semana');

        return view('admin.horarios.index', compact('semestre', 'horarios'));
    }

    public function create(Semestre $semestre)
    {
        return view('admin.horarios.create', compact('semestre'));
    }

    public function store(Request $request, Semestre $semestre)
    {
        $data = $request->validate([
            'dia_semana'  => ['required', 'in:segunda,terca,quarta,quinta,sexta,sabado'],
            'disciplina'  => ['required', 'string', 'max:255'],
            'professor'   => ['required', 'string', 'max:255'],
            'curso'       => ['required', 'string', 'max:255'],
            'inicio'      => ['required', 'date_format:H:i'],
            'fim'         => ['required', 'date_format:H:i', 'after:inicio'],
            'sala'        => ['required', 'string', 'max:20'],
        ]);

        $semestre->horarios()->create($data);

        return redirect()->route('admin.semestres.horarios.index', $semestre)
            ->with('success', 'Aula adicionada com sucesso.');
    }

    public function edit(Semestre $semestre, Horario $horario)
    {
        return view('admin.horarios.edit', compact('semestre', 'horario'));
    }

    public function update(Request $request, Semestre $semestre, Horario $horario)
    {
        $data = $request->validate([
            'dia_semana'  => ['required', 'in:segunda,terca,quarta,quinta,sexta,sabado'],
            'disciplina'  => ['required', 'string', 'max:255'],
            'professor'   => ['required', 'string', 'max:255'],
            'curso'       => ['required', 'string', 'max:255'],
            'inicio'      => ['required', 'date_format:H:i'],
            'fim'         => ['required', 'date_format:H:i', 'after:inicio'],
            'sala'        => ['required', 'string', 'max:20'],
        ]);

        $horario->update($data);

        return redirect()->route('admin.semestres.horarios.index', $semestre)
            ->with('success', 'Aula atualizada com sucesso.');
    }

    public function destroy(Semestre $semestre, Horario $horario)
    {
        $horario->delete();
        return back()->with('success', 'Aula removida.');
    }

    public function importar(Request $request, Semestre $semestre)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

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

        $diasValidos = ['segunda','terca','quarta','quinta','sexta','sabado'];
        $importados  = 0;
        $erros       = [];

        foreach (array_slice($linhas, 1) as $i => $linha) {
            if (count($linha) < count($cabecalho)) continue;
            $row = array_combine($cabecalho, array_map('trim', $linha));

            $dia = strtolower($row['dia'] ?? '');
            if (!in_array($dia, $diasValidos)) {
                $erros[] = "Linha " . ($i + 2) . ": dia '$dia' inválido.";
                continue;
            }

            $semestre->horarios()->create([
                'dia_semana'  => $dia,
                'disciplina'  => $row['disciplina'],
                'professor'   => $row['professor'],
                'curso'       => $row['curso'],
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
}
