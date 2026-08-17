<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use Illuminate\Database\Seeder;

class InstituicaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['FCBS', 'FCEE'] as $nome) {
            Instituicao::query()->firstOrCreate(['nome' => $nome]);
        }
    }
}
