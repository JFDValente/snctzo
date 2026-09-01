@extends('layouts.publico')

@section('titulo', 'Inscrição enviada')

@section('conteudo')
    <section class="conteiner secao-informativa" aria-labelledby="titulo-sucesso">
        <div class="aviso aviso--sucesso">
            <div>
                <h1 id="titulo-sucesso">Inscrição enviada com sucesso</h1>
                <a class="botao botao--sucesso" href="{{ route('inscricoes.create') }}">Deseja inscrever outra atividade?</a>
            </div>
        </div>
    </section>
@endsection
