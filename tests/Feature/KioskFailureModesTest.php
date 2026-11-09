<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KioskFailureModesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_content_can_be_empty_while_kiosk_contract_remains_valid(): void
    {
        $this->travelTo(Carbon::parse('2026-07-20 10:00:00'));

        // O HTML público é entregue como BinaryFileResponse; o conteúdo e o
        // estado visual correspondente são validados no teste Playwright.
        $this->get('/')->assertOk();

        $this->getJson('/api/horarios')
            ->assertOk()
            ->assertJsonPath('aulas', []);

        $this->getJson('/api/noticias')
            ->assertOk()
            ->assertJsonPath('noticias', [])
            ->assertJsonPath('total', 0);

        $this->getJson('/api/editais')
            ->assertOk()
            ->assertJsonPath('editais', []);

        $this->get('/api/clima')->assertNoContent();
    }
}
