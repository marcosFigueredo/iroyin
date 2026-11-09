<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'departamento_id',
        'unidade_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public const PERFIS = ['gestor', 'reitoria', 'departamento', 'unidade'];

    public const NOMES_PERFIS = [
        'gestor'       => 'Gestor central',
        'reitoria'     => 'Reitoria',
        'departamento' => 'Gabinete do departamento',
        'unidade'      => 'Secretaria',
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }

    public function isReitoria(): bool
    {
        return $this->role === 'reitoria';
    }

    public function isDepartamento(): bool
    {
        return $this->role === 'departamento';
    }

    public function isUnidade(): bool
    {
        return $this->role === 'unidade';
    }

    public function isAdmin(): bool
    {
        return $this->isGestor();
    }

    /** Departamento em que o usuário atua; o gestor escolhe um para dar suporte. */
    public function escopoDepartamentoId(): ?int
    {
        return $this->isGestor() ? session('operar_departamento_id') : $this->departamento_id;
    }
}
