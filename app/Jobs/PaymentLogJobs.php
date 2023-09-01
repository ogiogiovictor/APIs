<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail; // Import the Mail facade
use App\Mail\PaymentConfirmation; 

class PaymentLogJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;

    /**
     * Create a new job instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       

        //Send SMS
        //Send SMS to the Customer Here
        $amount = $this->payment->amount;
        $phone = $this->payment->phone;

        $baseUrl = env('SMS_MESSAGE');
        $data = [
            'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
            'sender' => "IBEDC",
            'to' => $phone,
            "message" => "Bill Payment $amount for the Month Successful",
            "type" => 0,
            "routing" => 3,
        ];

        $response = Http::asForm()->post($baseUrl, $data);

        $newResponse =  $response->json();

        Mail::to($this->payment->email)->send(new PaymentConfirmation($this->payment));
    }
}
