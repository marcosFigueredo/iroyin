<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    protected $fillable = ['titulo', 'link', 'fonte', 'imagem', 'inicio', 'fim'];

    protected $casts = [
        'inicio' => 'datetime',
        'fim'    => 'datetime',
    ];

    /** Apenas notícias com periodo vigente */
    public function scopeAtivas($query)
    {
        return $query->where('inicio', '<=', now())->where('fim', '>=', now());
    }

    /** Status calculado: ativa | futura | expirada */
    public function getStatusAttribute(): string
    {
        if (now() < $this->inicio) return 'futura';
        if (now() > $this->fim)    return 'expirada';
        return 'ativa';
    }

    /** Remove do disco a imagem associada */
    public function removerImagem(): void
    {
        if ($this->imagem && file_exists(public_path($this->imagem))) {
            unlink(public_path($this->imagem));
        }
    }
}
