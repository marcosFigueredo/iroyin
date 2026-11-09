<?php

namespace Tests\Concerns;

use App\Models\Campus;
use App\Models\Departamento;
use App\Models\Unidade;

trait CriaEstrutura
{
    protected function criaDepartamento(string $slug = 'dcet2'): Departamento
    {
        preg_match('/^([a-z]+)(\d*)$/', $slug, $m);
        $numero = ($m[2] ?? '') === '' ? null : (int) $m[2];

        $campus = Campus::firstOrCreate(
            ['numero' => $numero, 'nome' => 'Campus '.($numero ?? 'X')],
            ['cidade' => 'Alagoinhas', 'estado' => 'BA'],
        );

        return Departamento::create([
            'campus_id' => $campus->id,
            'sigla'     => strtoupper($m[1] ?? $slug),
            'nome'      => 'Departamento '.strtoupper($slug),
            'slug'      => $slug,
        ]);
    }

    protected function criaCurso(Departamento $d, string $nome = 'Sistemas de Informação'): Unidade
    {
        return Unidade::create(['departamento_id' => $d->id, 'tipo' => 'curso', 'nome' => $nome]);
    }
}
