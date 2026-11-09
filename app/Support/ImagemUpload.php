<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImagemUpload
{
    private const PASTA = 'noticias/imagens';

    /** A extensão vem do conteúdo do arquivo, nunca do nome enviado. */
    public static function salvar(UploadedFile $file): string
    {
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'imagem';
        $nome = time().'_'.Str::random(6).'_'.$base.'.'.($file->guessExtension() ?: 'jpg');

        $file->move(public_path(self::PASTA), $nome);

        return self::PASTA.'/'.$nome;
    }

    public static function apagar(?string $caminho): void
    {
        if ($caminho && is_file(public_path($caminho))) {
            unlink(public_path($caminho));
        }
    }
}
