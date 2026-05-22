<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratExpirantRH extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $employer) {}

    public function build()
    {
        return $this->subject('Contrat expirant bientôt - ' . $this->employer->first_name . ' ' . $this->employer->last_name)
            ->view('emails.contrat_expirant_rh');
    }
}