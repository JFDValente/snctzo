@extends('layouts.admin')

@section('titulo', 'Controle de presença')

@section('conteudo')
    <section class="conteiner admin-pagina" aria-labelledby="titulo-presencas">
        <header class="admin-pagina__cabecalho">
            <p class="rotulo">Operação do evento</p>
            <h1 id="titulo-presencas">Controle de presença</h1>
            <p>Selecione o dia e o turno antes de registrar a presença de uma atividade.</p>
        </header>

        <form class="admin-formulario admin-formulario--filtros-presenca" action="{{ route('admin.presencas.index') }}" method="GET">
            <div class="campo">
                <label for="data">Data</label>
                <select id="data" name="data" onchange="this.form.submit()">
                    @foreach ($opcoesDeData as $opcao)
                        <option value="{{ $opcao['valor'] }}" @selected($data === $opcao['valor'])>{{ $opcao['rotulo'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label for="turno">Turno</label>
                <select id="turno" name="turno" onchange="this.form.submit()">
                    @foreach ($turnos as $opcao)
                        <option value="{{ $opcao }}" @selected($turno === $opcao)>{{ $calendario->rotuloDoTurno($opcao) }}</option>
                    @endforeach
                </select>
            </div>
            <noscript><button class="botao" type="submit">Atualizar atividades</button></noscript>
        </form>

        @if ($atividades->isEmpty())
            <p class="admin-estado-vazio">Não há atividades agendadas para esta data.</p>
        @else
            <section class="admin-busca-local" data-lista-atividades>
                <div class="campo">
                    <label for="buscar-atividade">Buscar professor responsável</label>
                    <input id="buscar-atividade" data-busca-atividades type="search" placeholder="Digite parte do nome do professor" autocomplete="off">
                </div>
                <p class="admin-busca-local__vazio" data-busca-atividades-vazio hidden>Nenhuma atividade encontrada.</p>

                <div class="admin-tabela-container">
                    <table class="admin-tabela">
                        <thead>
                            <tr>
                                <th scope="col">Atividade</th>
                                <th scope="col">Professor responsável</th>
                                <th scope="col">Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($atividades as $atividade)
                                <tr data-atividade data-professor-responsavel="{{ $atividade->professorResponsavel?->nome }}">
                                    <td>
                                        <a href="{{ route('admin.presencas.show', ['atividade' => $atividade, 'data' => $data, 'turno' => $turno]) }}">{{ $atividade->nome }}</a>
                                    </td>
                                    <td>{{ $atividade->professorResponsavel?->nome ?? 'Não informado' }}</td>
                                    <td>{{ $atividade->periodosDePresenca->isEmpty() ? 'Pendente' : 'Confirmado' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </section>
@endsection
