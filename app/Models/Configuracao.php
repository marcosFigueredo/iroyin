<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = [
        'cidade_clima',
        'weather_api_key',
        'duracao_horarios',
        'duracao_noticia',
        'tema',
    ];

    protected $casts = [
        'duracao_horarios' => 'integer',
        'duracao_noticia'  => 'integer',
    ];

    public static function current(): self
    {
        $cfg = static::first();

        if (! $cfg) {
            $cfg = static::create([
                'cidade_clima'     => 'Alagoinhas',
                'weather_api_key'  => '',
                'duracao_horarios' => 120,
                'duracao_noticia'  => 30,
                'tema'             => 'azul',
            ]);
        }

        return $cfg;
    }
}
