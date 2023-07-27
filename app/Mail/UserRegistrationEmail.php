<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegistrationEmail extends Mailable 
{
    use Queueable, SerializesModels; 
   

    protected $user;
    protected $password;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'User Registration Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $user_data = $this->user;
        return new Content(
            view: 'email.user_registration',
            with: ['name' => $this->user['name'], 'email' => $this->user['email'], 'password' => $this->password ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}


