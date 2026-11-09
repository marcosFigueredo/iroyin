<?php

namespace Tests\Feature\Admin;

use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Semestre;
use App\Models\Unidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class ImportacaoCsvTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    private const CABECALHO = "dia,disciplina,professor,curso,inicio,fim,sala\n";

    private Departamento $dep;
    private Unidade $curso;
    private Semestre $semestre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dep = $this->criaDepartamento();
        $this->curso = $this->criaCurso($this->dep, 'Matemática');
        $this->semestre = $this->criaSemestreVigente();
    }

    private function enviar(string $conteudo, ?\App\Models\User $usuario = null): TestResponse
    {
        return $this->actingAs($usuario ?? $this->criaUsuario('departamento', $this->dep))
            ->post(route('admin.semestres.horarios.importar', $this->semestre), [
                'csv' => UploadedFile::fake()->createWithContent('h.csv', $conteudo),
            ]);
    }

    public function test_arquivo_completo_salva_todos_os_horarios(): void
    {
        $this->enviar(self::CABECALHO
            . "segunda,Cálculo,Ana,Matemática,07:00,08:40,L1\n"
            . "terca,Álgebra,Rui,Matemática,09:00,10:40,L2\n")->assertSessionHas('success');

        $this->assertSame(2, Horario::count());
        $this->assertSame($this->curso->id, Horario::first()->unidade_id);
    }

    public function test_linha_incompleta_nao_salva_nada(): void
    {
        $this->enviar(self::CABECALHO
            . "segunda,Cálculo,Ana,Matemática,07:00,08:40,L1\n"
            . "terca,Álgebra,,Matemática,09:00,10:40,L2\n")
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Linha 3') && str_contains($m, 'professor'));

        $this->assertSame(0, Horario::count());
    }

    public function test_linha_com_colunas_a_menos_nao_salva_nada(): void
    {
        $this->enviar(self::CABECALHO
            . "segunda,Cálculo,Ana,Matemática,07:00,08:40,L1\n"
            . "terca,Álgebra\n")->assertSessionHas('error');

        $this->assertSame(0, Horario::count());
    }

    public function test_coluna_faltando_nao_salva_nada(): void
    {
        $this->enviar("dia,disciplina,professor,curso,inicio,fim\nsegunda,C,A,Matemática,07:00,08:40\n")
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'sala'));

        $this->assertSame(0, Horario::count());
    }

    public function test_horario_invalido_nao_salva_nada(): void
    {
        $this->enviar(self::CABECALHO . "segunda,C,A,Matemática,25:00,26:00,L1\n")->assertSessionHas('error');
        $this->enviar(self::CABECALHO . "segunda,C,A,Matemática,09:00,08:00,L1\n")->assertSessionHas('error');

        $this->assertSame(0, Horario::count());
    }

    public function test_arquivo_sem_aulas_e_recusado(): void
    {
        $this->enviar(self::CABECALHO)->assertSessionHas('error');
    }

    public function test_aceita_ponto_e_virgula_do_excel(): void
    {
        $this->enviar("dia;disciplina;professor;curso;inicio;fim;sala\nsegunda;Cálculo;Ana;Matemática;07:00;08:40;L1\n")
            ->assertSessionHas('success');

        $this->assertSame(1, Horario::count());
    }

    public function test_aceita_acentos_salvos_em_latin1(): void
    {
        $this->enviar(mb_convert_encoding(self::CABECALHO . "segunda,Cálculo,Ana,Matemática,07:00,08:40,L1\n", 'ISO-8859-1', 'UTF-8'))
            ->assertSessionHas('success');

        $this->assertSame('Cálculo', Horario::first()->disciplina);
    }

    public function test_reenviar_o_mesmo_arquivo_nao_duplica(): void
    {
        $csv = self::CABECALHO . "segunda,Cálculo,Ana,Matemática,07:00,08:40,L1\n";

        $this->enviar($csv);
        $this->enviar($csv);

        $this->assertSame(1, Horario::count());
    }

    public function test_secretaria_nao_importa_aula_de_outro_curso(): void
    {
        $this->criaCurso($this->dep, 'Física');
        $secretaria = $this->criaUsuario('unidade', $this->dep, $this->curso);

        $this->enviar(self::CABECALHO . "segunda,Mecânica,Ana,Física,07:00,08:40,L1\n", $secretaria)->assertSessionHas('error');

        $this->assertSame(0, Horario::count());
    }

    public function test_secretaria_importa_o_proprio_curso(): void
    {
        $secretaria = $this->criaUsuario('unidade', $this->dep, $this->curso);

        $this->enviar(self::CABECALHO . "segunda,Cálculo,Ana,Matemática,07:00,08:40,L1\n", $secretaria)->assertSessionHas('success');

        $this->assertSame(1, Horario::count());
    }
}
