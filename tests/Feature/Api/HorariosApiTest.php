<?php

namespace Tests\Feature\Api;

use App\Models\Semestre;
use App\Models\Horario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorariosApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_horarios_returns_200(): void
    {
        $this->getJson('/api/horarios')
            ->assertStatus(200);
    }

    public function test_horarios_returns_empty_when_no_active_semester(): void
    {
        $this->getJson('/api/horarios')
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

        $semestre = Semestre::create(['nome' => '2026.1', 'ativo' => true]);

        Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana'  => $hoje,
            'disciplina'  => 'Cálculo I',
            'professor'   => 'Prof. Teste',
            'curso'       => 'Engenharia',
            'inicio'      => '08:00',
            'fim'         => '09:40',
            'sala'        => 'LAB 01',
        ]);

        $response = $this->getJson('/api/horarios');

        $response->assertStatus(200);

        if ($hoje !== 'domingo') {
            $response->assertJsonFragment(['disciplina' => 'Cálculo I']);
        }
    }

    public function test_horarios_ignores_inactive_semesters(): void
    {
        $semestre = Semestre::create(['nome' => '2025.2', 'ativo' => false]);

        Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana'  => 'segunda',
            'disciplina'  => 'Disciplina Inativa',
            'professor'   => 'Prof. Inativo',
            'curso'       => 'Curso',
            'inicio'      => '08:00',
            'fim'         => '09:40',
            'sala'        => null,
        ]);

        $this->getJson('/api/horarios')
            ->assertJson(['aulas' => []]);
    }

    public function test_horarios_structure_contains_required_fields(): void
    {
        $diasMap = [
            0 => 'domingo', 1 => 'segunda', 2 => 'terca',
            3 => 'quarta',  4 => 'quinta',  5 => 'sexta', 6 => 'sabado',
        ];
        $hoje = $diasMap[now()->dayOfWeek];

        if ($hoje === 'domingo') {
            $this->markTestSkipped('No schedule seeded for Sunday.');
        }

        $semestre = Semestre::create(['nome' => '2026.1', 'ativo' => true]);

        Horario::create([
            'semestre_id' => $semestre->id,
            'dia_semana'  => $hoje,
            'disciplina'  => 'Redes',
            'professor'   => 'Prof. A',
            'curso'       => 'CC',
            'inicio'      => '10:00',
            'fim'         => '11:40',
            'sala'        => 'LAB 02',
        ]);

        $this->getJson('/api/horarios')
            ->assertJsonStructure([
                'aulas' => [
                    '*' => ['disciplina', 'professor', 'curso', 'inicio', 'fim', 'sala'],
                ],
            ]);
    }
}
