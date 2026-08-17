<?php

namespace App\Support\Inscricoes;

use Carbon\CarbonImmutable;

class PeriodoDeInscricao
{
    public const ABERTA = 'aberta';

    public const AINDA_NAO_ABERTA = 'ainda_nao_aberta';

    public const ENCERRADA = 'encerrada';

    public function status(?CarbonImmutable $agora = null): string
    {
        $agora ??= CarbonImmutable::now(config('app.timezone'));
        $abertura = $this->dataConfigurada('abertura_em');
        $encerramento = $this->dataConfigurada('encerramento_em');

        if ($abertura?->isAfter($agora)) {
            return self::AINDA_NAO_ABERTA;
        }

        if ($encerramento?->isBefore($agora)) {
            return self::ENCERRADA;
        }

        return self::ABERTA;
    }

    public function estaAberta(?CarbonImmutable $agora = null): bool
    {
        return $this->status($agora) === self::ABERTA;
    }

    private function dataConfigurada(string $chave): ?CarbonImmutable
    {
        $valor = config("snctzo.inscricoes.{$chave}");

        if (blank($valor)) {
            return null;
        }

        return CarbonImmutable::parse($valor, config('app.timezone'));
    }
}
