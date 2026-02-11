<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneratedPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $password;
    protected string $nombreUsuario;

    public function __construct(string $password, string $nombreUsuario = '')
    {
        $this->password = $password;
        $this->nombreUsuario = $nombreUsuario;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('BigSei - Tu cuenta ha sido creada')
            ->greeting('¡Hola ' . ($this->nombreUsuario ?: 'Usuario') . '!')
            ->line('Se ha creado tu cuenta en BigSei.')
            ->line('Tu contraseña temporal es:')
            ->line('**' . $this->password . '**')
            ->line('Por seguridad, te recomendamos cambiar tu contraseña después de iniciar sesión.')
            ->action('Iniciar Sesión', config('app.url', 'https://bigsei.com'))
            ->line('Si no solicitaste esta cuenta, puedes ignorar este mensaje.')
            ->salutation('Saludos, el equipo de BigSei');
    }
}
