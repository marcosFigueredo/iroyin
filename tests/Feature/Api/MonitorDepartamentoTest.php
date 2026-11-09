<?php

namespace Tests\Feature\Api;

use App\Models\Horario;
use App\Models\Unidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class MonitorDepartamentoTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2027-05-10 10:00:00')); // segunda-feira
    }

    private function aula($semestre, Unidade $curso, string $disciplina, string $dia = 'segunda'): Horario
    {
        return Horario::create([
            'semestre_id' => $semestre->id, 'unidade_id' => $curso->id, 'dia_semana' => $dia,
            'disciplina' => $disciplina, 'professor' => 'P', 'inicio' => '08:00', 'fim' => '09:40', 'sala' => 'L1',
        ]);
    }

    private function dadosAula(array $extra = []): array
    {
        return array_merge([
            'dia_semana' => 'segunda', 'disciplina' => 'Redes', 'professor' => 'P',
            'inicio' => '08:00', 'fim' => '09:40', 'sala' => 'L1',
        ], $extra);
    }

    public function test_monitor_mostra_so_aulas_do_proprio_departamento(): void
    {
        $s = $this->criaSemestreVigente();
        $d1 = $this->criaDepartamento('dcet1');
        $d2 = $this->criaDepartamento('dcet2');
        $this->aula($s, $this->criaCurso($d1), 'Aula de Salvador');
        $this->aula($s, $this->criaCurso($d2), 'Aula de Alagoinhas');

        $this->getJson('/api/dcet2/horarios')
            ->assertOk()
            ->assertJsonFragment(['disciplina' => 'Aula de Alagoinhas', 'curso' => 'Sistemas de Informação'])
            ->assertJsonMissing(['disciplina' => 'Aula de Salvador']);
    }

    public function test_slug_inexistente_retorna_404(): void
    {
        foreach (['config', 'clima', 'horarios', 'noticias', 'editais'] as $r) {
            $this->getJson("/api/nao-existe/{$r}")->assertNotFound();
        }
    }

    public function test_departamento_inativo_retorna_404(): void
    {
        $this->criaDepartamento('dcet2')->update(['ativo' => false]);

        $this->getJson('/api/dcet2/config')->assertNotFound();
    }

    public function test_config_traz_cabecalho_e_url_acessivel_do_departamento(): void
    {
        $this->criaDepartamento('dcet2')->update(['nome' => 'Ciências Exatas', 'tema' => 'verde', 'duracao_noticia' => 20]);

        $this->getJson('/api/dcet2/config')
            ->assertOk()
            ->assertJsonPath('slug', 'dcet2')
            ->assertJsonPath('cabecalho', 'DCET2 · CIÊNCIAS EXATAS · ALAGOINHAS')
            ->assertJsonPath('tema', 'verde')
            ->assertJsonPath('duracao_noticia', 20)
            ->assertJsonPath('url_acessivel', url('/dcet2/acessivel'));
    }

    public function test_fora_de_semestre_nao_retorna_aulas(): void
    {
        $s = $this->criaSemestreVigente();
        $d = $this->criaDepartamento('dcet2');
        $this->aula($s, $this->criaCurso($d), 'Aula');

        $this->travelTo(Carbon::parse('2027-12-20 10:00:00')); // segunda-feira, fora do período

        $this->getJson('/api/dcet2/horarios')->assertOk()->assertJsonPath('aulas', []);
    }

    public function test_laboratorio_nao_cadastra_horario(): void
    {
        $s = $this->criaSemestreVigente();
        $d = $this->criaDepartamento('dcet2');
        $lab = Unidade::create(['departamento_id' => $d->id, 'tipo' => 'laboratorio', 'nome' => 'Lab']);

        $this->actingAs($this->criaUsuario('unidade', $d, $lab))
            ->post(route('admin.semestres.horarios.store', $s), $this->dadosAula())
            ->assertForbidden();

        $this->assertSame(0, Horario::count());
    }

    public function test_departamento_nao_vincula_horario_a_laboratorio(): void
    {
        $s = $this->criaSemestreVigente();
        $d = $this->criaDepartamento('dcet2');
        $lab = Unidade::create(['departamento_id' => $d->id, 'tipo' => 'laboratorio', 'nome' => 'Lab']);

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.semestres.horarios.store', $s), $this->dadosAula(['unidade_id' => $lab->id]))
            ->assertSessionHasErrors('unidade_id');
    }

    public function test_secretaria_lanca_horario_no_proprio_curso(): void
    {
        $s = $this->criaSemestreVigente();
        $d = $this->criaDepartamento('dcet2');
        $curso = $this->criaCurso($d);
        $outro = $this->criaCurso($d, 'Matemática');

        $this->actingAs($this->criaUsuario('unidade', $d, $curso))
            ->post(route('admin.semestres.horarios.store', $s), $this->dadosAula(['unidade_id' => $outro->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame($curso->id, Horario::first()->unidade_id);
    }

    public function test_secretaria_nao_altera_horario_de_outro_curso(): void
    {
        $s = $this->criaSemestreVigente();
        $d = $this->criaDepartamento('dcet2');
        $meu = $this->criaCurso($d);
        $alheio = $this->aula($s, $this->criaCurso($d, 'Matemática'), 'Álgebra');
        $u = $this->criaUsuario('unidade', $d, $meu);

        $this->actingAs($u)->get(route('admin.semestres.horarios.edit', [$s, $alheio]))->assertNotFound();
        $this->actingAs($u)->delete(route('admin.semestres.horarios.destroy', [$s, $alheio]))->assertNotFound();

        $this->assertNotNull($alheio->fresh());
    }

    public function test_departamento_nao_ve_horario_de_outro_na_lista(): void
    {
        $s = $this->criaSemestreVigente();
        $d1 = $this->criaDepartamento('dcet1');
        $d2 = $this->criaDepartamento('dcet2');
        $this->aula($s, $this->criaCurso($d1), 'Aula de Salvador');
        $this->aula($s, $this->criaCurso($d2), 'Aula de Alagoinhas');

        $this->actingAs($this->criaUsuario('departamento', $d2))
            ->get(route('admin.semestres.horarios.index', $s))
            ->assertOk()->assertSee('Aula de Alagoinhas')->assertDontSee('Aula de Salvador');
    }

    public function test_reitoria_nao_acessa_horarios(): void
    {
        $s = $this->criaSemestreVigente();

        $this->actingAs($this->criaUsuario('reitoria'))
            ->get(route('admin.semestres.horarios.index', $s))
            ->assertForbidden();
    }

    public function test_csv_com_curso_desconhecido_e_rejeitado(): void
    {
        $s = $this->criaSemestreVigente();
        $d = $this->criaDepartamento('dcet2');
        $this->criaCurso($d, 'Matemática');

        $csv = "dia,disciplina,professor,curso,inicio,fim,sala\n"
             . "segunda,Cálculo,P,Matemática,07:00,08:40,L1\n"
             . "terca,Física,P,Curso Inventado,07:00,08:40,L2\n";

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.semestres.horarios.importar', $s), [
                'csv' => UploadedFile::fake()->createWithContent('h.csv', $csv),
            ])->assertSessionHas('error');

        $this->assertSame(0, Horario::count());
    }
}
