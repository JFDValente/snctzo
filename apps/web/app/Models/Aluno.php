<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['curso_id', 'nome'])]
class Aluno extends Model
{
    protected $table = 'alunos';

    /**
     * @return BelongsTo<Curso, $this>
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * @return BelongsToMany<Atividade, $this>
     */
    public function atividades(): BelongsToMany
    {
        return $this->belongsToMany(Atividade::class, 'atividade_aluno')
            ->withPivot('cadastrado_tardiamente');
    }

    /**
     * @return HasMany<PresencaAluno, $this>
     */
    public function presencas(): HasMany
    {
        return $this->hasMany(PresencaAluno::class);
    }
}
