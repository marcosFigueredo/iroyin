<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class InformesApiTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    public function test_informe_do_departamento_nao_aparece_em_outro(): void
    {
        $d1 = $this->criaDepartamento('dcet1');
        $this->criaDepartamento('dcet2');
        $this->criaInforme($d1, ['titulo' => 'Só de Salvador']);

        $this->getJson('/api/dcet1/informes')->assertJsonFragment(['titulo' => 'Só de Salvador', 'escopo' => 'departamento']);
        $this->getJson('/api/dcet2/informes')->assertJsonMissing(['titulo' => 'Só de Salvador']);
    }

    public function test_informe_da_reitoria_aparece_em_todos(): void
    {
        $this->criaDepartamento('dcet1');
        $this->criaDepartamento('dcet2');
        $this->criaInforme(null, ['titulo' => 'Geral']);

        foreach (['dcet1', 'dcet2'] as $slug) {
            $this->getJson("/api/{$slug}/informes")
                ->assertJsonFragment(['titulo' => 'Geral', 'escopo' => 'reitoria', 'origem' => 'Reitoria']);
        }
    }

    public function test_origem_mostra_unidade_e_departamento(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $curso = $this->criaCurso($d, 'Matemática');
        $this->criaInforme($d, ['titulo' => 'Do curso', 'unidade_id' => $curso->id]);
        $this->criaInforme($d, ['titulo' => 'Do gabinete']);

        $this->getJson('/api/dcet2/informes')
            ->assertJsonFragment(['titulo' => 'Do curso', 'origem' => 'Matemática · DCET2'])
            ->assertJsonFragment(['titulo' => 'Do gabinete', 'origem' => 'DCET2']);
    }

    public function test_informe_vencido_nao_aparece(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $this->criaInforme($d, ['titulo' => 'Vencido', 'inicio' => now()->subDays(3), 'fim' => now()->subDay()]);

        $this->getJson('/api/dcet2/informes')->assertJsonMissing(['titulo' => 'Vencido']);
    }

    public function test_informe_futuro_nao_aparece(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $this->criaInforme($d, ['titulo' => 'Futuro', 'inicio' => now()->addDay(), 'fim' => now()->addDays(2)]);

        $this->getJson('/api/dcet2/informes')->assertJsonMissing(['titulo' => 'Futuro']);
    }

    public function test_departamento_inexistente_retorna_404(): void
    {
        $this->getJson('/api/nada/informes')->assertNotFound();
    }
}
