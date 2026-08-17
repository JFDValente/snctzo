@extends('layouts.publico')

@section('titulo', 'Inscrições de atividades')

@section('conteudo')
    <section class="apresentacao" aria-labelledby="titulo-pagina">
        <div class="conteiner apresentacao__conteudo">
            <p class="rotulo">Inscrições de atividades</p>
            <h1 id="titulo-pagina">SNCTZO 2026</h1>
            <p class="apresentacao__resumo">
                Cadastre a atividade que será apresentada na Semana Nacional de Ciência e Tecnologia na Zona Oeste.
            </p>

            <dl class="dados-evento" aria-label="Informações do evento">
                <div class="dados-evento__item">
                    <dt>Datas</dt>
                    <dd>
                        <time datetime="2026-10-20">20</time> e
                        <time datetime="2026-10-21">21 de outubro de 2026</time>
                    </dd>
                </div>
                <div class="dados-evento__item">
                    <dt>Local</dt>
                    <dd>Centro Esportivo Miécimo da Silva</dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="conteiner secao-informativa" aria-labelledby="titulo-status">
        <div class="aviso aviso--informativo">
            <div>
                <h2 id="titulo-status">Formulário em preparação</h2>
                <p>A estrutura de inscrição será disponibilizada nas próximas etapas de implementação.</p>
            </div>
        </div>
    </section>
@endsection
