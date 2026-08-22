<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'token_submissao',
    'curso_id',
    'professor_responsavel_id',
    'nome',
    'forma_apresentacao',
    'participa_dia_20',
    'participa_dia_21',
    'resumo',
    'observacoes',
    'termos_aceitos_em',
    'versao_termos',
    'email_confirmacao_enviado_em',
])]
class Atividade extends Model
{
    protected $table = 'atividades';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'participa_dia_20' => 'boolean',
            'participa_dia_21' => 'boolean',
            'termos_aceitos_em' => 'datetime',
            'email_confirmacao_enviado_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Curso, $this>
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * @return BelongsTo<Professor, $this>
     */
    public function professorResponsavel(): BelongsTo
    {
        return $this->belongsTo(Professor::class, 'professor_responsavel_id');
    }

    /**
     * @return BelongsToMany<Aluno, $this>
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'atividade_aluno');
    }

    /**
     * @return BelongsToMany<Professor, $this>
     */
    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class, 'atividade_professor');
    }
}
