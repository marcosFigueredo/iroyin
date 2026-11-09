<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instituicao extends Model
{
    protected $table = 'instituicao';

    protected $fillable = [
        'nome',
        'sigla',
        'cidade',
        'estado',
        'texto_banner',
        'logo_url',
    ];

    public static function current(): self
    {
        $inst = static::first();

        if (! $inst) {
            $inst = static::create([
                'nome'         => '',
                'sigla'        => 'IROYIN',
                'cidade'       => '',
                'estado'       => '',
                'texto_banner' => 'NOTÍCIAS',
            ]);
        }

        return $inst;
    }
}
