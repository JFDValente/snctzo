<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ([
                'atividade_aluno',
                'atividade_professor',
                'atividades',
                'alunos',
                'professores',
                'cursos',
                'instituicoes',
            ] as $tabela) {
                DB::table($tabela)->truncate();
            }

            $agora = now();
            DB::table('instituicoes')->insert([
                ['nome' => 'FCBS (Faculdade de Ciências Biológicas e Saúde)', 'created_at' => $agora, 'updated_at' => $agora],
                ['nome' => 'FCEE (Faculdade de Ciências Exatas e Engenharias)', 'created_at' => $agora, 'updated_at' => $agora],
            ]);

            $instituicoes = DB::table('instituicoes')->pluck('id', 'nome');
            $cursos = [
                'FCBS (Faculdade de Ciências Biológicas e Saúde)' => ['Farmácia', 'Ciências Biológicas'],
                'FCEE (Faculdade de Ciências Exatas e Engenharias)' => [
                    'Computação (CC e TCADS)',
                    'Engenharia de Materiais',
                    'Engenharia de Produção',
                    'Engenharia Metalúrgica',
                    'Tecnologia em Construção Naval',
                ],
            ];

            foreach ($cursos as $instituicao => $nomes) {
                DB::table('cursos')->insert(array_map(
                    fn (string $nome) => [
                        'instituicao_id' => $instituicoes->get($instituicao),
                        'nome' => $nome,
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ],
                    $nomes,
                ));
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    public function down(): void
    {
        // O reset de dados foi autorizado para a primeira versão.
    }
};
