<?php

namespace App\Actions\Presencas;

use App\Models\Aluno;
use App\Models\Atividade;
use App\Models\PeriodoDePresenca;
use App\Models\PresencaAluno;
use App\Models\PresencaProfessor;
use App\Models\Professor;
use App\Support\Presencas\CalendarioDePresencas;
use Carbon\CarbonImmutable;
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
            $this->validarConflitosDePresenca($atividade, $dados, $data, $turno);

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

    /** @param array<string, mixed> $dados */
    private function validarConflitosDePresenca(Atividade $atividade, array $dados, string $data, string $turno): void
    {
        $alunos = collect($dados['alunos'] ?? [])->filter(fn ($status) => $status === 'presente')->keys()->map(fn ($id) => (int) $id)->sort()->values();
        $professores = collect($dados['professores'] ?? [])->filter(fn ($status) => $status === 'presente')->keys()->map(fn ($id) => (int) $id)->sort()->values();

        Aluno::query()->whereKey($alunos)->orderBy('id')->lockForUpdate()->get();
        Professor::query()->whereKey($professores)->orderBy('id')->lockForUpdate()->get();

        $conflitos = PresencaAluno::query()
            ->select(['presencas_alunos.aluno_id as participante_id', 'alunos.nome as participante', 'atividades.nome as atividade'])
            ->join('periodos_de_presenca', 'periodos_de_presenca.id', '=', 'presencas_alunos.periodo_de_presenca_id')
            ->join('atividades', 'atividades.id', '=', 'periodos_de_presenca.atividade_id')
            ->join('alunos', 'alunos.id', '=', 'presencas_alunos.aluno_id')
            ->whereIn('presencas_alunos.aluno_id', $alunos)
            ->where('presencas_alunos.status', 'presente')->where('periodos_de_presenca.data', $data)->where('periodos_de_presenca.turno', $turno)
            ->where('atividades.id', '!=', $atividade->id)->whereNull('atividades.deleted_at')
            ->get()->map(fn ($item) => ['tipo' => 'alunos', ...$item->toArray()])
            ->concat(PresencaProfessor::query()
                ->select(['presencas_professores.professor_id as participante_id', 'professores.nome as participante', 'atividades.nome as atividade'])
                ->join('periodos_de_presenca', 'periodos_de_presenca.id', '=', 'presencas_professores.periodo_de_presenca_id')
                ->join('atividades', 'atividades.id', '=', 'periodos_de_presenca.atividade_id')
                ->join('professores', 'professores.id', '=', 'presencas_professores.professor_id')
                ->whereIn('presencas_professores.professor_id', $professores)
                ->where('presencas_professores.status', 'presente')->where('periodos_de_presenca.data', $data)->where('periodos_de_presenca.turno', $turno)
                ->where('atividades.id', '!=', $atividade->id)->whereNull('atividades.deleted_at')
                ->get()->map(fn ($item) => ['tipo' => 'professores', ...$item->toArray()]));

        if ($conflitos->isEmpty()) {
            return;
        }

        $mensagens = $conflitos->map(fn (array $conflito) => sprintf('%s %s já está marcado como presente na atividade “%s” em %s, no turno da %s. Altere uma das duas atividades para ausente e confirme novamente.', $conflito['tipo'] === 'alunos' ? 'Aluno' : 'Professor', $conflito['participante'], $conflito['atividade'], CarbonImmutable::parse($data)->format('d/m/Y'), $turno === 'manha' ? 'manhã' : $turno))->values()->all();
        $erros = ['participantes' => 'Não foi possível confirmar: um ou mais participantes já estão presentes em outra atividade neste turno.', 'conflitos' => $mensagens];
        foreach ($conflitos->values() as $indice => $conflito) {
            $erros["{$conflito['tipo']}.{$conflito['participante_id']}"] = $mensagens[$indice];
        }

        throw ValidationException::withMessages($erros);
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
