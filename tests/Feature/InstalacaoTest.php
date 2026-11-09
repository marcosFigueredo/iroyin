<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\User;
use Database\Seeders\InstalacaoUnebSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstalacaoTest extends TestCase
{
    use RefreshDatabase;

    private const SENHA = 'senha-inicial-de-teste';

    protected function setUp(): void
    {
        parent::setUp();
        $_ENV['GESTOR_SENHA_INICIAL'] = $_SERVER['GESTOR_SENHA_INICIAL'] = self::SENHA;
    }

    protected function tearDown(): void
    {
        unset($_ENV['GESTOR_SENHA_INICIAL'], $_SERVER['GESTOR_SENHA_INICIAL']);
        parent::tearDown();
    }

    public function test_instalacao_exige_a_senha_inicial(): void
    {
        unset($_ENV['GESTOR_SENHA_INICIAL'], $_SERVER['GESTOR_SENHA_INICIAL']);

        $this->expectException(\RuntimeException::class);
        (new InstalacaoUnebSeeder)->run();
    }

    public function test_primeiro_acesso_obriga_a_troca_da_senha(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);

        $this->actingAs(User::first())->get('/admin/users')->assertRedirect(route('profile.edit'));
        $this->actingAs(User::first())->get(route('profile.edit'))->assertOk();
    }

    public function test_depois_de_trocar_a_senha_o_painel_libera(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);
        $gestor = User::first();

        $this->actingAs($gestor)->put(route('password.update'), [
            'current_password' => self::SENHA,
            'password' => 'uma-senha-bem-nova', 'password_confirmation' => 'uma-senha-bem-nova',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($gestor->fresh()->trocar_senha);
        $this->actingAs($gestor->fresh())->get('/admin/users')->assertOk();
    }

    public function test_usuario_comum_nao_e_obrigado_a_trocar(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin/users')->assertOk();
    }

    public function test_instalacao_cria_apenas_o_gestor_e_os_departamentos(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertTrue(User::where('email', 'gestor')->first()->isGestor());
        $this->assertSame(32, Departamento::count());
    }

    public function test_gestor_entra_com_login_sem_arroba(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);

        $this->post('/login', ['email' => 'gestor', 'password' => self::SENHA])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_rodar_de_novo_nao_recria_nem_redefine_a_senha(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);
        User::where('email', 'gestor')->first()->update(['password' => 'nova-senha-segura']);

        $this->seed(InstalacaoUnebSeeder::class);

        $this->assertSame(1, User::count());
        $this->post('/login', ['email' => 'gestor', 'password' => self::SENHA]);
        $this->assertGuest();
    }

    public function test_instalacao_funciona_em_producao(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        (new InstalacaoUnebSeeder)->run();

        $this->assertSame(1, User::count());
    }

    public function test_menu_leva_ao_perfil_para_trocar_login_e_senha(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);

        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertSee(route('profile.edit'), false);
    }
}
