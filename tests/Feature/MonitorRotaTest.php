<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class MonitorRotaTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    public function test_raiz_lista_departamentos_ativos(): void
    {
        $this->criaDepartamento('dcet2');
        $this->criaDepartamento('dcet1')->update(['ativo' => false]);

        $this->get('/')->assertOk()->assertSee('/dcet2')->assertDontSee('/dcet1');
    }

    public function test_slug_serve_o_monitor(): void
    {
        $this->criaDepartamento('dcet2');

        $resposta = $this->get('/dcet2')->assertOk();
        $this->assertStringContainsString('monitor.html', $resposta->baseResponse->getFile()->getFilename());
    }

    public function test_slug_inativo_retorna_404(): void
    {
        $this->criaDepartamento('dcet2')->update(['ativo' => false]);

        $this->get('/dcet2')->assertNotFound();
    }

    public function test_slug_inexistente_retorna_404(): void
    {
        $this->get('/nao-existe')->assertNotFound();
    }

    public function test_rotas_do_sistema_nao_viram_monitor(): void
    {
        $this->criaUsuario('gestor');

        $this->get('/login')->assertOk()->assertSee('password', false);
    }

    public function test_nao_existe_index_html_publico(): void
    {
        $this->assertFileDoesNotExist(public_path('index.html'));
    }

    public function test_monitor_usa_caminhos_absolutos(): void
    {
        $html = file_get_contents(public_path('monitor.html'));

        $this->assertStringNotContainsString('href="assets/', $html);
        $this->assertStringNotContainsString('src="assets/', $html);
    }

    public function test_monitor_carrega_informes_e_criterios(): void
    {
        $html = file_get_contents(public_path('monitor.html'));

        foreach (['/assets/css/informe.css', '/assets/js/temas.js', '/assets/js/informeRender.js', '/assets/js/criterios.js', 'id="informe-section"'] as $trecho) {
            $this->assertStringContainsString($trecho, $html);
        }
    }

    public function test_noticias_informam_id_para_o_rodizio(): void
    {
        $this->criaDepartamento('dcet2');
        \App\Models\Noticia::create(['titulo' => 'N', 'fonte' => 'F', 'inicio' => now()->subHour(), 'fim' => now()->addHour()]);

        $this->getJson('/api/dcet2/noticias')->assertJsonStructure(['noticias' => [['id']]]);
    }
}
