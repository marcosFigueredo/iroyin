<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agencia extends Model
{
    protected $fillable = ['nome', 'sigla', 'cor_hex', 'url_noticias_rss', 'url_editais', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function editais(): HasMany
    {
        return $this->hasMany(Edital::class);
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}
