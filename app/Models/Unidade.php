<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    public const TIPOS = ['curso', 'laboratorio', 'centro'];

    protected $fillable = ['departamento_id', 'tipo', 'nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function informes(): HasMany
    {
        return $this->hasMany(Informe::class);
    }

    public function podeExcluir(): bool
    {
        return ! $this->usuarios()->exists()
            && ! $this->horarios()->exists()
            && ! $this->informes()->exists();
    }

    public function temHorarios(): bool
    {
        return $this->tipo === 'curso';
    }
}
