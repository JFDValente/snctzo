<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('alunos')->update(['curso_id' => null]);
    }

    public function down(): void
    {
        // A associação anterior não pode ser reconstituída com segurança.
    }
};
