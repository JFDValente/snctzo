<?php

use App\Http\Controllers\BuscarProfessorController;
use App\Http\Controllers\CatalogoInscricaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

Route::prefix('inscricoes/catalogo')
    ->name('inscricoes.catalogo.')
    ->group(function (): void {
        Route::get('instituicoes', [CatalogoInscricaoController::class, 'instituicoes'])
            ->name('instituicoes');
        Route::get('instituicoes/{instituicao}', [CatalogoInscricaoController::class, 'detalhesDaInstituicao'])
            ->whereNumber('instituicao')
            ->name('instituicao');
    });

Route::get('inscricoes/professores/busca', BuscarProfessorController::class)
    ->middleware('throttle:30,1')
    ->name('inscricoes.professores.busca');
