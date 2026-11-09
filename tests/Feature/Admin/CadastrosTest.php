<?php

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\Departamento;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class CadastrosTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    private function campus(): Campus
    {
        return Campus::create(['numero' => 2, 'nome' => 'Alagoinhas', 'cidade' => 'Alagoinhas', 'estado' => 'BA']);
    }

    private function novoDepartamento(array $extra = []): array
    {
        return array_merge(['campus_id' => $this->campus()->id, 'sigla' => 'DCET', 'nome' => 'Ciências Exatas'], $extra);
    }

    public function test_gestor_cria_departamento_e_slug_e_gerado(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))
            ->post(route('admin.departamentos.store'), $this->novoDepartamento())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('departamentos', ['slug' => 'dcet2']);
    }

    public function test_slug_reservado_e_recusado(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))
            ->post(route('admin.departamentos.store'), $this->novoDepartamento(['slug' => 'admin']))
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, Departamento::count());
    }

    public function test_slug_repetido_e_recusado(): void
    {
        $this->criaDepartamento('dcet2');

        $this->actingAs($this->criaUsuario('gestor'))
            ->post(route('admin.departamentos.store'), $this->novoDepartamento())
            ->assertSessionHasErrors('slug');
    }

    public function test_departamento_nao_cria_departamento(): void
    {
        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento()))
            ->post(route('admin.departamentos.store'), $this->novoDepartamento(['sigla' => 'XYZ']))
            ->assertForbidden();
    }

    public function test_departamento_cria_curso_e_secretario(): void
    {
        $d = $this->criaDepartamento();
        $gabinete = $this->criaUsuario('departamento', $d);

        $this->actingAs($gabinete)->post(route('admin.unidades.store'), [
            'tipo' => 'curso', 'nome' => 'Matemática',
        ])->assertSessionHasNoErrors();

        $curso = Unidade::where('nome', 'Matemática')->firstOrFail();
        $this->assertSame($d->id, $curso->departamento_id);

        $this->actingAs($gabinete)->post(route('admin.users.store'), [
            'name' => 'Sec', 'email' => 'sec@uneb.br', 'role' => 'unidade', 'unidade_id' => $curso->id,
            'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ])->assertSessionHasNoErrors();

        $sec = User::where('email', 'sec@uneb.br')->firstOrFail();
        $this->assertSame($curso->id, $sec->unidade_id);
        $this->assertSame($d->id, $sec->departamento_id);
    }

    public function test_departamento_nao_acessa_unidade_de_outro(): void
    {
        $curso = $this->criaCurso($this->criaDepartamento('dcet1'));
        $gabinete = $this->criaUsuario('departamento', $this->criaDepartamento('dcet2'));

        $this->actingAs($gabinete)->get(route('admin.unidades.edit', $curso))->assertNotFound();
        $this->actingAs($gabinete)->put(route('admin.unidades.update', $curso), ['tipo' => 'curso', 'nome' => 'X'])->assertNotFound();
        $this->actingAs($gabinete)->delete(route('admin.unidades.destroy', $curso))->assertNotFound();

        $this->assertNotNull($curso->fresh());
    }

    public function test_secretaria_nao_cadastra_unidade(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('unidade', $d, $this->criaCurso($d)))
            ->post(route('admin.unidades.store'), ['tipo' => 'curso', 'nome' => 'X'])
            ->assertForbidden();
    }

    public function test_excluir_curso_com_usuarios_e_bloqueado(): void
    {
        $d = $this->criaDepartamento();
        $curso = $this->criaCurso($d);
        $this->criaUsuario('unidade', $d, $curso);

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->delete(route('admin.unidades.destroy', $curso))
            ->assertSessionHas('error');

        $this->assertNotNull($curso->fresh());
    }

    public function test_excluir_curso_com_horarios_e_bloqueado(): void
    {
        $d = $this->criaDepartamento();
        $curso = $this->criaCurso($d);
        \App\Models\Horario::create([
            'semestre_id' => $this->criaSemestreVigente()->id, 'unidade_id' => $curso->id, 'dia_semana' => 'segunda',
            'disciplina' => 'A', 'professor' => 'P', 'inicio' => '08:00', 'fim' => '09:00',
        ]);

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->delete(route('admin.unidades.destroy', $curso))
            ->assertSessionHas('error');

        $this->assertNotNull($curso->fresh());
    }

    public function test_curso_com_horarios_nao_vira_laboratorio(): void
    {
        $d = $this->criaDepartamento();
        $curso = $this->criaCurso($d);
        \App\Models\Horario::create([
            'semestre_id' => $this->criaSemestreVigente()->id, 'unidade_id' => $curso->id, 'dia_semana' => 'segunda',
            'disciplina' => 'A', 'professor' => 'P', 'inicio' => '08:00', 'fim' => '09:00',
        ]);

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->put(route('admin.unidades.update', $curso), ['tipo' => 'laboratorio', 'nome' => $curso->nome, 'ativo' => 1])
            ->assertSessionHasErrors('tipo');

        $this->assertSame('curso', $curso->fresh()->tipo);
    }

    public function test_departamento_nao_cria_usuario_gestor(): void
    {
        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento()))
            ->post(route('admin.users.store'), [
                'name' => 'X', 'email' => 'x@uneb.br', 'role' => 'gestor',
                'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
            ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'x@uneb.br']);
    }

    public function test_departamento_nao_vincula_secretario_a_curso_de_outro(): void
    {
        $alheio = $this->criaCurso($this->criaDepartamento('dcet1'));

        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento('dcet2')))
            ->post(route('admin.users.store'), [
                'name' => 'X', 'email' => 'x@uneb.br', 'role' => 'unidade', 'unidade_id' => $alheio->id,
                'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
            ])->assertSessionHasErrors('unidade_id');
    }

    public function test_departamento_nao_edita_usuario_de_outro(): void
    {
        $outro = $this->criaDepartamento('dcet1');
        $alvo = $this->criaUsuario('unidade', $outro, $this->criaCurso($outro));

        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento('dcet2')))
            ->get(route('admin.users.edit', $alvo))
            ->assertNotFound();
    }

    public function test_departamento_altera_tema_e_tempos_proprios(): void
    {
        $d = $this->criaDepartamento();

        $this->actingAs($this->criaUsuario('departamento', $d))
            ->put(route('admin.departamento-config.update'), [
                'tema' => 'verde', 'duracao_horarios' => 90, 'duracao_noticia' => 20,
            ])->assertSessionHasNoErrors();

        $d->refresh();
        $this->assertSame('verde', $d->tema);
        $this->assertSame(90, $d->duracao_horarios);
    }

    public function test_tema_invalido_e_recusado(): void
    {
        $this->actingAs($this->criaUsuario('departamento', $this->criaDepartamento()))
            ->put(route('admin.departamento-config.update'), [
                'tema' => 'rosa', 'duracao_horarios' => 90, 'duracao_noticia' => 20,
            ])->assertSessionHasErrors('tema');
    }

    public function test_gestor_sem_departamento_escolhido_nao_cadastra_unidade(): void
    {
        $this->actingAs($this->criaUsuario('gestor'))
            ->post(route('admin.unidades.store'), ['tipo' => 'curso', 'nome' => 'X'])
            ->assertRedirect(route('admin.departamentos.index'));

        $this->assertSame(0, Unidade::count());
    }

    public function test_telas_de_cadastro_abrem(): void
    {
        $d = $this->criaDepartamento();
        $g = $this->criaUsuario('gestor');

        foreach (['admin.campi.index', 'admin.departamentos.index', 'admin.users.index', 'admin.users.create'] as $r) {
            $this->actingAs($g)->get(route($r))->assertOk();
        }

        $gabinete = $this->criaUsuario('departamento', $d);
        foreach (['admin.unidades.index', 'admin.departamento-config.edit', 'admin.users.index', 'admin.users.create'] as $r) {
            $this->actingAs($gabinete)->get(route($r))->assertOk();
        }
    }
}
