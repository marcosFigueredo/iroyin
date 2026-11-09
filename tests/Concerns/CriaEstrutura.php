<?php

namespace Tests\Concerns;

use App\Models\Campus;
use App\Models\Departamento;
use App\Models\Semestre;
use App\Models\Unidade;
use App\Models\User;

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

    protected function criaSemestreVigente(): Semestre
    {
        return Semestre::create([
            'nome'   => '2027.1',
            'inicio' => now()->subMonth()->toDateString(),
            'fim'    => now()->addMonth()->toDateString(),
        ]);
    }

    protected function criaUsuario(string $role, ?Departamento $d = null, ?Unidade $u = null): User
    {
        return User::factory()->create([
            'role'            => $role,
            'departamento_id' => $d?->id,
            'unidade_id'      => $u?->id,
        ]);
    }

    protected function criaCurso(Departamento $d, string $nome = 'Sistemas de Informação'): Unidade
    {
        return Unidade::create(['departamento_id' => $d->id, 'tipo' => 'curso', 'nome' => $nome]);
    }
}
