<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Reunioes\Models\Reuniao;

/**
 * E-mail de convocação (criação ou atualização de reunião).
 *
 * Quando $isUpdate = true, $changes contém ['campo' => ['old' => ..., 'new' => ...]]
 * para exibição das alterações relevantes.
 */
class ConvocacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reuniao $reuniao,
        public readonly string $nomeDestinatario,
        public readonly bool $isUpdate = false,
        public readonly array $changes = [],
    ) {}

    public function envelope(): Envelope
    {
        $sigla   = $this->reuniao->conselho->sigla ?? '';
        $dataPt  = $this->reuniao->data_hora?->format('d/m/Y') ?? '';
        $horaPt  = $this->reuniao->data_hora?->format('H:i') ?? '';
        $tipo    = $this->reuniao->tipoReuniao->nome ?? 'Reunião';

        if ($this->isUpdate) {
            $assunto = "Atualização de convocação ({$sigla}) - {$dataPt}" . ($horaPt ? " {$horaPt}h" : '');
        } else {
            $assunto = "Convocação: {$tipo} — {$sigla} - {$dataPt}" . ($horaPt ? " às {$horaPt}h" : '');
        }

        $fromEmail = $this->reuniao->conselho->email ?: config('mail.from.address');
        $fromName  = $sigla ?: config('mail.from.name');

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromEmail, $fromName),
            subject: $assunto,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.convocacao');
    }

    public function attachments(): array
    {
        return [];
    }
}
