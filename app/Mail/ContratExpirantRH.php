<?php

namespace App\Mail;

use App\Models\Employer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratExpirantRH extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Employer $employer) {}

    public function build()
    {
        return $this->subject('Contrat expirant bientôt - ' . $this->employer->nom . ' ' . $this->employer->prenom)
            ->view('emails.contrat_expirant_rh');
    }
}