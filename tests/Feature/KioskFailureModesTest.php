<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class KioskFailureModesTest extends TestCase
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

    public function test_all_content_can_be_empty_while_kiosk_contract_remains_valid(): void
    {
        $this->travelTo(Carbon::parse('2026-07-20 10:00:00'));

        // O HTML público é entregue como BinaryFileResponse; o conteúdo e o
        // estado visual correspondente são validados no teste Playwright.
        $this->get('/')->assertOk();

        $this->getJson('/api/dcet2/horarios')
            ->assertOk()
            ->assertJsonPath('aulas', []);

        $this->getJson('/api/dcet2/noticias')
            ->assertOk()
            ->assertJsonPath('noticias', [])
            ->assertJsonPath('total', 0);

        $this->getJson('/api/dcet2/editais')
            ->assertOk()
            ->assertJsonPath('editais', []);

        $this->get('/api/dcet2/clima')->assertNoContent();
    }
}
