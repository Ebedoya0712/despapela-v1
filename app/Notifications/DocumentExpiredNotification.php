<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(public Document $document)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Notificación: Un documento ha caducado')
                    ->greeting('Hola ' . $notifiable->name . ',')
                    ->line('Te informamos que el siguiente documento ha cumplido su fecha de caducidad y ha sido archivado en el sistema:')
                    ->line('**Documento:** ' . $this->document->original_filename)
                    ->line('**Empresa:** ' . $this->document->company->name)
                    ->line('**Fecha de caducidad:** ' . $this->document->expires_at->format('d/m/Y'))
                    ->action('Ir a Documentos', url('/')) // Puedes cambiar esta URL a donde prefieras
                    ->line('Este documento ya no estará disponible para su descarga directa.');
    }
}