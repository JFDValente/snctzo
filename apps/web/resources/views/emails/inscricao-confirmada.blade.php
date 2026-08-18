<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Confirmação de inscrição</title>
    </head>
    <body style="margin: 0; color: #17211d; background: #f5f7f6; font-family: Arial, sans-serif; line-height: 1.6;">
        <main style="width: min(100%, 680px); margin: 0 auto; padding: 24px;">
            <section style="padding: 32px; background: #ffffff; border: 1px solid #d9e1dd; border-radius: 12px;">
                <p style="margin: 0 0 8px; color: #176b49; font-size: 14px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase;">SNCTZO 2026</p>
                <h1 style="margin: 0 0 16px; font-size: 28px; line-height: 1.2;">Inscrição de atividade recebida</h1>
                <p style="margin: 0 0 24px;">Esta mensagem contém uma cópia completa da inscrição enviada.</p>

                <h2 style="font-size: 20px;">Instituição e curso</h2>
                <p>
                    <strong>Instituição:</strong> {{ $atividade->curso->instituicao->nome }}<br>
                    <strong>Curso principal:</strong> {{ $atividade->curso->nome }}
                </p>
                @if ($atividade->curso->instituicao->instagram || $atividade->curso->instituicao->facebook || $atividade->curso->instituicao->site || $atividade->curso->instituicao->outros_links)
                    <p><strong>Links e redes da instituição:</strong></p>
                    <ul>
                        @if ($atividade->curso->instituicao->instagram)
                            <li>Instagram: {{ $atividade->curso->instituicao->instagram }}</li>
                        @endif
                        @if ($atividade->curso->instituicao->facebook)
                            <li>Facebook: {{ $atividade->curso->instituicao->facebook }}</li>
                        @endif
                        @if ($atividade->curso->instituicao->site)
                            <li>Site: {{ $atividade->curso->instituicao->site }}</li>
                        @endif
                        @if ($atividade->curso->instituicao->outros_links)
                            <li>Outros links: {{ $atividade->curso->instituicao->outros_links }}</li>
                        @endif
                    </ul>
                @endif

                <h2 style="font-size: 20px;">Professor responsável</h2>
                <p>
                    <strong>Nome:</strong> {{ $atividade->professorResponsavel->nome }}<br>
                    <strong>E-mail:</strong> {{ $atividade->professorResponsavel->email }}
                </p>

                @php
                    $diasDeParticipacao = [];

                    if ($atividade->participa_dia_20) {
                        $diasDeParticipacao[] = '20/10/2026';
                    }

                    if ($atividade->participa_dia_21) {
                        $diasDeParticipacao[] = '21/10/2026';
                    }
                @endphp

                <h2 style="font-size: 20px;">Atividade</h2>
                <p>
                    <strong>Nome:</strong> {{ $atividade->nome }}<br>
                    <strong>Dias de participação:</strong> {{ implode(', ', $diasDeParticipacao) }}
                </p>
                <p><strong>Resumo:</strong><br>{!! nl2br(e($atividade->resumo)) !!}</p>
                @if ($atividade->observacoes)
                    <p><strong>Observações:</strong><br>{!! nl2br(e($atividade->observacoes)) !!}</p>
                @endif

                <h2 style="font-size: 20px;">Participantes</h2>
                <ul>
                    @forelse ($atividade->alunos as $aluno)
                        <li>Aluno: {{ $aluno->nome }} — {{ $aluno->curso->nome }}</li>
                    @empty
                    @endforelse
                    @foreach ($atividade->professores as $professor)
                        <li>Professor: {{ $professor->nome }} — {{ $professor->instituicao->nome }} — {{ $professor->email }}</li>
                    @endforeach
                </ul>

                <h2 style="font-size: 20px;">Declarações aceitas</h2>
                <p>Todos os itens abaixo foram confirmados na inscrição.</p>
                <ul>
                    <li>O professor responsável deve constar na lista de participantes caso deva ser incluído no resumo ou e-book.</li>
                    <li>A montagem dos estandes ocorrerá na tarde de 20/10/2026.</li>
                    <li>As atividades devem ser dinâmicas e interativas.</li>
                    <li>Não é permitido nenhum tipo de comércio no interior do ginásio durante o evento.</li>
                    <li>Os expositores devem chegar com pelo menos 30 minutos de antecedência.</li>
                    <li>Não são permitidas bebidas alcoólicas nem objetos perfurocortantes.</li>
                    <li>A realização das atividades é voluntária, sem remuneração ou auxílio financeiro da organização.</li>
                    <li>A atividade não faz divulgação ou manifestação partidária por citação direta ou indireta.</li>
                    <li>A atividade não faz apologia ao uso de material inflamável ou explosivo.</li>
                    <li>A atividade não faz apologia ao consumo de bebidas alcoólicas.</li>
                    <li>A atividade não faz apologia ao uso, fornecimento ou porte de drogas ilícitas, armas brancas ou armas de fogo.</li>
                    <li>A atividade não apresenta nudez nem qualquer outra ação de atentado ao pudor.</li>
                    <li>A atividade não incita violência nem discriminação de qualquer tipo.</li>
                    <li>O responsável e todas as pessoas do grupo cedem voluntária e gratuitamente seus direitos de imagem e concordam com a publicação do material no YouTube e nas redes sociais da SNCT ZO.</li>
                    <li>Um representante deve apresentar-se à comissão organizadora para confirmar a presença, ciente das consequências da ausência dessa confirmação.</li>
                    <li>O responsável leu e está ciente das informações necessárias à participação e à realização das atividades durante a SNCTZO 2026.</li>
                </ul>

                <p>
                    <strong>Versão dos termos:</strong> {{ $atividade->versao_termos }}<br>
                    <strong>Aceites registrados em:</strong> {{ $atividade->termos_aceitos_em->timezone(config('app.timezone'))->translatedFormat('d/m/Y \à\s H\hi') }}
                </p>
            </section>
        </main>
    </body>
</html>
