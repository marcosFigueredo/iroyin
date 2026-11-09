<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class ConfigApiTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    protected \App\Models\Departamento $departamento;
    protected \App\Models\Unidade $curso;

    protected function setUp(): void
    {
        parent::setUp();
        $this->departamento = $this->criaDepartamento('dcet2');
        $this->curso = $this->criaCurso($this->departamento);
    }

    public function test_config_endpoint_returns_200(): void
    {
        $this->getJson('/api/dcet2/config')
            ->assertStatus(200);
    }

    public function test_config_returns_required_keys(): void
    {
        $this->getJson('/api/dcet2/config')
            ->assertJsonStructure([
                'nome',
                'sigla',
                'departamento',
                'cidade',
                'estado',
                'texto_banner',
                'duracao_horarios',
                'duracao_noticia',
                'tema',
            ]);
    }

    public function test_config_does_not_expose_weather_api_key(): void
    {
        $response = $this->getJson('/api/dcet2/config');

        $response->assertJsonMissingPath('weather_api_key');
    }

}
