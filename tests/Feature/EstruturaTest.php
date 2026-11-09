<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\Unidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CriaEstrutura;
use Tests\TestCase;

class EstruturaTest extends TestCase
{
    use RefreshDatabase, CriaEstrutura;

    public function test_gera_slug_com_numero_do_campus(): void
    {
        $this->assertSame('dcet2', Departamento::gerarSlug('DCET', 2));
        $this->assertSame('dmce', Departamento::gerarSlug('DMCE', null));
        $this->assertSame('dct-opara', Departamento::gerarSlug('DCT/OPARÁ', null));
    }

    public function test_laboratorio_nao_tem_horarios(): void
    {
        $this->assertFalse((new Unidade(['tipo' => 'laboratorio']))->temHorarios());
        $this->assertFalse((new Unidade(['tipo' => 'centro']))->temHorarios());
    }

    public function test_curso_tem_horarios(): void
    {
        $this->assertTrue((new Unidade(['tipo' => 'curso']))->temHorarios());
    }

    public function test_slug_de_departamento_inativo_nao_resolve(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $this->assertNotNull((new Departamento)->resolveRouteBinding('dcet2', 'slug'));

        $d->update(['ativo' => false]);
        $this->assertNull((new Departamento)->resolveRouteBinding('dcet2', 'slug'));
    }

    public function test_relacoes_ligam_campus_departamento_e_unidade(): void
    {
        $d = $this->criaDepartamento('dcet2');
        $c = $this->criaCurso($d);

        $this->assertTrue($d->campus->departamentos->contains($d));
        $this->assertTrue($d->unidades->contains($c));
    }
}
