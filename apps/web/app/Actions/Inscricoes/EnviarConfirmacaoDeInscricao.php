<?php

namespace App\Actions\Inscricoes;

use App\Mail\InscricaoConfirmada;
use App\Models\Atividade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarConfirmacaoDeInscricao
{
    public function __invoke(Atividade $atividade): void
    {
        if (
            ! config('snctzo.email.confirmacao_ativa')
            || $atividade->email_confirmacao_enviado_em !== null
        ) {
            return;
        }

        try {
            Mail::to($atividade->professorResponsavel->email)->send(
                new InscricaoConfirmada($atividade),
            );

            $atividade->forceFill([
                'email_confirmacao_enviado_em' => now(),
            ])->save();
        } catch (Throwable $exception) {
            Log::warning('Não foi possível enviar a confirmação da inscrição.', [
                'atividade_id' => $atividade->id,
                'erro' => $exception::class,
            ]);
        }
    }
}
