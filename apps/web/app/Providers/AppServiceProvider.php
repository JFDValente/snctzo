<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('inscricoes', fn (Request $request) => Limit::perHour(
            max(1, (int) config('snctzo.inscricoes.limite_por_ip_por_hora')),
        )
            ->by($request->ip())
            ->response($this->respostaLimite()));

        RateLimiter::for('busca-professores', fn (Request $request) => Limit::perMinute(30)
            ->by($request->ip())
            ->response($this->respostaLimite()));

        RateLimiter::for('login-admin', fn (Request $request) => Limit::perMinute(5)
            ->by($request->ip().'|'.Str::lower((string) $request->input('email')))
            ->response($this->respostaLimiteLogin()));
    }

    /**
     * @return \Closure(Request, array<string, string>): JsonResponse
     */
    private function respostaLimite(): \Closure
    {
        return fn (Request $request, array $cabecalhos) => response()->json([
            'message' => 'Muitas tentativas. Aguarde alguns instantes e tente novamente.',
        ], 429, $cabecalhos);
    }

    /**
     * @return \Closure(Request, array<string, string>): RedirectResponse
     */
    private function respostaLimiteLogin(): \Closure
    {
        return fn (Request $request, array $cabecalhos) => back()
            ->withErrors(['email' => 'Muitas tentativas. Aguarde um minuto e tente novamente.'])
            ->onlyInput('email');
    }
}
