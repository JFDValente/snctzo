<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('atividade_aluno', function (Blueprint $table) {
            $table->boolean('cadastrado_tardiamente')->default(false);
        });

        Schema::table('atividade_professor', function (Blueprint $table) {
            $table->boolean('cadastrado_tardiamente')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atividade_aluno', function (Blueprint $table) {
            $table->dropColumn('cadastrado_tardiamente');
        });

        Schema::table('atividade_professor', function (Blueprint $table) {
            $table->dropColumn('cadastrado_tardiamente');
        });
    }
};
