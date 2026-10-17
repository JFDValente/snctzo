<?php

namespace App\Actions\Presencas;

use App\Models\Atividade;
use App\Models\PeriodoDePresenca;
use App\Support\Presencas\CalendarioDePresencas;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmarPeriodoDePresenca
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function __invoke(Atividade $atividade, array $dados): PeriodoDePresenca
    {
        $calendario = app(CalendarioDePresencas::class);
        $data = $dados['data'];
        $turno = $dados['turno'];

        if (! $calendario->periodoExiste($data, $turno) || ! $this->atividadeParticipaDoDia($atividade, $data)) {
            throw ValidationException::withMessages(['data' => 'A atividade não está agendada para este período.']);
        }

        $atividade->load(['alunos', 'professores']);
        $dados = $this->completarAusencias($atividade, $dados);
        $this->validarParticipantes($atividade, $dados);

        return DB::transaction(function () use ($atividade, $dados, $data, $turno): PeriodoDePresenca {
            $periodo = PeriodoDePresenca::query()
                ->where('atividade_id', $atividade->id)
                ->where('data', $data)
                ->where('turno', $turno)
                ->lockForUpdate()
                ->first();

            $atributos = [
                'confirmado_por_id' => auth()->id(),
                'confirmado_em' => now(),
            ];

            if ($periodo === null) {
                $periodo = $atividade->periodosDePresenca()->create([
                    'data' => $data,
                    'turno' => $turno,
                    ...$atributos,
                ]);
            } else {
                $periodo->update($atributos);
            }

            $periodo->presencasAlunos()->delete();
            $periodo->presencasProfessores()->delete();

            foreach ($dados['alunos'] ?? [] as $alunoId => $status) {
                $periodo->presencasAlunos()->create(['aluno_id' => $alunoId, 'status' => $status]);
            }

            foreach ($dados['professores'] ?? [] as $professorId => $status) {
                $periodo->presencasProfessores()->create(['professor_id' => $professorId, 'status' => $status]);
            }

            return $periodo;
        });
    }

    /** @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function completarAusencias(Atividade $atividade, array $dados): array
    {
        $dados['alunos'] = $dados['alunos'] ?? [];
        $dados['professores'] = $dados['professores'] ?? [];

        foreach ($atividade->alunos as $aluno) {
            $dados['alunos'][$aluno->id] ??= 'ausente';
        }

        foreach ($atividade->professores as $professor) {
            $dados['professores'][$professor->id] ??= 'ausente';
        }

        return $dados;
    }

    /** @param array<string, mixed> $dados */
    private function validarParticipantes(Atividade $atividade, array $dados): void
    {
        $alunosEsperados = $atividade->alunos->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
        $professoresEsperados = $atividade->professores->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
        $alunosInformados = collect(array_keys($dados['alunos'] ?? []))
            ->map(fn ($id) => (string) $id)
            ->sort()
            ->values()
            ->all();
        $professoresInformados = collect(array_keys($dados['professores'] ?? []))
            ->map(fn ($id) => (string) $id)
            ->sort()
            ->values()
            ->all();

        if ($alunosEsperados !== $alunosInformados || $professoresEsperados !== $professoresInformados) {
            throw ValidationException::withMessages(['participantes' => 'Informe a presença de todos os participantes da atividade.']);
        }
    }

    private function atividadeParticipaDoDia(Atividade $atividade, string $data): bool
    {
        return match ($data) {
            '2026-10-20' => $atividade->participa_dia_20,
            '2026-10-21' => $atividade->participa_dia_21,
            default => false,
        };
    }
}
