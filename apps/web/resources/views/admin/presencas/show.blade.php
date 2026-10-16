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
                            <td>{{ $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status ? ucfirst($periodo->presencasAlunos->firstWhere('aluno_id', $aluno->id)->status) : 'Não registrado' }}</td>
                        </tr>
                    @endforeach
                    @foreach ($atividade->professores as $professor)
                        <tr>
                            <td>Professor</td>
                            <td>{{ $professor->nome }}</td>
                            <td>{{ $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status ? ucfirst($periodo->presencasProfessores->firstWhere('professor_id', $professor->id)->status) : 'Não registrado' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
