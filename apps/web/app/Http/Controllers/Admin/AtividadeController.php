<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use App\Models\PeriodoDePresenca;
use App\Support\Presencas\CalendarioDePresencas;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AtividadeController extends Controller
{
    public function index(): View
    {
        $atividades = Atividade::query()
            ->with(['curso.instituicao'])
            ->orderBy('nome')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.atividades.index', compact('atividades'));
    }

    public function show(Atividade $atividade): View
    {
        $atividade->load([
            'curso.instituicao',
            'professorResponsavel.instituicao',
            'alunos.curso',
            'professores.instituicao',
        ]);

        return view('admin.atividades.show', compact('atividade'));
    }

    public function destroy(Atividade $atividade): RedirectResponse
    {
        $atividade->delete();

        return to_route('admin.atividades.index')
            ->with('sucesso', 'Atividade excluída com sucesso.');
    }

    public function exportar(): StreamedResponse
    {
        $nomeDoArquivo = 'atividades-snctzo-2026-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function (): void {
            $arquivo = fopen('php://output', 'w');

            if ($arquivo === false) {
                return;
            }

            fwrite($arquivo, "\xEF\xBB\xBF");

            fputcsv($arquivo, [
                'Nome da atividade',
                'Unidade acadêmica',
                'Curso principal',
                'Data de inscrição',
                'Professor responsável',
                'E-mail do responsável',
                'Participação em 20/10',
                'Participação em 21/10',
                'Resumo',
                'Observações',
                'Instagram',
                'Facebook',
                'Site',
                'Outros links',
                'Participantes',
            ], ';', '"', '');

            Atividade::query()
                ->with([
                    'curso.instituicao',
                    'professorResponsavel',
                    'alunos',
                    'professores',
                ])
                ->orderBy('created_at')
                ->orderBy('id')
                ->chunk(100, function ($atividades) use ($arquivo): void {
                    foreach ($atividades as $atividade) {
                        fputcsv($arquivo, $this->linhaDaExportacao($atividade), ';', '"', '');
                    }
                });

            fclose($arquivo);
        }, $nomeDoArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportarPresencas(CalendarioDePresencas $calendario): StreamedResponse
    {
        $nomeDoArquivo = 'presencas-snctzo-2026-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($calendario): void {
            $arquivo = fopen('php://output', 'w');

            if ($arquivo === false) {
                return;
            }

            fwrite($arquivo, "\xEF\xBB\xBF");

            fputcsv($arquivo, [
                'Atividade',
                'Professor responsável',
                'Unidade acadêmica',
                'Curso principal',
                'Data',
                'Turno',
                'Tipo',
                'Participante',
                'Status',
                'Participante adicionado',
                'Confirmado em',
                'Confirmado por',
            ], ';', '"', '');

            Atividade::query()
                ->with([
                    'curso.instituicao',
                    'professorResponsavel',
                    'alunos',
                    'professores',
                    'periodosDePresenca' => fn ($consulta) => $consulta
                        ->with(['confirmadoPor', 'presencasAlunos', 'presencasProfessores']),
                ])
                ->orderBy('nome')
                ->orderBy('id')
                ->chunk(100, function ($atividades) use ($arquivo, $calendario): void {
                    foreach ($atividades as $atividade) {
                        $this->exportarLinhasDePresenca($arquivo, $atividade, $calendario);
                    }
                });

            fclose($arquivo);
        }, $nomeDoArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return list<string>
     */
    private function linhaDaExportacao(Atividade $atividade): array
    {
        $participantes = [
            ...$atividade->alunos
                ->map(fn ($aluno): string => "(Aluno|{$aluno->nome})")
                ->all(),
            ...$atividade->professores
                ->map(fn ($professor): string => "(Professor|{$professor->nome})")
                ->all(),
        ];

        $valores = [
            $atividade->nome,
            $atividade->curso->instituicao->nome,
            $atividade->curso->nome,
            $atividade->created_at->format('d/m/Y \à\s H:i'),
            $atividade->professorResponsavel->nome,
            $atividade->professorResponsavel->email,
            $atividade->participa_dia_20 ? 'Sim' : 'Não',
            $atividade->participa_dia_21 ? 'Sim' : 'Não',
            $atividade->resumo,
            $atividade->observacoes,
            $atividade->instagram,
            $atividade->facebook,
            $atividade->site,
            $atividade->outros_links,
            implode('; ', $participantes),
        ];

        return array_map(
            fn (mixed $valor): string => $this->protegerCelulaCsv($valor),
            $valores,
        );
    }

    /** @param resource $arquivo */
    private function exportarLinhasDePresenca($arquivo, Atividade $atividade, CalendarioDePresencas $calendario): void
    {
        foreach ($calendario->opcoesDeData() as $opcaoDeData) {
            $data = $opcaoDeData['valor'];

            if (! $this->atividadeParticipaDaData($atividade, $data)) {
                continue;
            }

            foreach ($calendario->turnosDaData($data) as $turno) {
                $periodo = $atividade->periodosDePresenca
                    ->first(fn (PeriodoDePresenca $item) => $item->data->toDateString() === $data && $item->turno === $turno);

                $participantes = $atividade->alunos
                    ->map(fn ($aluno) => ['tipo' => 'Aluno', 'participante' => $aluno])
                    ->concat($atividade->professores->map(fn ($professor) => ['tipo' => 'Professor', 'participante' => $professor]))
                    ->sortBy(fn (array $item) => mb_strtolower(Str::ascii($item['participante']->nome)))
                    ->values();

                foreach ($participantes as $item) {
                    $participante = $item['participante'];
                    $status = $item['tipo'] === 'Aluno'
                        ? $periodo?->presencasAlunos->firstWhere('aluno_id', $participante->id)?->status
                        : $periodo?->presencasProfessores->firstWhere('professor_id', $participante->id)?->status;

                    fputcsv($arquivo, $this->linhaDaExportacaoDePresenca(
                        $atividade,
                        $data,
                        $turno,
                        $item['tipo'],
                        $participante,
                        $status,
                        $periodo,
                        (bool) $participante->pivot->cadastrado_tardiamente,
                        $calendario,
                    ), ';', '"', '');
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function linhaDaExportacaoDePresenca(
        Atividade $atividade,
        string $data,
        string $turno,
        string $tipo,
        mixed $participante,
        ?string $status,
        ?PeriodoDePresenca $periodo,
        bool $participanteAdicionado,
        CalendarioDePresencas $calendario,
    ): array {
        $valores = [
            $atividade->nome,
            $atividade->professorResponsavel->nome,
            $atividade->curso->instituicao->nome,
            $atividade->curso->nome,
            CarbonImmutable::parse($data)->format('d/m/Y'),
            $calendario->rotuloDoTurno($turno),
            $tipo,
            $participante->nome,
            $status === null ? 'Não registrado' : ucfirst($status),
            $participanteAdicionado ? 'Sim' : 'Não',
            $periodo?->confirmado_em?->format('d/m/Y \à\s H:i'),
            $periodo?->confirmadoPor?->name,
        ];

        return array_map(
            fn (mixed $valor): string => $this->protegerCelulaCsv($valor),
            $valores,
        );
    }

    private function atividadeParticipaDaData(Atividade $atividade, string $data): bool
    {
        return match ($data) {
            '2026-10-20' => $atividade->participa_dia_20,
            '2026-10-21' => $atividade->participa_dia_21,
            default => false,
        };
    }

    private function protegerCelulaCsv(mixed $valor): string
    {
        $texto = (string) ($valor ?? '');

        return preg_match('/^[=+\\-@]/u', $texto) === 1 ? "'{$texto}" : $texto;
    }
}
