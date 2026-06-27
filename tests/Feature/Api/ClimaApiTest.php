<?php

namespace Tests\Feature\Api;

use App\Models\Configuracao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClimaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_clima_returns_204_when_no_api_key_configured(): void
    {
        // Empty DB — Configuracao::current() returns defaults with empty api key
        $this->get('/api/clima')
            ->assertStatus(204);
    }

    public function test_clima_returns_204_when_no_city_configured(): void
    {
        Configuracao::create([
            'weather_api_key'  => 'some-key',
            'cidade_clima'     => '',
            'duracao_horarios' => 120,
            'duracao_noticia'  => 30,
            'tema'             => 'azul',
        ]);

        $this->get('/api/clima')
            ->assertStatus(204);
    }

    public function test_app_functions_normally_without_weather_key(): void
    {
        // Kiosk display must load even with no weather configuration
        $this->get('/')
            ->assertStatus(200);

        // Config API must work without weather key
        $this->getJson('/api/config')
            ->assertStatus(200);

        // Horarios API must work without weather key
        $this->getJson('/api/horarios')
            ->assertStatus(200);
    }
}
