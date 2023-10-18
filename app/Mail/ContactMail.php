<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    // protected $name;
    // protected $email;
    // protected $subject;
    // protected $accountType;
    // protected $unique_code;
    // protected $message;

    public $name;
    public $email;
    public $subject;
    public $accountType;
    public $unique_code;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $subject, $accountType, $unique_code, $mess)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subject = $subject;
        $this->accountType = $accountType;
        $this->unique_code = $unique_code;
        $this->mess = $mess;
    }

   
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CUSTOMER PAYMENT ISSUES: - '. $this->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('Inside Mail Function', ['MailContact' =>   $this->name, $this->email, $this->subject, $this->accountType, $this->unique_code, $this->mess]);
       
        return new Content(
            view: 'email.help',
            with: ['name' => $this->name, 'email' => $this->email, 'accountType' => $this->accountType, 'uniqueCode' => $this->unique_code, 'subject' => $this->subject, 'mess' => $this->mess ],
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
