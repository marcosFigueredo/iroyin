<?php

namespace Tests\Feature\Api;

use App\Models\Horario;
use App\Models\Semestre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class HorariosApiTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    protected \App\Models\Departamento $departamento;
    protected \App\Models\Unidade $curso;

    protected function setUp(): void
    {
        parent::setUp();
        $this->departamento = $this->criaDepartamento('dcet2');
        $this->curso = $this->criaCurso($this->departamento);

        // Evita falhas dependentes do calendário (a aplicação não cadastra
        // aulas aos domingos).
        $this->travelTo(Carbon::parse('2026-07-20 10:00:00'));
    }

    public function test_horarios_returns_200(): void
    {
        $this->getJson('/api/dcet2/horarios')
            ->assertStatus(200);
    }

    public function test_horarios_returns_empty_when_no_active_semester(): void
    {
        $this->getJson('/api/dcet2/horarios')
            ->assertStatus(200)
            ->assertJson(['aulas' => []]);
    }

    public function test_horarios_returns_todays_schedule(): void
    {
        $diasMap = [
            0 => 'domingo', 1 => 'segunda', 2 => 'terca',
            3 => 'quarta',  4 => 'quinta',  5 => 'sexta', 6 => 'sabado',
        ];
        $hoje = $diasMap[now()->dayOfWeek];

        $semestre = Semestre::create(['nome' => '2026.1', 'inicio' => now()->subMonth(), 'fim' => now()->addMonth()]);

        Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana' => $hoje,
            'disciplina' => 'Cálculo I',
            'professor' => 'Prof. Teste',
            'unidade_id' => $this->curso->id,
            'inicio' => '08:00',
            'fim' => '09:40',
            'sala' => 'LAB 01',
        ]);

        $response = $this->getJson('/api/dcet2/horarios');

        $response->assertStatus(200);

        if ($hoje !== 'domingo') {
            $response->assertJsonFragment(['disciplina' => 'Cálculo I']);
        }
    }

    public function test_horarios_ignores_inactive_semesters(): void
    {
        $semestre = Semestre::create(['nome' => '2025.2', 'inicio' => now()->subYear(), 'fim' => now()->subMonths(6)]);

        Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana' => 'segunda',
            'disciplina' => 'Disciplina Inativa',
            'professor' => 'Prof. Inativo',
            'unidade_id' => $this->curso->id,
            'inicio' => '08:00',
            'fim' => '09:40',
            'sala' => null,
        ]);

        $this->getJson('/api/dcet2/horarios')
            ->assertJson(['aulas' => []]);
    }

    public function test_horarios_structure_contains_required_fields(): void
    {
        $diasMap = [
            0 => 'domingo', 1 => 'segunda', 2 => 'terca',
            3 => 'quarta',  4 => 'quinta',  5 => 'sexta', 6 => 'sabado',
        ];
        $hoje = $diasMap[now()->dayOfWeek];

        $semestre = Semestre::create(['nome' => '2026.1', 'inicio' => now()->subMonth(), 'fim' => now()->addMonth()]);

        Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana' => $hoje,
            'disciplina' => 'Redes',
            'professor' => 'Prof. A',
            'unidade_id' => $this->curso->id,
            'inicio' => '10:00',
            'fim' => '11:40',
            'sala' => 'LAB 02',
        ]);

        $this->getJson('/api/dcet2/horarios')
            ->assertJsonStructure([
                'aulas' => [
                    '*' => ['disciplina', 'professor', 'curso', 'inicio', 'fim', 'sala'],
                ],
            ]);
    }

    public function test_horarios_enviam_a_semana_para_funcionar_sem_rede(): void
    {
        $semestre = $this->criaSemestreVigente();
        $outro = $this->criaCurso($this->criaDepartamento('dcet1'));

        foreach ([['segunda', 'Cálculo', $this->curso], ['terca', 'Redes', $this->curso], ['terca', 'De outro departamento', $outro]] as [$dia, $disciplina, $curso]) {
            Horario::create([
                'semestre_id' => $semestre->id, 'dia_semana' => $dia, 'disciplina' => $disciplina,
                'professor' => 'P', 'unidade_id' => $curso->id, 'inicio' => '08:00', 'fim' => '09:40', 'sala' => 'L1',
            ]);
        }

        $json = $this->getJson('/api/dcet2/horarios')->assertOk()->json();

        $this->assertSame(['Cálculo'], array_column($json['aulas'], 'disciplina'));
        $this->assertSame(['Cálculo'], array_column($json['semana']['segunda'], 'disciplina'));
        $this->assertSame(['Redes'], array_column($json['semana']['terca'], 'disciplina'));
        $this->assertSame($semestre->fim->toDateString(), $json['semestre_fim']);
    }
}
