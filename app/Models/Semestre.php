<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    protected $fillable = ['nome', 'inicio', 'fim'];

    protected $casts = ['inicio' => 'date', 'fim' => 'date'];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public static function vigente(): ?self
    {
        $hoje = now()->toDateString();

        return self::whereDate('inicio', '<=', $hoje)->whereDate('fim', '>=', $hoje)->first();
    }

    public function estaVigente(): bool
    {
        return $this->is(self::vigente());
    }

    public static function sobrepoe(Carbon $inicio, Carbon $fim, ?int $ignorarId = null): bool
    {
        return self::whereDate('inicio', '<=', $fim->toDateString())
            ->whereDate('fim', '>=', $inicio->toDateString())
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists();
    }
}
