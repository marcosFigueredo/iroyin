<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\Instituicao;
use App\Models\Semestre;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Dados de teste do DCET de Alagoinhas (dcet2). Nunca roda em produção.
 *
 *   php artisan db:seed --class=TesteDcetSeeder
 *
 * Senha de todos os usuários: teste@2027
 * gestor@iroyin.teste, reitoria@iroyin.teste, gabinete@iroyin.teste, secretaria@iroyin.teste
 */
class TesteDcetSeeder extends Seeder
{
    private const ARQUIVO = 'seeders/data/dcet2_horarios.csv';

    private const LINHAS_PADRAO = [
        ['segunda', 'Cálculo I', 'Ana Lima', 'Sistemas de Informação', '08:00', '09:40', 'SALA 11'],
        ['terca', 'Banco de Dados', 'Maria Souza', 'Sistemas de Informação', '19:00', '20:40', 'LAB 03'],
        ['quarta', 'Geometria', 'João Ferreira', 'Matemática', '14:00', '15:40', 'SALA 05'],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Os dados de teste não podem ser carregados em produção.');
        }

        (new UnebSeeder)->run();

        Instituicao::current()->update(['nome' => 'Universidade do Estado da Bahia', 'sigla' => 'UNEB']);

        $dep = Departamento::where('slug', 'dcet2')->firstOrFail();
        $semestre = Semestre::firstOrCreate(['nome' => 'Teste'], [
            'inicio' => now()->subMonth()->toDateString(),
            'fim'    => now()->addMonths(3)->toDateString(),
        ]);

        $cursos = $this->carregarHorarios($dep, $semestre);
        $curso = $cursos->first();

        $this->usuario('gestor', 'Gestor de Teste', 'gestor');
        $this->usuario('reitoria', 'Reitoria de Teste', 'reitoria');
        $this->usuario('gabinete', 'Gabinete DCET2', 'departamento', $dep->id);
        $this->usuario('secretaria', 'Secretaria de Teste', 'unidade', $dep->id, $curso->id);

        $this->informe(null, null, 'Inscrições para bolsas abertas', 'A Reitoria informa que as inscrições seguem até o fim do mês.', 'normal');
        $this->informe($dep->id, null, 'Reunião de departamento', 'Reunião geral na sexta-feira, às 14h, no auditório.', 'importante');
        $this->informe($dep->id, $curso->id, 'Aula de hoje cancelada', 'A aula das 18h30 não acontecerá. A reposição será informada pelo professor.', 'urgente');
    }

    private function carregarHorarios(Departamento $dep, Semestre $semestre)
    {
        $caminho = database_path(self::ARQUIVO);
        $linhas = self::LINHAS_PADRAO;

        if (is_file($caminho)) {
            $linhas = array_map('str_getcsv', array_slice(file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), 1));
        }

        $cursos = collect();

        if (! Horario::where('semestre_id', $semestre->id)->exists()) {
            foreach ($linhas as [$dia, $disciplina, $professor, $nomeCurso, $inicio, $fim, $sala]) {
                $curso = $cursos->get($nomeCurso) ?? Unidade::firstOrCreate(
                    ['departamento_id' => $dep->id, 'nome' => $nomeCurso],
                    ['tipo' => 'curso'],
                );
                $cursos->put($nomeCurso, $curso);

                Horario::create([
                    'semestre_id' => $semestre->id, 'unidade_id' => $curso->id, 'dia_semana' => $dia,
                    'disciplina' => $disciplina, 'professor' => $professor,
                    'inicio' => $inicio, 'fim' => $fim, 'sala' => $sala,
                ]);
            }
        }

        return $cursos->isNotEmpty() ? $cursos : Unidade::where('departamento_id', $dep->id)->get();
    }

    private function usuario(string $apelido, string $nome, string $perfil, ?int $departamentoId = null, ?int $unidadeId = null): void
    {
        User::updateOrCreate(['email' => "{$apelido}@iroyin.teste"], [
            'name' => $nome, 'password' => Hash::make('teste@2027'), 'role' => $perfil, 'active' => true,
            'departamento_id' => $departamentoId, 'unidade_id' => $unidadeId, 'email_verified_at' => now(),
        ]);
    }

    private function informe(?int $departamentoId, ?int $unidadeId, string $titulo, string $texto, string $urgencia): void
    {
        Informe::firstOrCreate(['titulo' => $titulo], [
            'departamento_id' => $departamentoId, 'unidade_id' => $unidadeId, 'formato' => 'texto', 'texto' => $texto,
            'urgencia' => $urgencia, 'inicio' => now()->subDay(), 'fim' => now()->addDays(6),
        ]);
    }
}
