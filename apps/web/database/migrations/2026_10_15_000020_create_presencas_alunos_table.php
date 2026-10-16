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
        Schema::create('presencas_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_de_presenca_id')
                ->constrained('periodos_de_presenca')
                ->cascadeOnDelete();
            $table->foreignId('aluno_id')
                ->constrained('alunos')
                ->restrictOnDelete();
            $table->enum('status', ['presente', 'ausente']);
            $table->timestamps();

            $table->unique(['periodo_de_presenca_id', 'aluno_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presencas_alunos');
    }
};
