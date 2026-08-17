<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->char('token_submissao', 36)->unique();
            $table->foreignId('curso_id')
                ->constrained('cursos')
                ->restrictOnDelete();
            $table->foreignId('professor_responsavel_id')
                ->constrained('professores')
                ->restrictOnDelete();
            $table->string('nome');
            $table->boolean('participa_dia_20');
            $table->boolean('participa_dia_21');
            $table->text('resumo');
            $table->text('observacoes')->nullable();
            $table->dateTime('termos_aceitos_em');
            $table->string('versao_termos', 32);
            $table->dateTime('email_confirmacao_enviado_em')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        DB::statement(
            'ALTER TABLE atividades ADD CONSTRAINT atividades_participa_algum_dia_check '
            .'CHECK (participa_dia_20 = TRUE OR participa_dia_21 = TRUE)',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atividades');
    }
};
