<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = [
        'weather_api_key',
    ];


    public static function current(): self
    {
        $cfg = static::first();

        if (! $cfg) {
            $cfg = static::create([
                'weather_api_key' => '',
            ]);
        }

        return $cfg;
    }
}
