<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $agora = now();
        $instituicoes = DB::table('instituicoes')
            ->whereIn('nome', [
                'FCBS (Faculdade de Ciências Biológicas e Saúde)',
                'FCEE (Faculdade de Ciências Exatas e Engenharias)',
            ])
            ->pluck('id', 'nome');

        $cursos = [
            'FCBS (Faculdade de Ciências Biológicas e Saúde)' => [
                'Ciências Biológicas (com ênfase em Biotecnologia e Produção)',
                'Ciências Biológicas (com ênfase em Gestão Ambiental)',
                'Farmácia',
            ],
            'FCEE (Faculdade de Ciências Exatas e Engenharias)' => [
                'Ciência da Computação',
                'Engenharia de Materiais',
                'Engenharia de Produção',
                'Engenharia Metalúrgica',
                'Tecnologia em Análise e Desenvolvimento de Sistemas',
                'Tecnologia em Construção Naval',
            ],
        ];

        foreach ($cursos as $nomeInstituicao => $nomesDosCursos) {
            $instituicaoId = $instituicoes->get($nomeInstituicao);

            if ($instituicaoId === null) {
                throw new RuntimeException("A instituição {$nomeInstituicao} não foi provisionada.");
            }

            DB::table('cursos')->insertOrIgnore(array_map(
                fn (string $nome) => [
                    'instituicao_id' => $instituicaoId,
                    'nome' => $nome,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ],
                $nomesDosCursos,
            ));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserva dados operacionais já referenciados por inscrições.
    }
};
