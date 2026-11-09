<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unidade extends Model
{
    public const TIPOS = ['curso', 'laboratorio', 'centro'];

    protected $fillable = ['departamento_id', 'tipo', 'nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function temHorarios(): bool
    {
        return $this->tipo === 'curso';
    }
}
