<?php

namespace App\Actions\Presencas;

use App\Models\Aluno;
use App\Models\Atividade;
use App\Models\Professor;
use App\Support\Normalizacao\NormalizadorDeTexto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class AdicionarParticipanteTardio
{
    public function __invoke(Atividade $atividade, string $tipo, string $nome): Model
    {
        $normalizador = new NormalizadorDeTexto;
        $nome = $normalizador->normalizar($nome);
        $relacao = $tipo === 'aluno' ? 'alunos' : 'professores';

        $atividade->load($relacao);

        if ($atividade->{$relacao}->contains(fn ($pessoa): bool => $normalizador->chaveDeComparacao($pessoa->nome) === $normalizador->chaveDeComparacao($nome))) {
            throw ValidationException::withMessages(['nome' => 'Este participante já está vinculado à atividade.']);
        }

        $pessoa = $tipo === 'aluno'
            ? Aluno::query()->create(['curso_id' => null, 'nome' => $nome])
            : Professor::query()->create(['instituicao_id' => null, 'nome' => $nome, 'email' => null]);

        $atividade->{$relacao}()->attach($pessoa->id, ['cadastrado_tardiamente' => true]);

        return $pessoa;
    }
}
