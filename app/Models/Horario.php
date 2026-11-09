<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $fillable = [
        'semestre_id', 'unidade_id', 'dia_semana', 'disciplina',
        'professor', 'inicio', 'fim', 'sala',
    ];

    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function getCursoAttribute(): string
    {
        return $this->unidade?->nome ?? '';
    }

    public static function diasOrdem(): array
    {
        return ['segunda','terca','quarta','quinta','sexta','sabado'];
    }
}
