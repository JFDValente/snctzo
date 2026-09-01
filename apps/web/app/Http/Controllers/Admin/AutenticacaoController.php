<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutenticacaoController extends Controller
{
    public function create()
    {
        return view('admin.autenticacao.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credenciais = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credenciais)) {
            return back()
                ->withErrors(['email' => 'As credenciais informadas não foram reconhecidas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.atividades.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('admin.login');
    }
}
