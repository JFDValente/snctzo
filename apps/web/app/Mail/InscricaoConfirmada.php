<?php

namespace App\Mail;

use App\Models\Atividade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscricaoConfirmada extends Mailable
{
    use Queueable;
    use SerializesModels;

    public readonly Atividade $atividade;

    public function __construct(Atividade $atividade)
    {
        $this->atividade = $atividade->loadMissing([
            'curso.instituicao',
            'professorResponsavel.instituicao',
            'alunos.curso',
            'professores.instituicao',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmação de inscrição — '.$this->atividade->nome,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inscricao-confirmada',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
