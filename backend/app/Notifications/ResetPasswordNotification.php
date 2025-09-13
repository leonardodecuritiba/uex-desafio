<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificação de redefinição de senha.
 * Formata o link para o frontend com token e e-mail como query params.
 */
class ResetPasswordNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontend = rtrim((string) env('FRONTEND_URL'), '/');
        $email = urlencode((string) $notifiable->email);
        $url = sprintf('%s/reset-password?token=%s&email=%s', $frontend, $this->token, $email);

        return (new MailMessage)
            ->subject('Redefinição de senha')
            ->greeting('Olá!')
            ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
            ->action('Redefinir senha', $url)
            ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.');
    }
}
