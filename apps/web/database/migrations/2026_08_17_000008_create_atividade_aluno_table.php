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
        Schema::create('atividade_aluno', function (Blueprint $table) {
            $table->foreignId('atividade_id')
                ->constrained('atividades')
                ->cascadeOnDelete();
            $table->foreignId('aluno_id')
                ->constrained('alunos')
                ->restrictOnDelete();

            $table->unique(['atividade_id', 'aluno_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividade_aluno');
    }
};
