<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Presencas\ConfirmarPeriodoDePresenca;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmarPresencaRequest;
use App\Models\Atividade;
use App\Support\Presencas\CalendarioDePresencas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
                'periodosDePresenca' => fn ($consulta) => $consulta
                    ->where('data', $data)
                    ->where('turno', $turno),
            ])
            ->where($colunaDoDia, true)
            ->orderBy('created_at')
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
            'alunos',
            'professores',
            'periodosDePresenca' => fn ($consulta) => $consulta
                ->with(['confirmadoPor', 'presencasAlunos', 'presencasProfessores'])
                ->where('data', $data)
                ->where('turno', $turno),
        ]);

        $periodo = $atividade->periodosDePresenca->first();

        return view('admin.presencas.show', compact(
            'atividade',
            'calendario',
            'data',
            'periodo',
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
}
