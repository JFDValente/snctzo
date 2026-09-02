<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Instituicao;
use Illuminate\Http\JsonResponse;

class CatalogoInscricaoController extends Controller
{
    public function instituicoes(): JsonResponse
    {
        $instituicoes = Instituicao::query()
            ->select(['id', 'nome'])
            ->orderBy('nome')
            ->get();

        return response()->json(['instituicoes' => $instituicoes]);
    }

    public function detalhesDaInstituicao(Instituicao $instituicao): JsonResponse
    {
        $cursos = $instituicao->cursos()
            ->select(['id', 'instituicao_id', 'nome'])
            ->orderBy('nome')
            ->get();

        $alunos = Aluno::query()
            ->select(['id', 'curso_id', 'nome'])
            ->where(function ($consulta) use ($cursos): void {
                $consulta->whereIn('curso_id', $cursos->modelKeys())
                    ->orWhereNull('curso_id');
            })
            ->orderBy('nome')
            ->get();

        return response()->json([
            'instituicao' => $instituicao->only(['id', 'nome']),
            'cursos' => $cursos,
            'alunos' => $alunos,
        ]);
    }
}
