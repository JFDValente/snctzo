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

        DB::table('instituicoes')->insertOrIgnore([
            [
                'nome' => 'FCBS (Faculdade de Ciências Biológicas e Saúde)',
                'instagram' => '@fcbsuerjzo',
                'facebook' => 'https://www.facebook.com/uezouniversidade/',
                'site' => 'https://www.fcbs.uerj.br/',
                'outros_links' => '@extensao.fcbs.uerj',
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'nome' => 'FCEE (Faculdade de Ciências Exatas e Engenharias)',
                'instagram' => '@extensao.fcee.uerj',
                'facebook' => 'https://www.facebook.com/uezouniversidade/',
                'site' => 'https://www.fcee.uerj.br/',
                'outros_links' => null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserva dados operacionais já referenciados por inscrições.
    }
};
