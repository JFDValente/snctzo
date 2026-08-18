<?php

use App\Http\Controllers\BuscarProfessorController;
use App\Http\Controllers\CatalogoInscricaoController;
use App\Http\Controllers\InscricaoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/inscricoes')->name('inicio');

Route::view('inscricoes', 'inscricoes.create')->name('inscricoes.create');

Route::view('inscricoes/sucesso', 'inscricoes.sucesso')->name('inscricoes.sucesso');

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

Route::post('inscricoes', InscricaoController::class)
    ->middleware('throttle:'.config('snctzo.inscricoes.limite_por_ip_por_hora').',60')
    ->name('inscricoes.store');
