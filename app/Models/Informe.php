<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Informe extends Model
{
    public const URGENCIAS = ['normal', 'importante', 'urgente'];

    public const MAX_DIAS_URGENTE = 7;

    protected $fillable = [
        'departamento_id', 'unidade_id', 'user_id', 'formato', 'titulo', 'texto',
        'imagem', 'urgencia', 'inicio', 'fim',
    ];

    protected $casts = ['inicio' => 'datetime', 'fim' => 'datetime'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('inicio', '<=', now())->where('fim', '>=', now());
    }

    /** Informes vigentes que um departamento exibe: os próprios e os da Reitoria. */
    public function scopeParaMonitor(Builder $query, Departamento $departamento): Builder
    {
        return $query->vigentes()->where(function (Builder $q) use ($departamento) {
            $q->where('departamento_id', $departamento->id)->orWhereNull('departamento_id');
        });
    }

    public function escopo(): string
    {
        return $this->departamento_id ? 'departamento' : 'reitoria';
    }

    public function origem(): string
    {
        if (! $this->departamento_id) {
            return 'Reitoria';
        }

        $sigla = mb_strtoupper($this->departamento->slug);

        return $this->unidade ? "{$this->unidade->nome} · {$sigla}" : $sigla;
    }

    public function paraApi(): array
    {
        return [
            'id'        => $this->id,
            'formato'   => $this->formato,
            'titulo'    => $this->titulo,
            'texto'     => $this->texto,
            'imagem'    => $this->imagem,
            'urgencia'  => $this->urgencia,
            'origem'    => $this->origem(),
            'escopo'    => $this->escopo(),
            'criado_em' => $this->created_at?->toISOString(),
        ];
    }
}
