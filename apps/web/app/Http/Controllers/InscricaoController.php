<?php

namespace App\Http\Controllers;

use App\Actions\Inscricoes\CriarAtividade;
use App\Http\Requests\StoreAtividadeRequest;
use App\Support\Inscricoes\PeriodoDeInscricao;
use Illuminate\Http\JsonResponse;

class InscricaoController extends Controller
{
    public function __invoke(
        StoreAtividadeRequest $request,
        PeriodoDeInscricao $periodo,
        CriarAtividade $criarAtividade,
    ): JsonResponse {
        if (! $periodo->estaAberta()) {
            return response()->json([
                'message' => 'As inscrições não estão abertas neste momento.',
            ], 422);
        }

        $criarAtividade($request->validated());

        return response()->json([
            'message' => 'Inscrição enviada com sucesso.',
        ], 201);
    }
}
