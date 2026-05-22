<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratExpirantEmployer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $employer) {}

    public function build()
    {
        return $this->subject('Votre contrat expire bientôt')
            ->view('emails.contrat_expirant_employer');
    }
}