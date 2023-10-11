<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $customer_name;
    protected $email;
    protected $amount;
    protected $providerRef;

    /**
     * Create a new message instance.
     */
    public function __construct($customer_name, $email, $amount, $providerRef)
    {
        $this->customer_name = $customer_name;
        $this->email = $email;
        $this->amount = $amount;
        $this->providerRef = $providerRef;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
           // subject: 'Payment Confirmation For '. $this->payment['customer_name'],
            subject: 'Payment Confirmation For '. $this->customer_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.payment_confirmation',
            with: ['name' => $this->customer_name, 'email' => $this->email, 'amount' => $this->amount, 
            'providerRef' => $this->providerRef ],

           // with: ['name' => $this->user['name'], 'email' => $this->user['email'], 'password' => $this->password ],
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
