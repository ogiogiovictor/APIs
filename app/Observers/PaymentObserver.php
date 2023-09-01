<?php

namespace App\Observers;

use App\Models\PaymentModel;
use App\Mail\PaymentConfirmation; // Import the email class
use Illuminate\Support\Facades\Mail;
use App\Models\ZoneECMI;
use App\Models\ZoneCustomer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{

     //We want to fire an email event that the customer have paid
    public function created(PaymentModel $payment){

        Log::info('Payment created event fired.', [$payment->email]);
        $this->handlePaymentEvent($payment);
    }

    // This method will be triggered when a PaymentModel instance is updated
    public function updated(PaymentModel $payment)
    {
        Log::info('Payment updated event fired.', ['payment' => $payment]);
        return $this->handlePaymentEvent($payment);
    }

    private function handlePaymentEvent(PaymentModel $payment)
    {

       // if($payment->status == "success" && $payment->account_type == "Postpaid"){ //&& $payment->account_type == "Postpaid"
            $customerEmail = $payment->email;
            Log::info('Payment event should fire on status success that is updated.'. $payment);
           
            Mail::to($customerEmail)->send(new PaymentConfirmation($payment));

           
            //Send SMS to the Customer Here
            $baseUrl = env('SMS_MESSAGE');
            $data = [
                'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                'sender' => "IBEDC",
                'to' => $payment->phone,
                "message" => "Bill Payment $payment->amount for the Month Successful",
                "type" => 0,
                "routing" => 3,
            ];
       // }
    }
   
    //We also want to fire and SMS to the customer
}
