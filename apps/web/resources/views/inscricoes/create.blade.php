@extends('layouts.publico')

@section('titulo', 'Inscrição de atividade')

@section('conteudo')
    @php
        $encerramento = filled(config('snctzo.inscricoes.encerramento_em'))
            ? \Carbon\CarbonImmutable::parse(config('snctzo.inscricoes.encerramento_em'), config('app.timezone'))
            : null;
    @endphp

    <section class="inscricao-apresentacao">
        <div class="conteiner inscricao-apresentacao__conteudo">
            <div>
                <p class="rotulo">SNCTZO 2026</p>
                <h1>Inscrições de atividades SNCTZO 2026</h1>
                <p>Preencha uma inscrição para cada atividade que será apresentada no evento.</p>
                <p><strong>20/10, das 9h às 21h · 21/10, das 9h às 17h</strong></p>

                @if ($encerramento !== null)
                    <p class="inscricao-apresentacao__prazo">
                        Este formulário deve ser preenchido pelo responsável da instituição até
                        {{ $encerramento->translatedFormat('d/m/Y \à\s H\hi') }}.
                    </p>
                @endif
            </div>

            <figure class="inscricao-apresentacao__banner">
                <img src="{{ asset('imagens/banner-snctzo-2026.jpeg') }}" width="900" height="1600" alt="Cartaz da SNCTZO 2026, com o tema Ciência Delas, realizada em 20 e 21 de outubro no Centro Esportivo Miecimo da Silva.">
            </figure>
        </div>
    </section>

    <section class="conteiner inscricao" aria-labelledby="titulo-formulario">
        <div class="inscricao__cabecalho">
            <div>
                <p class="rotulo">Formulário de inscrição</p>
                <h2 id="titulo-formulario">Cadastre sua atividade</h2>
            </div>
            <p class="inscricao__orientacao">Os campos marcados com * são obrigatórios.</p>
        </div>

        <ol class="progresso" aria-label="Etapas do formulário">
            <li class="progresso__etapa" data-progresso="1" aria-current="step"><span>1</span> Avisos</li>
            <li class="progresso__etapa" data-progresso="2"><span>2</span> Identificação</li>
            <li class="progresso__etapa" data-progresso="3"><span>3</span> Atividade</li>
            <li class="progresso__etapa" data-progresso="4"><span>4</span> Realização</li>
            <li class="progresso__etapa" data-progresso="5"><span>5</span> Condições</li>
        </ol>

        <form id="formulario-inscricao" class="formulario" action="{{ route('inscricoes.store') }}" method="post" novalidate>
            @csrf
            <input name="token_submissao" type="hidden" value="{{ \Illuminate\Support\Str::uuid() }}">
            <input type="text" class="campo-armadilha" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

            @include('inscricoes.partials.etapa-1')
            @include('inscricoes.partials.etapa-2')
            @include('inscricoes.partials.etapa-3')
            @include('inscricoes.partials.etapa-4')
            @include('inscricoes.partials.etapa-5')

            <p class="mensagem-formulario" data-formulario-mensagem aria-live="polite"></p>
            <div class="formulario__navegacao">
                <button class="botao botao--secundario" type="button" data-anterior>Anterior</button>
                <button class="botao" type="button" data-proxima>Próxima</button>
                <button class="botao" type="submit" data-enviar>Enviar inscrição</button>
            </div>
        </form>
    </section>
@endsection
