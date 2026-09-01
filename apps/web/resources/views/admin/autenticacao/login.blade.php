@extends('layouts.admin-autenticacao')

@section('titulo', 'Acesso administrativo')

@section('conteudo')
    <section class="admin-autenticacao__cartao" aria-labelledby="titulo-login">
        <a class="marca" href="{{ route('inicio') }}" aria-label="Página pública da SNCTZO 2026">
            <span class="marca__sigla">SNCTZO</span>
            <span class="marca__ano">2026</span>
        </a>

        <h1 id="titulo-login">Gerenciamento</h1>
        <p>Acesse com seu e-mail e senha autorizados.</p>

        <form class="admin-formulario" action="{{ route('admin.login.autenticar') }}" method="POST">
            @csrf

            <div class="campo">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')
                    <p class="mensagem-campo mensagem-campo--erro">{{ $message }}</p>
                @enderror
            </div>

            <div class="campo">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>

            <button class="botao" type="submit">Entrar</button>
        </form>
    </section>
@endsection
