@extends('layouts.admin')

@section('titulo', 'Atividades')

@section('conteudo')
    <section class="conteiner admin-pagina" aria-labelledby="titulo-atividades">
        <header class="admin-pagina__cabecalho">
            <p class="rotulo">Inscrições recebidas</p>
            <h1 id="titulo-atividades">Atividades</h1>
            <p>As atividades estão ordenadas pela data de inscrição, da mais antiga para a mais recente.</p>
        </header>

        <div class="admin-pagina__acoes">
            <a class="botao" href="{{ route('admin.atividades.exportar') }}">Exportar CSV</a>
        </div>

        @if ($atividades->isEmpty())
            <p class="admin-estado-vazio">Ainda não há atividades cadastradas.</p>
        @else
            <div class="admin-tabela-container">
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th scope="col">Nome da atividade</th>
                            <th scope="col">Unidade acadêmica</th>
                            <th scope="col">Curso</th>
                            <th scope="col">Data de inscrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($atividades as $atividade)
                            <tr>
                                <td><a href="{{ route('admin.atividades.show', $atividade) }}">{{ $atividade->nome }}</a></td>
                                <td>{{ $atividade->curso->instituicao->nome }}</td>
                                <td>{{ $atividade->curso->nome }}</td>
                                <td>{{ $atividade->created_at->format('d/m/Y \\à\\s H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="admin-paginacao">
                {{ $atividades->links('vendor.pagination.admin') }}
            </div>
        @endif
    </section>
@endsection
