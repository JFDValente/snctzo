<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarAcessoAoModulo
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        abort_unless($request->user()?->podeAcessarModulo($modulo), 403);

        return $next($request);
    }
}
