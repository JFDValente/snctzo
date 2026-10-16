<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'atividade_id',
    'data',
    'turno',
    'confirmado_por_id',
    'confirmado_em',
])]
class PeriodoDePresenca extends Model
{
    protected $table = 'periodos_de_presenca';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'date',
            'confirmado_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Atividade, $this>
     */
    public function atividade(): BelongsTo
    {
        return $this->belongsTo(Atividade::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por_id');
    }

    /**
     * @return HasMany<PresencaAluno, $this>
     */
    public function presencasAlunos(): HasMany
    {
        return $this->hasMany(PresencaAluno::class);
    }

    /**
     * @return HasMany<PresencaProfessor, $this>
     */
    public function presencasProfessores(): HasMany
    {
        return $this->hasMany(PresencaProfessor::class);
    }
}
