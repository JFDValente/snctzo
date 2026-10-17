<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProvisionarOperadoresDePresenca extends Command
{
    protected $signature = 'presencas:provisionar-operadores';

    protected $description = 'Cria ou atualiza os quatro operadores do controle de presença.';

    /** @var list<string> */
    private const USUARIOS = [
        'SNCTZO_01',
        'SNCTZO_02',
        'SNCTZO_03',
        'SNCTZO_04',
    ];

    public function handle(): int
    {
        $senhas = [];

        foreach (self::USUARIOS as $username) {
            $senha = (string) $this->secret("Senha para {$username}");
            $confirmacao = (string) $this->secret("Confirme a senha para {$username}");

            if ($senha === '' || $senha !== $confirmacao) {
                $this->error("A senha de {$username} está vazia ou a confirmação não confere.");

                return self::FAILURE;
            }

            $senhas[$username] = $senha;
        }

        DB::transaction(function () use ($senhas): void {
            foreach ($senhas as $username => $senha) {
                User::query()->updateOrCreate(
                    ['username' => $username],
                    [
                        'name' => $username,
                        'email' => null,
                        'perfil' => User::PERFIL_OPERADOR_PRESENCA,
                        'password' => Hash::make($senha),
                    ],
                );
            }
        });

        $this->info('Operadores do controle de presença provisionados com sucesso.');

        return self::SUCCESS;
    }
}
