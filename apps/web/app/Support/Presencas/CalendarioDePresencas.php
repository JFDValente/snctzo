<?php

namespace App\Support\Presencas;

use Carbon\CarbonImmutable;

class CalendarioDePresencas
{
    public const MANHA = 'manha';

    public const TARDE = 'tarde';

    public const NOITE = 'noite';

    /**
     * @var array<string, string>
     */
    private const ROTULOS_DOS_TURNOS = [
        self::MANHA => 'Manhã',
        self::TARDE => 'Tarde',
        self::NOITE => 'Noite',
    ];

    /**
     * @return list<array{valor: string, rotulo: string}>
     */
    public function opcoesDeData(): array
    {
        return array_map(
            fn (string $data): array => [
                'valor' => $data,
                'rotulo' => CarbonImmutable::parse($data, config('app.timezone'))->format('d/m/Y'),
            ],
            array_keys($this->calendario()),
        );
    }

    /**
     * @return list<string>
     */
    public function turnosDaData(string $data): array
    {
        return $this->calendario()[$data] ?? [];
    }

    public function dataEstaConfigurada(string $data): bool
    {
        return array_key_exists($data, $this->calendario());
    }

    public function periodoExiste(string $data, string $turno): bool
    {
        return in_array($turno, $this->turnosDaData($data), true);
    }

    public function rotuloDoTurno(string $turno): string
    {
        return self::ROTULOS_DOS_TURNOS[$turno] ?? $turno;
    }

    /**
     * @return array<string, list<string>>
     */
    private function calendario(): array
    {
        return config('snctzo.presencas.calendario', []);
    }
}
