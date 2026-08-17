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
        Schema::create('cursos', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->id();
            $table->foreignId('instituicao_id')
                ->constrained('instituicoes')
                ->restrictOnDelete();
            $table->string('nome', 150);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->unique(
                ['instituicao_id', 'nome'],
                'cursos_instituicao_nome_unico',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
