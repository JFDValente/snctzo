<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuscarProfessorRequest;
use App\Models\Professor;
use Illuminate\Http\JsonResponse;

class BuscarProfessorController extends Controller
{
    public function __invoke(BuscarProfessorRequest $request): JsonResponse
    {
        $professor = Professor::query()
            ->with('instituicao:id,nome')
            ->select(['id', 'instituicao_id', 'nome'])
            ->where('email', $request->validated('email'))
            ->first();

        if ($professor === null) {
            return response()->json(['professor' => null]);
        }

        return response()->json([
            'professor' => [
                'id' => $professor->id,
                'nome' => $professor->nome,
                'instituicao' => [
                    'id' => $professor->instituicao->id,
                    'nome' => $professor->instituicao->nome,
                ],
            ],
        ]);
    }
}
