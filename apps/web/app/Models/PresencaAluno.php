<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['periodo_de_presenca_id', 'aluno_id', 'status'])]
class PresencaAluno extends Model
{
    protected $table = 'presencas_alunos';

    /**
     * @return BelongsTo<PeriodoDePresenca, $this>
     */
    public function periodoDePresenca(): BelongsTo
    {
        return $this->belongsTo(PeriodoDePresenca::class);
    }

    /**
     * @return BelongsTo<Aluno, $this>
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
