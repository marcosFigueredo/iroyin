<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $fillable = ['nome', 'url', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
