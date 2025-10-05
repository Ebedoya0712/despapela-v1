<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewStaffCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    // Propiedades para almacenar los datos que se pasan a la notificación
    protected $email;
    protected $password;
    protected $roleName;
    protected $companyName;

    /**
     * Crea una nueva instancia de notificación.
     *
     * @param string $email El email del nuevo usuario (también es su usuario).
     * @param string $password La contraseña temporal generada.
     * @param string $roleName El nombre del rol asignado (Ej: "Técnico", "Trabajador").
     * @param string $companyName El nombre de la empresa a la que pertenece.
     * @return void
     */
    public function __construct(string $email, string $password, string $roleName, string $companyName)
    {
        $this->email = $email;
        $this->password = $password;
        $this->roleName = $roleName;
        $this->companyName = $companyName;
    }

    /**
     * Obtiene los canales de notificación.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Enviar esta notificación a través del canal de correo (mail).
        return ['mail'];
    }

    /**
     * Obtiene la representación de la notificación por correo.
     *
     * @param  mixed  $notifiable (El modelo User que se está notificando)
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            // Define el asunto del correo usando los datos de la empresa y el rol
            ->subject("¡Acceso a Despapela como {$this->roleName} en {$this->companyName}!")

            // -----------------------------------------------------------------
            // CAMBIO CLAVE: Usamos ->view() para cargar la plantilla HTML Blade
            // -----------------------------------------------------------------
            ->view('emails.new_staff_html', [
                // Los datos de la notificación se pasan a la vista Blade
                'name' => $notifiable->name,
                'email' => $this->email,
                'password' => $this->password,
                'roleName' => $this->roleName,
                'companyName' => $this->companyName,
            ]);
    }

    /**
     * Obtiene la representación de la notificación de matriz.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'email' => $this->email,
            'roleName' => $this->roleName,
            'companyName' => $this->companyName,
        ];
    }
}
