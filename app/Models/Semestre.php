<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    protected $fillable = ['nome', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public static function ativar(int $id): void
    {
        self::query()->update(['ativo' => false]);
        self::find($id)->update(['ativo' => true]);
    }
}
