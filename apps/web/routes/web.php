<?php

use App\Http\Controllers\Admin\AtividadeController as AdminAtividadeController;
use App\Http\Controllers\Admin\AutenticacaoController as AdminAutenticacaoController;
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

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('login', [AdminAutenticacaoController::class, 'create'])->name('login');
            Route::post('login', [AdminAutenticacaoController::class, 'store'])
                ->middleware('throttle:login-admin')
                ->name('login.autenticar');
        });

        Route::middleware('auth')->group(function (): void {
            Route::redirect('/', '/admin/atividades')->name('inicio');
            Route::post('logout', [AdminAutenticacaoController::class, 'destroy'])->name('logout');
            Route::get('atividades', [AdminAtividadeController::class, 'index'])->name('atividades.index');
            Route::get('atividades/{atividade}', [AdminAtividadeController::class, 'show'])
                ->whereNumber('atividade')
                ->name('atividades.show');
        });
    });
