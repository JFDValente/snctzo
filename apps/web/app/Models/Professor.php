<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['instituicao_id', 'nome', 'email'])]
class Professor extends Model
{
    protected $table = 'professores';

    /**
     * @return BelongsTo<Instituicao, $this>
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class);
    }

    /**
     * @return HasMany<Atividade, $this>
     */
    public function atividadesComoResponsavel(): HasMany
    {
        return $this->hasMany(Atividade::class, 'professor_responsavel_id');
    }

    /**
     * @return BelongsToMany<Atividade, $this>
     */
    public function atividades(): BelongsToMany
    {
        return $this->belongsToMany(Atividade::class, 'atividade_professor');
    }
}
