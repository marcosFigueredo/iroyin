<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Edital extends Model
{
    protected $table = 'editais';

    protected $fillable = ['agencia_id', 'titulo', 'objetivo', 'link', 'data_fechamento', 'ativo'];

    protected $casts = [
        'ativo'           => 'boolean',
        'data_fechamento' => 'date',
    ];

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function getDiasRestantesAttribute(): ?int
    {
        if (! $this->data_fechamento) return null;
        return (int) now()->startOfDay()->diffInDays($this->data_fechamento, false);
    }
}
