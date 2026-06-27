<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $fillable = [
        'semestre_id', 'dia_semana', 'disciplina',
        'professor', 'curso', 'inicio', 'fim', 'sala',
    ];

    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    public static function diasOrdem(): array
    {
        return ['segunda','terca','quarta','quinta','sexta','sabado'];
    }
}
