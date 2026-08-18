<?php

use App\Http\Controllers\BuscarProfessorController;
use App\Http\Controllers\CatalogoInscricaoController;
use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\InscricaoSucessoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inscricoes')->name('inicio');

Route::view('inscricoes', 'inscricoes.create')->name('inscricoes.create');

Route::get('inscricoes/sucesso', InscricaoSucessoController::class)->name('inscricoes.sucesso');

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
    ->middleware('throttle:busca-professores')
    ->name('inscricoes.professores.busca');

Route::post('inscricoes', InscricaoController::class)
    ->middleware('throttle:inscricoes')
    ->name('inscricoes.store');
