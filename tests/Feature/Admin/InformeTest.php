<?php

namespace Tests\Feature\Admin;

use App\Models\Informe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class InformeTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    protected function setUp(): void
    {
        parent::setUp();
        // Os uploads de teste não podem cair na pasta pública real.
        $this->app->usePublicPath(sys_get_temp_dir().'/iroyin-testes');
    }

    private function dados(array $extra = []): array
    {
        return array_merge([
            'formato' => 'texto', 'titulo' => 'Aula cancelada', 'texto' => 'Sem aula hoje.',
            'urgencia' => 'normal', 'inicio' => now()->toDateString(), 'fim' => now()->addDays(2)->toDateString(),
        ], $extra);
    }

    public function test_titulo_acima_de_70_e_recusado(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados(['titulo' => Str::repeat('a', 71)]))
            ->assertSessionHasErrors('titulo');
    }

    public function test_titulo_com_70_e_aceito(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados(['titulo' => Str::repeat('a', 70)]))
            ->assertSessionHasNoErrors();
    }

    public function test_texto_acima_de_160_e_recusado(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados(['texto' => Str::repeat('a', 161)]))
            ->assertSessionHasErrors('texto');
    }

    public function test_unidade_nao_publica_imagem(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('unidade', $d, $this->criaCurso($d)))
            ->post(route('admin.informes.store'), $this->dados([
                'formato' => 'imagem', 'imagem' => UploadedFile::fake()->image('a.png'),
            ]))->assertSessionHasErrors('formato');

        $this->assertSame(0, Informe::count());
    }

    public function test_departamento_publica_imagem(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados([
                'formato' => 'imagem', 'imagem' => UploadedFile::fake()->image('cartaz.png'),
            ]))->assertSessionHasNoErrors();

        $this->assertStringEndsWith('.png', Informe::first()->imagem);
    }

    private function pngFalso(string $nome): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nome, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
    }

    public function test_arquivo_php_disfarcado_de_imagem_e_recusado(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados(['formato' => 'imagem', 'imagem' => $this->pngFalso('x.php')]))
            ->assertSessionHasErrors('imagem');

        $this->assertSame(0, Informe::count());
    }

    public function test_arquivo_salvo_usa_extensao_real_do_conteudo(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados(['formato' => 'imagem', 'imagem' => $this->pngFalso('x.exe')]));

        $this->assertStringEndsWith('.png', Informe::firstOrFail()->imagem);
    }

    public function test_svg_e_recusado(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados([
                'formato' => 'imagem',
                'imagem'  => UploadedFile::fake()->createWithContent('a.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            ]))->assertSessionHasErrors('imagem');
    }

    public function test_reitoria_publica_informe_geral(): void
    {
        $this->actingAs($this->criaUsuario('reitoria'))
            ->post(route('admin.informes.store'), $this->dados())
            ->assertSessionHasNoErrors();

        $this->assertNull(Informe::first()->departamento_id);
    }

    public function test_origem_vem_do_usuario_e_nao_do_formulario(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $outro = $this->criaDepartamento('dcet1');
        $curso = $this->criaCurso($d);

        $this->actingAs($this->criaUsuario('unidade', $d, $curso))
            ->post(route('admin.informes.store'), $this->dados(['departamento_id' => $outro->id, 'unidade_id' => 999]));

        $informe = Informe::first();
        $this->assertSame($d->id, $informe->departamento_id);
        $this->assertSame($curso->id, $informe->unidade_id);
    }

    public function test_unidade_nao_publica_para_outra_unidade(): void
    {
        $d = $this->criaDepartamento();
        $alheio = $this->criaInforme($d, ['unidade_id' => $this->criaCurso($d, 'Matemática')->id]);

        $u = $this->criaUsuario('unidade', $d, $this->criaCurso($d));

        $this->actingAs($u)->put(route('admin.informes.update', $alheio), $this->dados())->assertNotFound();
        $this->actingAs($u)->delete(route('admin.informes.destroy', $alheio))->assertNotFound();

        $this->assertNotNull($alheio->fresh());
    }

    public function test_departamento_gerencia_informe_de_sua_unidade(): void
    {
        $d = $this->criaDepartamento();
        $informe = $this->criaInforme($d, ['unidade_id' => $this->criaCurso($d)->id]);

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->delete(route('admin.informes.destroy', $informe))->assertRedirect();

        $this->assertNull($informe->fresh());
    }

    public function test_departamento_nao_altera_informe_de_outro(): void
    {
        $alheio = $this->criaInforme($this->criaDepartamento('dcet1'));

        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento('dcet2')))
            ->put(route('admin.informes.update', $alheio), $this->dados())->assertNotFound();
    }

    public function test_departamento_nao_altera_informe_da_reitoria(): void
    {
        $geral = $this->criaInforme(null);

        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento()))
            ->delete(route('admin.informes.destroy', $geral))->assertNotFound();
    }

    public function test_urgente_com_mais_de_7_dias_e_recusado(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados([
                'urgencia' => 'urgente', 'fim' => now()->addDays(8)->toDateString(),
            ]))->assertSessionHasErrors('fim');
    }

    public function test_urgente_com_7_dias_e_aceito(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados([
                'urgencia' => 'urgente', 'fim' => now()->addDays(7)->toDateString(),
            ]))->assertSessionHasNoErrors();
    }

    public function test_secretaria_marca_urgente_no_proprio_departamento(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('unidade', $d, $this->criaCurso($d)))
            ->post(route('admin.informes.store'), $this->dados(['urgencia' => 'urgente']))
            ->assertSessionHasNoErrors();
    }

    public function test_fim_e_o_final_do_dia(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.informes.store'), $this->dados());

        $this->assertSame('23:59:59', Informe::first()->fim->format('H:i:s'));
    }

    public function test_excluir_curso_com_informes_e_bloqueado(): void
    {
        $d = $this->criaDepartamento();
        $curso = $this->criaCurso($d);
        $this->criaInforme($d, ['unidade_id' => $curso->id]);

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->delete(route('admin.unidades.destroy', $curso))
            ->assertSessionHas('error');

        $this->assertNotNull($curso->fresh());
    }
}
