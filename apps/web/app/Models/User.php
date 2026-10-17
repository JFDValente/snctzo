<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'perfil'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const PERFIL_ADMINISTRADOR = 'administrador';

    public const PERFIL_OPERADOR_PRESENCA = 'operador_presenca';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    /**
     * @return HasMany<PeriodoDePresenca, $this>
     */
    public function periodosDePresencaConfirmados(): HasMany
    {
        return $this->hasMany(PeriodoDePresenca::class, 'confirmado_por_id');
    }

    public function podeAcessarModulo(string $modulo): bool
    {
        return match ($modulo) {
            'atividades' => $this->perfil === self::PERFIL_ADMINISTRADOR,
            'presencas' => in_array($this->perfil, [
                self::PERFIL_ADMINISTRADOR,
                self::PERFIL_OPERADOR_PRESENCA,
            ], true),
            default => false,
        };
    }

    public function rotaInicialAdministrativa(): string
    {
        return $this->perfil === self::PERFIL_OPERADOR_PRESENCA
            ? route('admin.presencas.index')
            : route('admin.atividades.index');
    }
}
