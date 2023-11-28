<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProcessCAADMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $caad;
    protected $name;
    /**
     * Create a new message instance.
     */
    public function __construct($caad, $name)
    {
        $this->caad = $caad;
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CAAD Request Awaiting Approval For '. $this->caad->accountNo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        return new Content(
            view: 'email.caad_processing',
            with: [ 
                'accountNo' => $this->caad->accountNo, 
                'phone' => $this->caad->phone, 
                'surname' => $this->caad->surname, 
                'lastname' => $this->caad->lastname, 
                'accountType' => $this->caad->accountType, 
                'amount' => $this->caad->amount, 
                'region' => $this->caad->region,
                'effective_date' => $this->caad->effective_date, 
                'name' => $this->name,
                'id' => $this->id,
            ],
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
