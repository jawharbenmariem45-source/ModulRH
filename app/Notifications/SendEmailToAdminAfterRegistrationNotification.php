<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailToAdminAfterRegistrationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $code;
    public $email;
    public function __construct($codeToSend, $sendToemail)
    {
       $this->code= $codeToSend;
       $this->email=$sendToemail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('Création de compte administrateur')
        ->greeting('Bonjour')
        ->line('Votre compte a été créé avec succès sur la plateforme de gestion de salaire et d\'employés.')
        ->line('Cliquez sur le bouton ci-dessous pour valider votre compte.')
        ->line('Saisissez le code '.$this->code.' et renseignez-le dans le formulaire qui apparaîtra lorsque vous cliquerez sur le bouton ci-dessous.')
        ->action('Valider mon compte', url('/validate-account/'. urlencode($this->email)))
        ->line('Merci d\'utiliser nos services !');
}


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
