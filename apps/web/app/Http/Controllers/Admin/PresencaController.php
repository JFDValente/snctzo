<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Presencas\AdicionarParticipanteTardio;
use App\Actions\Presencas\ConfirmarPeriodoDePresenca;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdicionarParticipanteTardioRequest;
use App\Http\Requests\Admin\ConfirmarPresencaRequest;
use App\Models\Atividade;
use App\Support\Presencas\CalendarioDePresencas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PresencaController extends Controller
{
    public function index(Request $request, CalendarioDePresencas $calendario): View
    {
        $opcoesDeData = $calendario->opcoesDeData();
        $data = $request->string('data')->toString() ?: $opcoesDeData[0]['valor'];
        $turnos = $calendario->turnosDaData($data);
        $turno = $request->string('turno')->toString() ?: ($turnos[0] ?? '');

        abort_unless($calendario->periodoExiste($data, $turno), 404);

        $colunaDoDia = match ($data) {
            '2026-10-20' => 'participa_dia_20',
            '2026-10-21' => 'participa_dia_21',
        };

        $atividades = Atividade::query()
            ->with([
                'curso.instituicao',
                'professorResponsavel',
                'periodosDePresenca' => fn ($consulta) => $consulta
                    ->where('data', $data)
                    ->where('turno', $turno),
            ])
            ->where($colunaDoDia, true)
            ->orderBy('nome')
            ->orderBy('id')
            ->get();

        return view('admin.presencas.index', compact(
            'atividades',
            'calendario',
            'data',
            'opcoesDeData',
            'turno',
            'turnos',
        ));
    }

    public function show(Request $request, Atividade $atividade, CalendarioDePresencas $calendario): View
    {
        $data = $request->string('data')->toString();
        $turno = $request->string('turno')->toString();

        abort_unless($calendario->periodoExiste($data, $turno), 404);

        $colunaDoDia = match ($data) {
            '2026-10-20' => 'participa_dia_20',
            '2026-10-21' => 'participa_dia_21',
        };

        abort_unless($atividade->{$colunaDoDia}, 404);

        $atividade->load([
            'alunos' => fn ($consulta) => $consulta->orderBy('nome')->orderBy('id'),
            'professores' => fn ($consulta) => $consulta->orderBy('nome')->orderBy('id'),
            'periodosDePresenca' => fn ($consulta) => $consulta
                ->with(['confirmadoPor', 'presencasAlunos', 'presencasProfessores'])
                ->where('data', $data)
                ->where('turno', $turno),
        ]);

        $periodo = $atividade->periodosDePresenca->first();
        $participantes = $atividade->alunos
            ->map(fn ($aluno) => [
                'participante' => $aluno,
                'status' => $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status,
                'tipo' => 'aluno',
            ])
            ->concat($atividade->professores->map(fn ($professor) => [
                'participante' => $professor,
                'status' => $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status,
                'tipo' => 'professor',
            ]))
            ->sortBy(fn (array $item) => mb_strtolower(Str::ascii($item['participante']->nome)))
            ->values();

        return view('admin.presencas.show', compact(
            'atividade',
            'calendario',
            'data',
            'periodo',
            'participantes',
            'turno',
        ));
    }

    public function confirmar(ConfirmarPresencaRequest $request, Atividade $atividade, ConfirmarPeriodoDePresenca $confirmar): RedirectResponse
    {
        $confirmar($atividade, $request->validated());

        return to_route('admin.presencas.show', [
            'atividade' => $atividade,
            'data' => $request->input('data'),
            'turno' => $request->input('turno'),
        ])->with('sucesso', 'Presença confirmada com sucesso.');
    }

    public function situacao(Request $request, Atividade $atividade, CalendarioDePresencas $calendario): JsonResponse
    {
        $data = $request->string('data')->toString();
        $turno = $request->string('turno')->toString();

        abort_unless($calendario->periodoExiste($data, $turno), 404);

        $periodo = $atividade->periodosDePresenca()
            ->with('confirmadoPor')
            ->where('data', $data)
            ->where('turno', $turno)
            ->first();

        return response()->json([
            'versao' => $periodo?->updated_at?->toISOString(),
            'confirmado_por' => $periodo?->confirmadoPor?->name,
            'confirmado_em' => $periodo?->confirmado_em?->format('d/m/Y \à\s H:i'),
        ]);
    }

    public function adicionarParticipante(AdicionarParticipanteTardioRequest $request, Atividade $atividade, AdicionarParticipanteTardio $adicionar): JsonResponse|RedirectResponse
    {
        $tipo = $request->string('tipo')->toString();
        $participante = $adicionar($atividade, $tipo, $request->string('nome')->toString());

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.presencas.partials.cartao-participante', [
                    'participante' => $participante,
                    'status' => null,
                    'tipo' => $tipo,
                ])->render(),
            ], 201);
        }

        return to_route('admin.presencas.show', [
            'atividade' => $atividade,
            'data' => $request->input('data'),
            'turno' => $request->input('turno'),
        ])->with('sucessoParticipante', 'Participante tardio incluído com sucesso.');
    }
}
