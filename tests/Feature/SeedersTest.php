<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Departamento;
use App\Models\Horario;
use App\Models\Informe;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\TesteDcetSeeder;
use Database\Seeders\UnebSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_uneb_tem_26_campi_e_32_departamentos(): void
    {
        $this->seed(UnebSeeder::class);

        $this->assertSame(26, Campus::count());
        $this->assertSame(32, Departamento::count());
    }

    public function test_uneb_contem_dcet1_e_dcet2(): void
    {
        $this->seed(UnebSeeder::class);

        $this->assertSame('Salvador', Departamento::where('slug', 'dcet1')->first()->campus->cidade);
        $this->assertSame('Alagoinhas', Departamento::where('slug', 'dcet2')->first()->campus->cidade);
        $this->assertNotNull(Departamento::where('slug', 'opara')->first());
    }

    public function test_slugs_da_uneb_nao_colidem_com_rotas_do_sistema(): void
    {
        $this->seed(UnebSeeder::class);

        $this->assertSame(0, Departamento::whereIn('slug', Departamento::SLUGS_RESERVADOS)->count());
    }

    public function test_uneb_pode_rodar_duas_vezes(): void
    {
        $this->seed(UnebSeeder::class);
        $this->seed(UnebSeeder::class);

        $this->assertSame(32, Departamento::count());
    }

    public function test_dados_de_teste_nao_rodam_em_producao(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(\RuntimeException::class);
        (new TesteDcetSeeder)->run();
    }

    public function test_dados_de_teste_criam_um_usuario_de_cada_perfil(): void
    {
        $this->seed(TesteDcetSeeder::class);

        foreach (User::PERFIS as $perfil) {
            $this->assertTrue(User::where('role', $perfil)->exists(), $perfil);
        }

        $this->assertGreaterThan(0, Horario::count());
        $this->assertTrue(Informe::whereNull('departamento_id')->exists());
        $this->assertTrue(Informe::where('urgencia', 'urgente')->exists());
    }

    public function test_instalacao_nova_comeca_sem_departamentos(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Departamento::count());
    }

    public function test_demo_funciona_na_estrutura_nova(): void
    {
        $this->seed(DemoSeeder::class);

        $this->getJson('/api/demo/config')->assertOk();
        $this->assertTrue(User::where('email', 'admin@iroyin.demo')->first()->isGestor());
        $this->assertGreaterThan(0, Horario::count());
    }
}
