<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    protected $table = 'campi';

    protected $fillable = ['numero', 'nome', 'cidade', 'estado'];

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class);
    }

    public function cidadeClima(): string
    {
        return $this->cidade.',BR';
    }
}
