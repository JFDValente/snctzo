<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>@yield('titulo', 'Gerenciamento') — SNCTZO 2026</title>

        @vite(['resources/css/app.css', 'resources/css/admin.css'])
    </head>
    <body class="admin">
        <a class="atalho-conteudo" href="#conteudo">Ir para o conteúdo</a>

        <header class="admin__cabecalho">
            <div class="conteiner admin__cabecalho-conteudo">
                <a class="marca" href="{{ route('admin.atividades.index') }}">
                    <span class="marca__sigla">SNCTZO</span>
                    <span class="marca__ano">2026</span>
                </a>
                <p class="admin__titulo">Gerenciamento</p>
                <nav aria-label="Navegação administrativa">
                    <a class="admin__navegacao-link {{ request()->routeIs('admin.atividades.*') ? 'admin__navegacao-link--atual' : '' }}" href="{{ route('admin.atividades.index') }}">Atividades</a>
                </nav>
                <div class="admin__usuario">
                    <span>{{ auth()->user()->name }}</span>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="botao botao--texto" type="submit">Sair</button>
                    </form>
                </div>
            </div>
        </header>

        <main id="conteudo" class="admin__conteudo">
            @yield('conteudo')
        </main>
    </body>
</html>
