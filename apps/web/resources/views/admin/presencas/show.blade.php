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

        @php($temErroDeParticipantes = $errors->has('participantes'))

        @if (session('sucessoParticipante'))
            <p class="admin-estado-vazio">{{ session('sucessoParticipante') }}</p>
        @endif

        <form action="{{ route('admin.presencas.confirmar', $atividade) }}" data-formulario-presenca data-situacao-url="{{ route('admin.presencas.situacao', ['atividade' => $atividade, 'data' => $data, 'turno' => $turno]) }}" data-versao="{{ $periodo?->updated_at?->toISOString() }}" method="POST">
            @csrf
            <input name="data" type="hidden" value="{{ $data }}">
            <input name="turno" type="hidden" value="{{ $turno }}">

            <div class="admin-presencas-lista">
                @foreach ($atividade->alunos as $aluno)
                    @include('admin.presencas.partials.cartao-participante', [
                        'participante' => $aluno,
                        'status' => $periodo?->presencasAlunos->firstWhere('aluno_id', $aluno->id)?->status,
                        'temErroDeParticipantes' => $temErroDeParticipantes,
                        'tipo' => 'aluno',
                    ])
                @endforeach
                @foreach ($atividade->professores as $professor)
                    @include('admin.presencas.partials.cartao-participante', [
                        'participante' => $professor,
                        'status' => $periodo?->presencasProfessores->firstWhere('professor_id', $professor->id)?->status,
                        'temErroDeParticipantes' => $temErroDeParticipantes,
                        'tipo' => 'professor',
                    ])
                @endforeach
            </div>

            <div class="admin-presencas-acoes">
                <button class="botao botao--secundario" data-abrir-participante-tardio type="button">Adicionar participante</button>
                <button class="botao" type="submit">Confirmar presença do turno</button>
            </div>
        </form>

        <dialog class="admin-dialogo" data-dialogo-participante-tardio aria-labelledby="titulo-participante-tardio">
            <div class="admin-dialogo__cabecalho">
                <h2 id="titulo-participante-tardio">Adicionar participante tardio</h2>
                <button class="botao--texto" data-fechar-dialogo type="button">Fechar</button>
            </div>
            <form class="admin-formulario admin-formulario--participante-tardio" data-formulario-participante-tardio action="{{ route('admin.presencas.participantes.store', $atividade) }}" method="POST">
                @csrf
                <input name="data" type="hidden" value="{{ $data }}">
                <input name="turno" type="hidden" value="{{ $turno }}">
                <div class="admin-campo">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo"><option value="aluno">Aluno</option><option value="professor">Professor</option></select>
                </div>
                <div class="admin-campo">
                    <label for="nome">Nome completo</label>
                    <input id="nome" name="nome" required>
                </div>
                <p class="admin-erro-formulario" data-erro-participante hidden></p>
                <button class="botao" type="submit">Incluir participante</button>
            </form>
        </dialog>

        <noscript>
            <form class="admin-formulario admin-formulario--participante-tardio" action="{{ route('admin.presencas.participantes.store', $atividade) }}" method="POST">
                @csrf
                <input name="data" type="hidden" value="{{ $data }}">
                <input name="turno" type="hidden" value="{{ $turno }}">
                <div class="admin-campo"><label for="tipo-sem-js">Tipo</label><select id="tipo-sem-js" name="tipo"><option value="aluno">Aluno</option><option value="professor">Professor</option></select></div>
                <div class="admin-campo"><label for="nome-sem-js">Nome completo</label><input id="nome-sem-js" name="nome" required value="{{ old('nome') }}"></div>
                @error('nome')<p class="admin-erro-formulario">{{ $message }}</p>@enderror
                <button class="botao" type="submit">Adicionar participante</button>
            </form>
        </noscript>

        @if (session('sucesso'))
            <dialog class="admin-dialogo" data-dialogo-retorno aria-labelledby="titulo-confirmacao-presenca">
                <h2 id="titulo-confirmacao-presenca">Presença confirmada</h2>
                <p class="admin-dialogo__mensagem">{{ session('sucesso') }}</p>
                <a class="botao" href="{{ route('admin.presencas.index', ['data' => $data, 'turno' => $turno]) }}">OK</a>
            </dialog>
            <noscript><p class="admin-estado-vazio">{{ session('sucesso') }} <a href="{{ route('admin.presencas.index', ['data' => $data, 'turno' => $turno]) }}">Voltar às atividades</a></p></noscript>
        @endif

        @if ($temErroDeParticipantes || $errors->has('data') || $errors->has('turno'))
            <dialog class="admin-dialogo" data-dialogo-retorno aria-labelledby="titulo-erro-confirmacao">
                <h2 id="titulo-erro-confirmacao">Não foi possível confirmar</h2>
                <p class="admin-dialogo__mensagem">{{ $errors->first('participantes') ?? $errors->first('data') ?? $errors->first('turno') }}</p>
                <button class="botao" data-fechar-dialogo type="button">OK</button>
            </dialog>
            <noscript><p class="admin-erro-formulario">{{ $errors->first('participantes') ?? $errors->first('data') ?? $errors->first('turno') }}</p></noscript>
        @endif
    </section>
@endsection
