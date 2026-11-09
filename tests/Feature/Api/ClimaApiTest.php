<?php

namespace Tests\Feature\Api;

use App\Models\Configuracao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class ClimaApiTest extends TestCase
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

    public function test_clima_returns_204_when_no_api_key_configured(): void
    {
        // Empty DB — Configuracao::current() returns defaults with empty api key
        $this->get('/api/dcet2/clima')
            ->assertStatus(204);
    }

    public function test_app_functions_normally_without_weather_key(): void
    {
        // Kiosk display must load even with no weather configuration
        $this->get('/dcet2')
            ->assertStatus(200);

        // Config API must work without weather key
        $this->getJson('/api/dcet2/config')
            ->assertStatus(200);

        // Horarios API must work without weather key
        $this->getJson('/api/dcet2/horarios')
            ->assertStatus(200);
    }
}
