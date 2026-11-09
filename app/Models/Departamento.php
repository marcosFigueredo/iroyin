<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Departamento extends Model
{
    public const SLUGS_RESERVADOS = [
        'admin', 'api', 'login', 'logout', 'register', 'setup', 'dashboard', 'profile',
        'build', 'assets', 'images', 'horarios', 'noticias', 'storage', 'up', 'acessivel',
        'forgot-password', 'reset-password', 'verify-email', 'confirm-password', 'password', 'email',
    ];

    protected $fillable = [
        'campus_id', 'sigla', 'nome', 'slug', 'ativo', 'tema', 'duracao_horarios', 'duracao_noticia',
    ];

    protected $casts = [
        'ativo'            => 'boolean',
        'duracao_horarios' => 'integer',
        'duracao_noticia'  => 'integer',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(Unidade::class);
    }

    public static function gerarSlug(string $sigla, ?int $numeroCampus): string
    {
        return Str::slug(str_replace('/', ' ', $sigla)).($numeroCampus ?? '');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->newQuery()->where($field ?? $this->getRouteKeyName(), $value);

        if ($field === 'slug') {
            $query->where('ativo', true);
        }

        return $query->first();
    }
}
