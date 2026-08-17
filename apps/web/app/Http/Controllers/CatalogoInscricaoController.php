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
            ->select(['id', 'nome', 'instagram', 'facebook', 'site', 'outros_links'])
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
            ->whereIn('curso_id', $cursos->modelKeys())
            ->orderBy('nome')
            ->get();

        return response()->json([
            'instituicao' => $instituicao->only([
                'id',
                'nome',
                'instagram',
                'facebook',
                'site',
                'outros_links',
            ]),
            'cursos' => $cursos,
            'alunos' => $alunos,
        ]);
    }
}
