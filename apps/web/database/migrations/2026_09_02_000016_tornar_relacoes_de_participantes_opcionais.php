<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->foreignId('curso_id')->nullable()->change();
        });

        Schema::table('professores', function (Blueprint $table) {
            $table->foreignId('instituicao_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('professores', function (Blueprint $table) {
            $table->foreignId('instituicao_id')->nullable(false)->change();
        });

        Schema::table('alunos', function (Blueprint $table) {
            $table->foreignId('curso_id')->nullable(false)->change();
        });
    }
};
