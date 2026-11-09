<?php

namespace Tests\Feature\Api;

use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class NoticiasApiTest extends TestCase
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

    public function test_noticias_returns_200(): void
    {
        $this->getJson('/api/dcet2/noticias')
            ->assertStatus(200);
    }

    public function test_noticias_returns_empty_when_no_news(): void
    {
        $this->getJson('/api/dcet2/noticias')
            ->assertJson(['noticias' => []]);
    }

    public function test_noticias_returns_active_news(): void
    {
        Noticia::create([
            'titulo' => 'Notícia Ativa',
            'fonte'  => 'Teste',
            'inicio' => now()->subHour(),
            'fim'    => now()->addDays(7),
        ]);

        $this->getJson('/api/dcet2/noticias')
            ->assertJsonFragment(['titulo' => 'Notícia Ativa']);
    }

    public function test_noticias_excludes_expired_news(): void
    {
        Noticia::create([
            'titulo' => 'Notícia Expirada',
            'fonte'  => 'Teste',
            'inicio' => now()->subDays(10),
            'fim'    => now()->subDay(),
        ]);

        $this->getJson('/api/dcet2/noticias')
            ->assertJson(['noticias' => []]);
    }

    public function test_noticias_excludes_future_news(): void
    {
        Noticia::create([
            'titulo' => 'Notícia Futura',
            'fonte'  => 'Teste',
            'inicio' => now()->addDay(),
            'fim'    => now()->addDays(7),
        ]);

        $this->getJson('/api/dcet2/noticias')
            ->assertJson(['noticias' => []]);
    }

    public function test_noticias_structure_contains_required_fields(): void
    {
        Noticia::create([
            'titulo' => 'Notícia Estrutura',
            'fonte'  => 'Fonte Teste',
            'link'   => 'https://example.com',
            'inicio' => now()->subHour(),
            'fim'    => now()->addDays(7),
        ]);

        $this->getJson('/api/dcet2/noticias')
            ->assertJsonStructure([
                'noticias' => [
                    '*' => ['titulo', 'fonte', 'link', 'imagem'],
                ],
            ]);
    }
}
