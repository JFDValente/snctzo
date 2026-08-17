<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Inscrições de atividades para a SNCTZO 2026">

        <title>@yield('titulo', 'Inscrições') — SNCTZO 2026</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <a class="atalho-conteudo" href="#conteudo">Ir para o conteúdo</a>

        <header class="cabecalho-publico">
            <div class="conteiner cabecalho-publico__conteudo">
                <a class="marca" href="{{ route('inicio') }}" aria-label="Página inicial da SNCTZO 2026">
                    <span class="marca__sigla">SNCTZO</span>
                    <span class="marca__ano">2026</span>
                </a>
            </div>
        </header>

        <main id="conteudo">
            @yield('conteudo')
        </main>

        <footer class="rodape-publico">
            <div class="conteiner">
                <p>Semana Nacional de Ciência e Tecnologia na Zona Oeste</p>
            </div>
        </footer>
    </body>
</html>
