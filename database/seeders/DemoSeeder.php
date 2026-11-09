<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Configuracao;
use App\Models\Departamento;
use App\Models\Instituicao;
use App\Models\Noticia;
use App\Models\Semestre;
use App\Models\Horario;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds fictional demo data so anyone can run IROYIN locally
 * and see a fully working system without manual data entry.
 *
 * Admin credentials:
 *   Email:    admin@iroyin.demo
 *   Password: demo@2026
 */
class DemoSeeder extends Seeder
{
    private Departamento $departamento;

    public function run(): void
    {
        // A conta de demonstração tem senha conhecida: nunca em produção.
        if (app()->isProduction()) {
            throw new \RuntimeException('Os dados de demonstração não podem ser carregados em produção.');
        }

        $this->seedAdmin();
        $this->seedInstituicao();
        $this->seedConfiguracao();
        $this->seedEstrutura();
        $this->seedSemestre();
        $this->seedNoticias();
    }

    // ── Admin user ────────────────────────────────────────────────────────────

    private function seedAdmin(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@iroyin.demo'],
            [
                'name'     => 'Administrador Demo',
                'password' => Hash::make('demo@2026'),
                'role'     => 'gestor',
                'active'   => true,
            ]
        );
    }

    // ── Fictional institution ─────────────────────────────────────────────────

    private function seedInstituicao(): void
    {
        $inst = Instituicao::first();

        if (! $inst) {
            Instituicao::create([
                'nome'         => 'Instituto de Tecnologia e Inovação',
                'sigla'        => 'ITI',
                'cidade'       => 'Cidade Exemplo',
                'estado'       => 'EX',
                'texto_banner' => 'NOTÍCIAS',
            ]);
        }
    }

    // ── Display configuration ─────────────────────────────────────────────────

    private function seedConfiguracao(): void
    {
        $cfg = Configuracao::first();

        if (! $cfg) {
            Configuracao::create(['weather_api_key' => '']);
        }
    }

    // ── Fictional campus and department, served at /demo ──────────────────────

    private function seedEstrutura(): void
    {
        $campus = Campus::firstOrCreate(['nome' => 'Campus Demo'], ['cidade' => 'Salvador', 'estado' => 'BA']);

        $this->departamento = Departamento::firstOrCreate(['slug' => 'demo'], [
            'campus_id' => $campus->id, 'sigla' => 'DEMO', 'nome' => 'Departamento de Demonstração',
        ]);
    }

    private function curso(string $nome): int
    {
        return Unidade::firstOrCreate(
            ['departamento_id' => $this->departamento->id, 'nome' => $nome],
            ['tipo' => 'curso'],
        )->id;
    }

    // ── Semester + schedules for every day of the week ────────────────────────

    private function seedSemestre(): void
    {
        $semestre = Semestre::firstOrCreate(
            ['nome' => now()->year.'.1'],
            ['inicio' => now()->subMonth()->toDateString(), 'fim' => now()->addMonths(3)->toDateString()]
        );

        if (Horario::where('semestre_id', $semestre->id)->exists()) {
            return;
        }

        $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta'];

        $grades = [
            // Manhã
            ['inicio' => '07:00', 'fim' => '08:40', 'disciplina' => 'Cálculo I',                 'professor' => 'Ana Lima',          'curso' => 'Engenharia de Software',   'sala' => 'LAB 01'],
            ['inicio' => '07:00', 'fim' => '08:40', 'disciplina' => 'Algoritmos e Estruturas',   'professor' => 'Carlos Mendes',     'curso' => 'Ciência da Computação',    'sala' => 'LAB 02'],
            ['inicio' => '09:00', 'fim' => '10:40', 'disciplina' => 'Banco de Dados I',          'professor' => 'Maria Souza',       'curso' => 'Sistemas de Informação',   'sala' => 'LAB 03'],
            ['inicio' => '09:00', 'fim' => '10:40', 'disciplina' => 'Redes de Computadores',     'professor' => 'João Ferreira',     'curso' => 'Ciência da Computação',    'sala' => 'LAB 01'],
            ['inicio' => '11:00', 'fim' => '12:40', 'disciplina' => 'Engenharia de Software',    'professor' => 'Paula Rocha',       'curso' => 'Sistemas de Informação',   'sala' => 'LAB 04'],
            ['inicio' => '11:00', 'fim' => '12:40', 'disciplina' => 'Programação Orientada a Objetos', 'professor' => 'Roberto Nunes', 'curso' => 'Engenharia de Software', 'sala' => 'LAB 02'],
            // Tarde
            ['inicio' => '14:00', 'fim' => '15:40', 'disciplina' => 'Inteligência Artificial',   'professor' => 'Fernanda Costa',   'curso' => 'Ciência da Computação',    'sala' => 'LAB 05'],
            ['inicio' => '14:00', 'fim' => '15:40', 'disciplina' => 'Sistemas Operacionais',     'professor' => 'Tiago Alves',      'curso' => 'Sistemas de Informação',   'sala' => 'LAB 01'],
            ['inicio' => '16:00', 'fim' => '17:40', 'disciplina' => 'Desenvolvimento Web',       'professor' => 'Juliana Matos',    'curso' => 'Engenharia de Software',   'sala' => 'LAB 03'],
            ['inicio' => '16:00', 'fim' => '17:40', 'disciplina' => 'Compiladores',              'professor' => 'André Barbosa',    'curso' => 'Ciência da Computação',    'sala' => 'LAB 02'],
            // Noite
            ['inicio' => '19:00', 'fim' => '20:40', 'disciplina' => 'Segurança da Informação',   'professor' => 'Camila Torres',    'curso' => 'Sistemas de Informação',   'sala' => 'LAB 04'],
            ['inicio' => '19:00', 'fim' => '20:40', 'disciplina' => 'Machine Learning',          'professor' => 'Bruno Carvalho',   'curso' => 'Ciência da Computação',    'sala' => 'LAB 05'],
            ['inicio' => '21:00', 'fim' => '22:30', 'disciplina' => 'Gestão de Projetos de TI',  'professor' => 'Renata Freitas',   'curso' => 'Engenharia de Software',   'sala' => 'LAB 01'],
            ['inicio' => '21:00', 'fim' => '22:30', 'disciplina' => 'Computação em Nuvem',       'professor' => 'Lucas Ribeiro',    'curso' => 'Sistemas de Informação',   'sala' => 'LAB 03'],
        ];

        foreach ($dias as $dia) {
            foreach ($grades as $h) {
                Horario::create(array_merge(Arr::except($h, 'curso'), [
                    'unidade_id'  => $this->curso($h['curso']),
                    'semestre_id' => $semestre->id,
                    'dia_semana'  => $dia,
                ]));
            }
        }

        // Sábado — turno reduzido
        $sabado = [
            ['inicio' => '08:00', 'fim' => '11:40', 'disciplina' => 'Tópicos em IA',             'professor' => 'Fernanda Costa',   'curso' => 'Ciência da Computação',    'sala' => 'LAB 05'],
            ['inicio' => '08:00', 'fim' => '11:40', 'disciplina' => 'Trabalho de Conclusão I',   'professor' => 'Paula Rocha',      'curso' => 'Sistemas de Informação',   'sala' => 'LAB 04'],
            ['inicio' => '13:00', 'fim' => '16:40', 'disciplina' => 'Trabalho de Conclusão II',  'professor' => 'Maria Souza',      'curso' => 'Engenharia de Software',   'sala' => 'LAB 02'],
        ];

        foreach ($sabado as $h) {
            Horario::create(array_merge(Arr::except($h, 'curso'), [
                    'unidade_id'  => $this->curso($h['curso']),
                'semestre_id' => $semestre->id,
                'dia_semana'  => 'sabado',
            ]));
        }
    }

    // ── Fictional news items (no images) ─────────────────────────────────────

    private function seedNoticias(): void
    {
        if (Noticia::exists()) {
            return;
        }

        $noticias = [
            [
                'titulo' => 'ITI abre inscrições para o Programa de Iniciação Científica 2026',
                'fonte'  => 'ITI Notícias',
                'link'   => null,
            ],
            [
                'titulo' => 'Semana de Computação reúne pesquisadores de todo o país no ITI',
                'fonte'  => 'ITI Notícias',
                'link'   => null,
            ],
            [
                'titulo' => 'Novo laboratório de Inteligência Artificial é inaugurado no Departamento',
                'fonte'  => 'ITI Notícias',
                'link'   => null,
            ],
            [
                'titulo' => 'Estudantes do ITI vencem Maratona de Programação Regional 2026',
                'fonte'  => 'ITI Notícias',
                'link'   => null,
            ],
            [
                'titulo' => 'Edital para bolsas de extensão tecnológica está aberto até 15 de julho',
                'fonte'  => 'ITI Notícias',
                'link'   => null,
            ],
        ];

        foreach ($noticias as $n) {
            Noticia::create([
                'titulo' => $n['titulo'],
                'fonte'  => $n['fonte'],
                'link'   => $n['link'],
                'imagem' => null,
                'inicio' => now(),
                'fim'    => now()->addDays(60),
            ]);
        }
    }
}
