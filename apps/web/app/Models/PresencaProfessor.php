<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['periodo_de_presenca_id', 'professor_id', 'status'])]
class PresencaProfessor extends Model
{
    protected $table = 'presencas_professores';

    /**
     * @return BelongsTo<PeriodoDePresenca, $this>
     */
    public function periodoDePresenca(): BelongsTo
    {
        return $this->belongsTo(PeriodoDePresenca::class);
    }

    /**
     * @return BelongsTo<Professor, $this>
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }
}
