<?php

namespace Modules\Reunioes\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Reunioes\Models\Reuniao;

class ConselheiroNotification extends Notification
{
    public function __construct(
        public readonly string $assunto,
        public readonly string $corpo,
        public readonly ?Reuniao $reuniao = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->assunto)
            ->greeting('Olá, ' . $notifiable->nome . '!')
            ->line($this->corpo);

        if ($this->reuniao) {
            $mail
                ->line('**Reunião:** ' . ($this->reuniao->conselho->nome ?? ''))
                ->line('**Data/Hora:** ' . $this->reuniao->data_hora->format('d/m/Y \à\s H:i'))
                ->line('**Local:** ' . ($this->reuniao->local ?? 'A definir'));
        }

        return $mail->line('Este é um aviso automático do Sistema de Conselhos Municipais.');
    }
}
