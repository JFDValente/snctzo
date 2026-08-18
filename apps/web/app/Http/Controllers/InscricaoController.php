<?php

namespace App\Http\Controllers;

use App\Actions\Inscricoes\CriarAtividade;
use App\Actions\Inscricoes\EnviarConfirmacaoDeInscricao;
use App\Http\Requests\StoreAtividadeRequest;
use App\Support\Inscricoes\PeriodoDeInscricao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class InscricaoController extends Controller
{
    public function __invoke(
        StoreAtividadeRequest $request,
        PeriodoDeInscricao $periodo,
        CriarAtividade $criarAtividade,
        EnviarConfirmacaoDeInscricao $enviarConfirmacao,
    ): JsonResponse|RedirectResponse {
        if (! $periodo->estaAberta()) {
            if (! $request->expectsJson()) {
                return back()->withErrors([
                    'inscricao' => 'As inscrições não estão abertas neste momento.',
                ])->withInput();
            }

            return response()->json([
                'message' => 'As inscrições não estão abertas neste momento.',
            ], 422);
        }

        $atividade = $criarAtividade($request->validated());
        $enviarConfirmacao($atividade);
        $request->session()->flash('inscricao_enviada', true);

        if (! $request->expectsJson()) {
            return to_route('inscricoes.sucesso');
        }

        return response()->json([
            'message' => 'Inscrição enviada com sucesso.',
            'url_sucesso' => route('inscricoes.sucesso'),
        ], 201);
    }
}
