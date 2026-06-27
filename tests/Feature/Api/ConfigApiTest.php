<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_endpoint_returns_200(): void
    {
        $this->getJson('/api/config')
            ->assertStatus(200);
    }

    public function test_config_returns_required_keys(): void
    {
        $this->getJson('/api/config')
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
        $response = $this->getJson('/api/config');

        $response->assertJsonMissingPath('weather_api_key');
        $response->assertJsonMissingPath('cidade_clima');
    }

    public function test_config_does_not_contain_institution_specific_branding(): void
    {
        $response = $this->getJson('/api/config');
        $content  = $response->getContent();

        $this->assertStringNotContainsStringIgnoringCase('DCET',     $content);
        $this->assertStringNotContainsStringIgnoringCase('UNEB',     $content);
        $this->assertStringNotContainsStringIgnoringCase('Alagoinhas', $content);
    }
}
