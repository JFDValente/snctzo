@extends('layouts.admin')

@section('titulo', 'Registrar presença')

@section('conteudo')
    <section class="conteiner admin-pagina" aria-labelledby="titulo-presenca">
        <a class="admin-voltar" href="{{ route('admin.presencas.index', ['data' => $data, 'turno' => $turno]) }}">← Voltar às atividades</a>

        <header class="admin-pagina__cabecalho">
            <p class="rotulo">{{ $calendario->rotuloDoTurno($turno) }} — {{ \Carbon\CarbonImmutable::parse($data)->format('d/m/Y') }}</p>
            <h1 id="titulo-presenca">{{ $atividade->nome }}</h1>
            @if ($periodo)
                <p>Última confirmação por {{ $periodo->confirmadoPor->name }}, em {{ $periodo->confirmado_em->format('d/m/Y \à\s H:i') }}.</p>
            @else
                <p>Este período ainda não foi confirmado.</p>
            @endif
        </header>

        @if (session('sucesso'))
            <p class="admin-estado-vazio">{{ session('sucesso') }}</p>
        @endif

        @if ($errors->has('participantes'))
            <p class="admin-estado-vazio">{{ $errors->first('participantes') }}</p>
        @endif

        <form action="{{ route('admin.presencas.confirmar', $atividade) }}" data-formulario-presenca data-situacao-url="{{ route('admin.presencas.situacao', ['atividade' => $atividade, 'data' => $data, 'turno' => $turno]) }}" data-versao="{{ $periodo?->updated_at?->toISOString() }}" method="POST">
            @csrf
            <input name="data" type="hidden" value="{{ $data }}">
            <input name="turno" type="hidden" value="{{ $turno }}">

            <div class="admin-tabela-container">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th scope="col">Tipo</th>
                            <th scope="col">Participante</th>
                            <th scope="col">Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($atividade->alunos as $aluno)
                            <tr>
                                <td>Aluno</td>
                                <td>{{ $aluno->nome }}</td>
                                <td>
                                    <select name="alunos[{{ $aluno->id }}]" aria-label="Presença de {{ $aluno->nome }}">
                                        <option value="">Não registrado</option>
                                        <option value="presente" @selected(old("alunos.{$aluno->id}", $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status) === 'presente')>Presente</option>
                                        <option value="ausente" @selected(old("alunos.{$aluno->id}", $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status) === 'ausente')>Ausente</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($atividade->professores as $professor)
                            <tr>
                                <td>Professor</td>
                                <td>{{ $professor->nome }}</td>
                                <td>
                                    <select name="professores[{{ $professor->id }}]" aria-label="Presença de {{ $professor->nome }}">
                                        <option value="">Não registrado</option>
                                        <option value="presente" @selected(old("professores.{$professor->id}", $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status) === 'presente')>Presente</option>
                                        <option value="ausente" @selected(old("professores.{$professor->id}", $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status) === 'ausente')>Ausente</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <label><input name="confirmar" type="checkbox" value="1"> Confirmo o registro de presença deste turno.</label>
            <button class="botao" type="submit">Confirmar presença do turno</button>
        </form>
    </section>
@endsection
