<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProcessCRMDMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $crmd;
    /**
     * Create a new message instance.
     */
    public function __construct($crmd)
    {
        $this->crmd = $crmd;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CRMD Request Awaiting Approval For '. $this->crmd->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.crmd',
            with: [ 
                'name' => $this->crmd->name, 
                'id' => $this->crmd->id, 
                'email' => $this->crmd->email, 
                'accountNo' => $this->crmd->accountNo, 
                'MeterNo' => $this->crmd->MeterNo
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
