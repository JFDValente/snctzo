<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InscricaoSucessoController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (! session()->has('inscricao_enviada')) {
            return to_route('inscricoes.create');
        }

        return view('inscricoes.sucesso');
    }
}
