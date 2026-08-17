<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['instituicao_id', 'nome'])]
class Curso extends Model
{
    protected $table = 'cursos';

    /**
     * @return BelongsTo<Instituicao, $this>
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }

    /**
     * @return HasMany<Aluno, $this>
     */
    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class);
    }

    /**
     * @return HasMany<Atividade, $this>
     */
    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }
}
