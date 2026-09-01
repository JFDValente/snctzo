<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituicoes', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'facebook', 'site', 'outros_links']);
        });

        Schema::table('professores', function (Blueprint $table) {
            $table->string('email', 254)->nullable()->change();
        });

        Schema::table('atividades', function (Blueprint $table) {
            $table->dropColumn('forma_apresentacao');
            $table->string('instagram')->nullable()->after('observacoes');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('site', 2048)->nullable()->after('facebook');
            $table->text('outros_links')->nullable()->after('site');
        });
    }

    public function down(): void
    {
        Schema::table('atividades', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'facebook', 'site', 'outros_links']);
            $table->string('forma_apresentacao', 16)->default('presencial')->after('nome');
        });

        Schema::table('professores', function (Blueprint $table) {
            $table->string('email', 254)->nullable(false)->change();
        });

        Schema::table('instituicoes', function (Blueprint $table) {
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('site', 2048)->nullable();
            $table->text('outros_links')->nullable();
        });
    }
};
