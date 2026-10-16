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
        Schema::create('periodos_de_presenca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atividade_id')
                ->constrained('atividades')
                ->cascadeOnDelete();
            $table->date('data');
            $table->string('turno', 10);
            $table->foreignId('confirmado_por_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->dateTime('confirmado_em');
            $table->timestamps();

            $table->unique(['atividade_id', 'data', 'turno']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_de_presenca');
    }
};
