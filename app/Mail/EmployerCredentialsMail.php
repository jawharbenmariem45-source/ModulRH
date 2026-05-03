<?php

namespace App\Mail;

use App\Models\Employer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployerCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Employer $employer;
    public string $motDePasse;

    public function __construct(Employer $employer, string $motDePasse)
    {
        $this->employer   = $employer;
        $this->motDePasse = $motDePasse;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants - Espace Employé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.employer_credentials',
        );
    }
}