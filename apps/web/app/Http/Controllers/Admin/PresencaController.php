<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use App\Support\Presencas\CalendarioDePresencas;
use Illuminate\Contracts\View\View;
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
}
