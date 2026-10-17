<?php

use App\Http\Controllers\Admin\AtividadeController as AdminAtividadeController;
use App\Http\Controllers\Admin\AutenticacaoController as AdminAutenticacaoController;
use App\Http\Controllers\Admin\PresencaController as AdminPresencaController;
use App\Http\Controllers\BuscarProfessorController;
use App\Http\Controllers\CatalogoInscricaoController;
use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\InscricaoSucessoController;
use Illuminate\Http\Request;
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

Route::get('presenca', fn () => to_route('admin.presencas.index'))
    ->middleware(['auth', 'acesso.modulo:presencas'])
    ->name('presenca.atalho');

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
            Route::get('/', fn (Request $request) => redirect()->to(
                $request->user()->rotaInicialAdministrativa(),
            ))->name('inicio');
            Route::post('logout', [AdminAutenticacaoController::class, 'destroy'])->name('logout');
            Route::get('atividades', [AdminAtividadeController::class, 'index'])
                ->middleware('acesso.modulo:atividades')
                ->name('atividades.index');
            Route::get('atividades/exportar', [AdminAtividadeController::class, 'exportar'])
                ->middleware('acesso.modulo:atividades')
                ->name('atividades.exportar');
            Route::get('atividades/exportar-presencas', [AdminAtividadeController::class, 'exportarPresencas'])
                ->middleware('acesso.modulo:atividades')
                ->name('atividades.presencas.exportar');
            Route::get('atividades/{atividade}', [AdminAtividadeController::class, 'show'])
                ->whereNumber('atividade')
                ->middleware('acesso.modulo:atividades')
                ->name('atividades.show');
            Route::get('presencas', [AdminPresencaController::class, 'index'])
                ->middleware('acesso.modulo:presencas')
                ->name('presencas.index');
            Route::get('presencas/{atividade}', [AdminPresencaController::class, 'show'])
                ->whereNumber('atividade')
                ->middleware('acesso.modulo:presencas')
                ->name('presencas.show');
            Route::post('presencas/{atividade}/confirmar', [AdminPresencaController::class, 'confirmar'])
                ->whereNumber('atividade')
                ->middleware('acesso.modulo:presencas')
                ->name('presencas.confirmar');
            Route::get('presencas/{atividade}/situacao', [AdminPresencaController::class, 'situacao'])
                ->whereNumber('atividade')
                ->middleware('acesso.modulo:presencas')
                ->name('presencas.situacao');
            Route::post('presencas/{atividade}/participantes', [AdminPresencaController::class, 'adicionarParticipante'])
                ->whereNumber('atividade')
                ->middleware('acesso.modulo:presencas')
                ->name('presencas.participantes.store');
        });
    });
