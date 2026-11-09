<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class PerfisTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    public function test_setup_cria_gestor(): void
    {
        $this->post('/setup', [
            'name' => 'Gestora', 'email' => 'g@uneb.br',
            'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ]);

        $this->assertTrue(User::where('email', 'g@uneb.br')->first()->isGestor());
    }

    public function test_perfil_fora_da_lista_recebe_403(): void
    {
        $d = $this->criaDepartamento();

        foreach (['reitoria', 'departamento', 'unidade'] as $perfil) {
            $this->actingAs($this->criaUsuario($perfil, $d))->get('/admin/feeds')->assertForbidden();
            $this->actingAs($this->criaUsuario($perfil, $d))->get('/admin/noticias')->assertForbidden();
        }
    }

    public function test_gabinete_sem_departamento_nao_acessa_nada(): void
    {
        $sem = $this->criaUsuario('departamento');

        $this->actingAs($sem)->get('/admin/users')->assertForbidden();
        $this->actingAs($sem)->get('/admin/informes')->assertForbidden();
    }

    public function test_secretaria_sem_unidade_nao_acessa_nada(): void
    {
        $this->actingAs($this->criaUsuario('unidade', $this->criaDepartamento()))
            ->get('/admin/informes')
            ->assertForbidden();
    }

    public function test_gestor_acessa_conteudo_comum(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))->get('/admin/noticias')->assertOk();
    }

    public function test_departamento_ve_semestres_mas_nao_cria(): void
    {
        $u = $this->criaUsuario('departamento', $this->criaDepartamento());

        $this->actingAs($u)->get('/admin/semestres')->assertOk();
        $this->actingAs($u)->post('/admin/semestres', [
            'nome' => '2030.1', 'inicio' => '2030-03-01', 'fim' => '2030-07-01',
        ])->assertForbidden();
    }

    public function test_escopo_do_departamento_e_o_proprio(): void
    {
        $d = $this->criaDepartamento();

        $this->assertSame($d->id, $this->criaUsuario('departamento', $d)->escopoDepartamentoId());
    }

    public function test_gestor_opera_como_departamento(): void
    {
        $d = $this->criaDepartamento();
        $g = $this->criaUsuario('gestor');

        $this->assertNull($g->escopoDepartamentoId());

        $this->actingAs($g)->post(route('admin.operar-como'), ['departamento_id' => $d->id])->assertRedirect();
        $this->assertSame($d->id, $g->escopoDepartamentoId());

        $this->actingAs($g)->post(route('admin.operar-como'), ['departamento_id' => ''])->assertRedirect();
        $this->assertNull($g->escopoDepartamentoId());
    }

    public function test_departamento_nao_usa_operar_como(): void
    {
        $d = $this->criaDepartamento();
        $outro = $this->criaDepartamento('dcet1');

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->post(route('admin.operar-como'), ['departamento_id' => $outro->id])
            ->assertForbidden();
    }
}
