<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class InformeTelaTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    public function test_formulario_carrega_o_mesmo_css_e_render_do_monitor(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))->get(route('admin.informes.create'))
            ->assertOk()
            ->assertSee('/assets/css/informe.css', false)
            ->assertSee('/assets/js/temas.js', false)
            ->assertSee('/assets/js/informeRender.js', false);
    }

    public function test_formulario_tem_limites_70_e_160(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))->get(route('admin.informes.create'))
            ->assertSee('maxlength="70"', false)
            ->assertSee('maxlength="160"', false);
    }

    public function test_unidade_nao_ve_opcao_de_imagem(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('unidade', $d, $this->criaCurso($d)))->get(route('admin.informes.create'))
            ->assertOk()
            ->assertDontSee('value="imagem"', false);
    }

    public function test_departamento_ve_opcao_de_imagem(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))->get(route('admin.informes.create'))
            ->assertSee('value="imagem"', false);
    }

    public function test_formulario_nao_tem_seletor_de_alcance(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))->get(route('admin.informes.create'))
            ->assertDontSee('name="alcance"', false);
    }

    public function test_listagem_escapa_titulo_com_script(): void
    {
        $d = $this->criaDepartamento();
        $this->criaInforme($d, ['titulo' => '<script>alert(1)</script>']);

        $this->actingAs($this->criaUsuario('departamento', $d))->get(route('admin.informes.index'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_gestor_ve_o_menu_de_informes_sem_escolher_departamento(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))->get('/dashboard')
            ->assertOk()
            ->assertSee(route('admin.informes.index'), false);
    }

    public function test_cadastro_manual_tem_botoes_de_noticia_e_informe(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))->get(route('admin.noticias.index'))
            ->assertOk()
            ->assertSee(route('admin.noticias.create'), false)
            ->assertSee(route('admin.informes.create'), false);
    }

    public function test_gestor_abre_novo_informe_sem_escolher_departamento(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))->get(route('admin.informes.create'))
            ->assertOk()
            ->assertSee('REITORIA');
    }

    public function test_tela_de_edicao_abre(): void
    {
        $d = $this->criaDepartamento();
        $informe = $this->criaInforme($d);

        $this->actingAs($this->criaUsuario('departamento', $d))->get(route('admin.informes.edit', $informe))->assertOk();
    }

    public function test_reitoria_abre_o_formulario(): void
    {
        $this->actingAs($this->criaUsuario('reitoria'))->get(route('admin.informes.create'))->assertOk()->assertSee('REITORIA');
    }
}
