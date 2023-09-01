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

    protected $payment;
    /**
     * Create a new message instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Confirmation For '. $this->payment['customer_name'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $paymentData = $this->payment;

        return new Content(
            view: 'email.payment_confirmation',
            with: ['name' => $paymentData['customer_name'], 'email' => $this->payment['email'], 'amount' => $this->payment['amount'] , 
            'providerRef' => $this->payment['providerRef'], 'BUID' => $this->payment['BUID'], 'object' => $this->payment ],
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
