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

        <form class="admin-formulario admin-formulario--participante-tardio" action="{{ route('admin.presencas.participantes.store', $atividade) }}" method="POST">
            @csrf
            <input name="data" type="hidden" value="{{ $data }}">
            <input name="turno" type="hidden" value="{{ $turno }}">
            <div class="admin-campo">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo"><option value="aluno">Aluno</option><option value="professor">Professor</option></select>
            </div>
            <div class="admin-campo">
                <label for="nome">Nome completo</label>
                <input id="nome" name="nome" required value="{{ old('nome') }}">
            </div>
            @error('nome')<p>{{ $message }}</p>@enderror
            <button class="botao" type="submit">Adicionar participante</button>
        </form>

        <form action="{{ route('admin.presencas.confirmar', $atividade) }}" data-formulario-presenca data-situacao-url="{{ route('admin.presencas.situacao', ['atividade' => $atividade, 'data' => $data, 'turno' => $turno]) }}" data-versao="{{ $periodo?->updated_at?->toISOString() }}" method="POST">
            @csrf
            <input name="data" type="hidden" value="{{ $data }}">
            <input name="turno" type="hidden" value="{{ $turno }}">

            <div class="admin-presencas-lista">
                @foreach ($atividade->alunos as $aluno)
                    <article class="admin-presenca-item">
                        <div>
                            <strong>{{ $aluno->nome }}</strong>
                            <span>Aluno{{ $aluno->pivot->cadastrado_tardiamente ? ' (cadastro tardio)' : '' }}</span>
                        </div>
                        <fieldset class="admin-situacao" data-situacao-presenca>
                            <legend>Situação</legend>
                            <label><input name="alunos[{{ $aluno->id }}]" type="radio" value="presente" required @checked(old("alunos.{$aluno->id}", $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status) === 'presente')> Presente</label>
                            <label><input name="alunos[{{ $aluno->id }}]" type="radio" value="ausente" @checked(old("alunos.{$aluno->id}", $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status) === 'ausente')> Ausente</label>
                        </fieldset>
                    </article>
                @endforeach
                @foreach ($atividade->professores as $professor)
                    <article class="admin-presenca-item">
                        <div>
                            <strong>{{ $professor->nome }}</strong>
                            <span>Professor{{ $professor->pivot->cadastrado_tardiamente ? ' (cadastro tardio)' : '' }}</span>
                        </div>
                        <fieldset class="admin-situacao" data-situacao-presenca>
                            <legend>Situação</legend>
                            <label><input name="professores[{{ $professor->id }}]" type="radio" value="presente" required @checked(old("professores.{$professor->id}", $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status) === 'presente')> Presente</label>
                            <label><input name="professores[{{ $professor->id }}]" type="radio" value="ausente" @checked(old("professores.{$professor->id}", $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status) === 'ausente')> Ausente</label>
                        </fieldset>
                    </article>
                @endforeach
            </div>

            <button class="botao" type="submit">Confirmar presença do turno</button>
        </form>
    </section>
@endsection
