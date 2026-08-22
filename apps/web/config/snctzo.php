<?php

return [
    'evento' => [
        'nome' => 'SNCTZO 2026',
        'local' => 'Centro Esportivo Miécimo da Silva',
        'dias' => [
            '2026-10-20' => env('EVENTO_HORARIO_DIA_20'),
            '2026-10-21' => env('EVENTO_HORARIO_DIA_21'),
        ],
    ],

    'inscricoes' => [
        'abertura_em' => env('INSCRICOES_ABERTURA_EM'),
        'encerramento_em' => env('INSCRICOES_ENCERRAMENTO_EM'),
        'limite_por_ip_por_hora' => (int) env(
            'RATE_LIMIT_INSCRICOES_POR_HORA',
            100,
        ),
    ],

    'termos' => [
        'versao' => env('TERMOS_VERSAO', '2026.2'),
    ],

    'email' => [
        'confirmacao_ativa' => env('EMAIL_CONFIRMACAO_ATIVA', false),
    ],
];
