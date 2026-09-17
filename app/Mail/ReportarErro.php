<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportarErro extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $municipio,
        public readonly string $pagina,
        public readonly string $descricao,
        public readonly ?string $nomeRemetente = null,
        public readonly ?string $emailRemetente = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Portal {$this->municipio}] Relato de erro no portal",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.reportar-erro',
        );
    }
}
