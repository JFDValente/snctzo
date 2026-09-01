<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CriarUsuarioAdministrativo extends Command
{
    protected $signature = 'admin:criar-usuario {--nome=} {--email=}';

    protected $description = 'Cria um usuário com acesso ao gerenciamento administrativo.';

    public function handle(): int
    {
        $nome = trim((string) ($this->option('nome') ?: $this->ask('Nome')));
        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('E-mail'))));
        $senha = (string) $this->secret('Senha');
        $confirmacao = (string) $this->secret('Confirme a senha');

        $validador = Validator::make([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
        ], [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:filter', 'max:254', 'unique:users,email'],
            'senha' => ['required', 'string', 'min:6'],
        ]);

        if ($senha !== $confirmacao) {
            $validador->errors()->add('senha', 'A confirmação da senha não confere.');
        }

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $erro) {
                $this->error($erro);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $nome,
            'email' => $email,
            'password' => Hash::make($senha),
        ]);

        $this->info("Usuário administrativo criado para {$email}.");

        return self::SUCCESS;
    }
}
