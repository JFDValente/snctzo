<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AtividadeController extends Controller
{
    public function index(): View
    {
        $atividades = Atividade::query()
            ->with(['curso.instituicao'])
            ->orderBy('created_at')
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
            ], ';');

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
                        fputcsv($arquivo, $this->linhaDaExportacao($atividade), ';');
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

    private function protegerCelulaCsv(mixed $valor): string
    {
        $texto = (string) ($valor ?? '');

        return preg_match('/^[=+\\-@]/u', $texto) === 1 ? "'{$texto}" : $texto;
    }
}
