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

        if ($instituicoes->count() !== 2) {
            throw new RuntimeException('As unidades acadêmicas FCBS e FCEE precisam estar provisionadas antes dos cursos.');
        }

        DB::table('cursos')->delete();

        $cursos = [
            'FCBS (Faculdade de Ciências Biológicas e Saúde)' => [
                'Farmácia',
                'Ciências Biológicas',
                'Pós Graduação em Ciência e Tecnologia Ambiente (PGCTA)',
            ],
            'FCEE (Faculdade de Ciências Exatas e Engenharias)' => [
                'Computação (CC e TCADS)',
                'Engenharia de Materiais',
                'Engenharia de Produção',
                'Engenharia Metalúrgica',
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
        // O catálogo anterior é deliberadamente substituído por esta migration.
    }
};
