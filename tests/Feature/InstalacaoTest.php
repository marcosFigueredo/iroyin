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

        $this->post('/login', ['email' => 'gestor', 'password' => 'iroyn@2027'])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_rodar_de_novo_nao_recria_nem_redefine_a_senha(): void
    {
        $this->seed(InstalacaoUnebSeeder::class);
        User::where('email', 'gestor')->first()->update(['password' => 'nova-senha-segura']);

        $this->seed(InstalacaoUnebSeeder::class);

        $this->assertSame(1, User::count());
        $this->post('/login', ['email' => 'gestor', 'password' => 'iroyn@2027']);
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

        $this->actingAs(User::first())->get('/dashboard')->assertSee(route('profile.edit'), false);
        $this->actingAs(User::first())->get(route('profile.edit'))->assertOk();
    }
}
