@extends('layouts.admin')

@section('titulo', $atividade->nome)

@section('conteudo')
    <section class="conteiner admin-pagina" aria-labelledby="titulo-atividade">
        <a class="admin-voltar" href="{{ route('admin.atividades.index') }}">← Voltar para atividades</a>

        <header class="admin-pagina__cabecalho">
            <p class="rotulo">Atividade cadastrada em {{ $atividade->created_at->format('d/m/Y \\à\\s H:i') }}</p>
            <h1 id="titulo-atividade">{{ $atividade->nome }}</h1>
        </header>

        <div class="admin-detalhes">
            <section class="admin-cartao" aria-labelledby="titulo-geral">
                <h2 id="titulo-geral">Dados gerais</h2>
                <dl class="admin-lista-dados">
                    <div><dt>Unidade acadêmica</dt><dd>{{ $atividade->curso->instituicao->nome }}</dd></div>
                    <div><dt>Curso principal</dt><dd>{{ $atividade->curso->nome }}</dd></div>
                    <div><dt>Professor responsável</dt><dd>{{ $atividade->professorResponsavel->nome }}</dd></div>
                    <div><dt>E-mail do responsável</dt><dd><a href="mailto:{{ $atividade->professorResponsavel->email }}">{{ $atividade->professorResponsavel->email }}</a></dd></div>
                    <div><dt>Dias de participação</dt><dd>{{ collect([($atividade->participa_dia_20 ? '20/10' : null), ($atividade->participa_dia_21 ? '21/10' : null)])->filter()->join(' e ') }}</dd></div>
                </dl>
            </section>

            <section class="admin-cartao" aria-labelledby="titulo-descricao">
                <h2 id="titulo-descricao">Descrição</h2>
                <h3>Resumo</h3>
                <p class="admin-texto-longo">{{ $atividade->resumo }}</p>
                @if ($atividade->observacoes)
                    <h3>Observações</h3>
                    <p class="admin-texto-longo">{{ $atividade->observacoes }}</p>
                @endif
            </section>

            @if ($atividade->instagram || $atividade->facebook || $atividade->site || $atividade->outros_links)
                <section class="admin-cartao" aria-labelledby="titulo-links">
                    <h2 id="titulo-links">Links da atividade</h2>
                    <dl class="admin-lista-dados">
                        @if ($atividade->instagram)<div><dt>Instagram</dt><dd>{{ $atividade->instagram }}</dd></div>@endif
                        @if ($atividade->facebook)<div><dt>Facebook</dt><dd>{{ $atividade->facebook }}</dd></div>@endif
                        @if ($atividade->site)<div><dt>Site</dt><dd><a href="{{ $atividade->site }}" rel="noopener noreferrer" target="_blank">{{ $atividade->site }}</a></dd></div>@endif
                        @if ($atividade->outros_links)<div><dt>Outros links</dt><dd class="admin-texto-longo">{{ $atividade->outros_links }}</dd></div>@endif
                    </dl>
                </section>
            @endif

            <section class="admin-cartao admin-cartao--amplo" aria-labelledby="titulo-participantes">
                <h2 id="titulo-participantes">Participantes</h2>
                @if ($atividade->alunos->isEmpty() && $atividade->professores->isEmpty())
                    <p>Nenhum participante adicional foi registrado.</p>
                @else
                    <div class="admin-tabela-container">
                        <table class="admin-tabela">
                            <thead>
                                <tr>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Nome completo</th>
                                    <th scope="col">Curso ou unidade acadêmica</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($atividade->alunos as $aluno)
                                    <tr><td>Aluno</td><td>{{ $aluno->nome }}</td><td>{{ $aluno->curso->nome }}</td></tr>
                                @endforeach
                                @foreach ($atividade->professores as $professor)
                                    <tr><td>Professor</td><td>{{ $professor->nome }}</td><td>{{ $professor->instituicao->nome }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </section>
@endsection
