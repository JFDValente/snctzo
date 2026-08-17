<?php

namespace App\Actions\Inscricoes;

use App\Models\Aluno;
use App\Models\Atividade;
use App\Models\Curso;
use App\Models\Instituicao;
use App\Models\Professor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CriarAtividade
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function __invoke(array $dados): Atividade
    {
        $atividadeExistente = $this->atividadeExistente($dados['token_submissao']);

        if ($atividadeExistente !== null) {
            return $atividadeExistente;
        }

        try {
            return DB::transaction(function () use ($dados): Atividade {
                $atividadeExistente = $this->atividadeExistente($dados['token_submissao'], true);

                if ($atividadeExistente !== null) {
                    return $atividadeExistente;
                }

                $instituicao = $this->resolverInstituicao($dados['instituicao']);
                $curso = $this->resolverCurso($dados['curso_principal'], $instituicao);
                $professorResponsavel = $this->resolverProfessorResponsavel(
                    $dados['professor_responsavel'],
                    $instituicao,
                );

                $atividade = Atividade::query()->create([
                    'token_submissao' => $dados['token_submissao'],
                    'curso_id' => $curso->id,
                    'professor_responsavel_id' => $professorResponsavel->id,
                    'nome' => $dados['atividade']['nome'],
                    'participa_dia_20' => $dados['atividade']['participa_dia_20'],
                    'participa_dia_21' => $dados['atividade']['participa_dia_21'],
                    'resumo' => $dados['atividade']['resumo'],
                    'observacoes' => $dados['atividade']['observacoes'] ?? null,
                    'termos_aceitos_em' => now(),
                    'versao_termos' => config('snctzo.termos.versao'),
                ]);

                foreach ($dados['participantes'] as $participante) {
                    if ($participante['tipo'] === 'aluno') {
                        $atividade->alunos()->syncWithoutDetaching([
                            $this->resolverAluno($participante, $instituicao)->id,
                        ]);

                        continue;
                    }

                    $atividade->professores()->syncWithoutDetaching([
                        $this->resolverProfessorParticipante($participante)->id,
                    ]);
                }

                return $atividade;
            }, attempts: 3);
        } catch (QueryException $exception) {
            $atividadeExistente = $this->atividadeExistente($dados['token_submissao']);

            if ($atividadeExistente !== null) {
                return $atividadeExistente;
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverInstituicao(array $dados): Instituicao
    {
        if (isset($dados['id'])) {
            return Instituicao::query()->findOrFail($dados['id']);
        }

        return Instituicao::query()->firstOrCreate(
            ['nome' => $dados['nome']],
            Arr::only($dados, ['instagram', 'facebook', 'site', 'outros_links']),
        );
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverCurso(array $dados, Instituicao $instituicao): Curso
    {
        if (isset($dados['id'])) {
            $curso = Curso::query()->findOrFail($dados['id']);

            if ($curso->instituicao_id !== $instituicao->id) {
                throw ValidationException::withMessages([
                    'curso_principal.id' => 'O curso principal não pertence à instituição selecionada.',
                ]);
            }

            return $curso;
        }

        return Curso::query()->firstOrCreate([
            'instituicao_id' => $instituicao->id,
            'nome' => $dados['nome'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverProfessorResponsavel(array $dados, Instituicao $instituicao): Professor
    {
        $professor = Professor::query()->firstOrCreate(
            ['email' => $dados['email']],
            [
                'instituicao_id' => $instituicao->id,
                'nome' => $dados['nome'],
            ],
        );

        if ($professor->instituicao_id !== $instituicao->id) {
            throw ValidationException::withMessages([
                'professor_responsavel.email' => 'E-mail já utilizado por outro professor.',
            ]);
        }

        return $professor;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverAluno(array $dados, Instituicao $instituicao): Aluno
    {
        if (isset($dados['id'])) {
            $aluno = Aluno::query()->with('curso')->findOrFail($dados['id']);

            if ($aluno->curso->instituicao_id !== $instituicao->id) {
                throw ValidationException::withMessages([
                    'participantes' => 'O aluno não pertence à instituição selecionada.',
                ]);
            }

            return $aluno;
        }

        $curso = $this->resolverCursoDoAluno($dados['curso'], $instituicao);

        return Aluno::query()->create([
            'curso_id' => $curso->id,
            'nome' => $dados['nome'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverCursoDoAluno(array $dados, Instituicao $instituicao): Curso
    {
        if (isset($dados['id'])) {
            $curso = Curso::query()->findOrFail($dados['id']);

            if ($curso->instituicao_id !== $instituicao->id) {
                throw ValidationException::withMessages([
                    'participantes' => 'O curso do aluno não pertence à instituição selecionada.',
                ]);
            }

            return $curso;
        }

        return Curso::query()->firstOrCreate([
            'instituicao_id' => $instituicao->id,
            'nome' => $dados['nome'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function resolverProfessorParticipante(array $dados): Professor
    {
        if (isset($dados['id'])) {
            return Professor::query()->findOrFail($dados['id']);
        }

        $instituicao = $this->resolverInstituicao($dados['instituicao']);

        return Professor::query()->firstOrCreate(
            ['email' => $dados['email']],
            [
                'instituicao_id' => $instituicao->id,
                'nome' => $dados['nome'],
            ],
        );
    }

    private function atividadeExistente(string $token, bool $comBloqueio = false): ?Atividade
    {
        $consulta = Atividade::query()->where('token_submissao', $token);

        if ($comBloqueio) {
            $consulta->lockForUpdate();
        }

        return $consulta->first();
    }
}
