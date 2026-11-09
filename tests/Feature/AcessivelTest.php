<?php

namespace Tests\Feature;

use App\Models\Horario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class AcessivelTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2027-05-10 10:00:00')); // segunda-feira
    }

    public function test_mostra_aulas_so_do_departamento(): void
    {
        $s = $this->criaSemestreVigente();
        $d1 = $this->criaDepartamento('dcet1');
        $d2 = $this->criaDepartamento('dcet2');

        foreach ([[$d1, 'Aula de Salvador'], [$d2, 'Aula de Alagoinhas']] as [$d, $nome]) {
            Horario::create([
                'semestre_id' => $s->id, 'unidade_id' => $this->criaCurso($d)->id, 'dia_semana' => 'segunda',
                'disciplina' => $nome, 'professor' => 'P', 'inicio' => '08:00', 'fim' => '09:40', 'sala' => 'L1',
            ]);
        }

        $this->get('/dcet2/acessivel')->assertOk()->assertSee('Aula de Alagoinhas')->assertDontSee('Aula de Salvador');
    }

    public function test_lista_informes_na_integra_urgentes_primeiro(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $texto = str_repeat('palavra ', 19).'final';
        $this->criaInforme($d, ['titulo' => 'Normal', 'texto' => $texto]);
        $this->criaInforme($d, ['titulo' => 'Urgentíssimo', 'urgencia' => 'urgente']);

        $this->get('/dcet2/acessivel')
            ->assertOk()
            ->assertSee($texto)
            ->assertSeeInOrder(['Urgentíssimo', 'Normal']);
    }

    public function test_inclui_informes_da_reitoria(): void
    {
        $this->criaDepartamento('dcet2');
        $this->criaInforme(null, ['titulo' => 'Comunicado geral']);

        $this->get('/dcet2/acessivel')->assertSee('Comunicado geral')->assertSee('Reitoria');
    }

    public function test_nao_inclui_informe_de_outro_departamento(): void
    {
        $this->criaInforme($this->criaDepartamento('dcet1'), ['titulo' => 'De Salvador']);
        $this->criaDepartamento('dcet2');

        $this->get('/dcet2/acessivel')->assertDontSee('De Salvador');
    }

    public function test_titulo_com_script_e_escapado(): void
    {
        $this->criaInforme($this->criaDepartamento('dcet2'), ['titulo' => '<script>alert(1)</script>']);

        $this->get('/dcet2/acessivel')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_manifest_aponta_para_o_departamento(): void
    {
        $this->criaDepartamento('dcet2');

        $this->getJson('/dcet2/acessivel/manifest.json')
            ->assertOk()
            ->assertJsonPath('start_url', '/dcet2/acessivel')
            ->assertJsonPath('scope', '/dcet2/acessivel');
    }

    public function test_endereco_antigo_redireciona_para_a_lista(): void
    {
        $this->get('/acessivel')->assertRedirect('/');
    }

    public function test_departamento_inativo_retorna_404(): void
    {
        $this->criaDepartamento('dcet2')->update(['ativo' => false]);

        $this->get('/dcet2/acessivel')->assertNotFound();
    }
}
