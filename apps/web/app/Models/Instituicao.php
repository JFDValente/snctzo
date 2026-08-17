<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nome', 'instagram', 'facebook', 'site', 'outros_links'])]
class Instituicao extends Model
{
    protected $table = 'instituicoes';

    /**
     * @return HasMany<Curso, $this>
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    /**
     * @return HasMany<Professor, $this>
     */
    public function professores(): HasMany
    {
        return $this->hasMany(Professor::class);
    }
}
