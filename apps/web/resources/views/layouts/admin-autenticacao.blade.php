<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>@yield('titulo', 'Acesso administrativo') — SNCTZO 2026</title>

        @vite(['resources/css/app.css', 'resources/css/admin.css'])
    </head>
    <body class="admin admin--autenticacao">
        <main id="conteudo" class="admin-autenticacao">
            @yield('conteudo')
        </main>
    </body>
</html>
