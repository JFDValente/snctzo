<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AutenticacaoController extends Controller
{
    public function create()
    {
        return view('admin.autenticacao.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $identificador = $request->validated('identificador');
        $usuario = User::query()
            ->where(function ($consulta) use ($identificador): void {
                $consulta
                    ->whereRaw('LOWER(email) = ?', [$identificador])
                    ->orWhereRaw('LOWER(username) = ?', [$identificador]);
            })
            ->first();

        if ($usuario === null || ! Hash::check($request->validated('password'), $usuario->password)) {
            return back()
                ->withErrors(['identificador' => 'As credenciais informadas não foram reconhecidas.'])
                ->onlyInput('identificador');
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        $destinoPretendido = $request->session()->pull('url.intended');

        if ($this->podeUsarDestinoPretendido($usuario, $destinoPretendido)) {
            return redirect()->to($destinoPretendido);
        }

        return redirect()->to($usuario->rotaInicialAdministrativa());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('admin.login');
    }

    private function podeUsarDestinoPretendido(User $usuario, ?string $destino): bool
    {
        if ($destino === null) {
            return false;
        }

        $caminho = parse_url($destino, PHP_URL_PATH) ?: '';

        return ! ($usuario->perfil === User::PERFIL_OPERADOR_PRESENCA
            && Str::startsWith($caminho, '/admin/atividades'));
    }
}
