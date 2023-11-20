<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ECMITransactions;
use App\Models\PaymentModel;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\PrepaidPaymentMail;
use Mail;

class RequeryToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:requery-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Requery Token already existing in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** REQUERY API :: Starting to push Pending Payments *************');
        $data = [];

        try {
            $checkTransaction = PaymentModel::whereNull('receiptno')
                 ->where('account_type', 'Prepaid')
                 ->where('status', 'pending')
                 ->whereNotNull('providerRef')
                 ->chunk(5, function ($paymentLogs) use (&$data) {
                     // Add the payment logs to the data array
                     foreach ($paymentLogs as $paymentLog) {

                    $transactionID = ECMITransactions::where('transref', 'like', '%' . $paymentLog->transaction_id)->first();
 
                     if($transactionID){                 
 
                         $update = PaymentModel::where("transaction_id", $paymentLog->transaction_id)->update([
                             'status' => 'success', 
                             'provider' =>  $transactionID->OperatorID,
                             'receiptno' =>    $transactionID->Token,  
                             'Descript' =>   $transactionID->Reasons,
                         ]);

                       
                         $this->info('***** REQUERY API :: Sending token to the customer *************');
                         
                         $baseUrl = env('SMS_MESSAGE');

                         $data[] = [
                            'transaction_id' => $paymentLog->transaction_id,
                            // Include other relevant data here
                        ];
                         
                         $idata = [
                             'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                             'sender' => "IBEDC",
                             'to' => $paymentLog->phone,
                            // "message" => "IBEDC - Your Payment Token is $transactionID->Token for this ReferenceNo $paymentLog->transaction_id",
                             "message" => "Meter Token: $transactionID->Token  Your payment of $paymentLog->amount for MeterNo $paymentLog->meter_no was successful. REF: $paymentLog->transaction_id. For Support: 07001239999",
                             "type" => 0,
                             "routing" => 3,
                         ];

                         //  "message" => "Meter Token: $token Your payment of $amount for Meter No $meterNo was successful. REF: $transactionID. For Support: 07001239999",
 
                         $iresponse = Http::asForm()->post($baseUrl, $idata);

                         $this->info('***** REQUERY API :: SMS has been sent to the customer *************');
                         $emailData = [
                            'token' => $transactionID->Token,
                            'meterno' => $paymentLog->meter_no,
                            'amount' => $paymentLog->amount,
                            "custname" => $paymentLog->customer_name,
                            "custphoneno" => $paymentLog->phone,
                            "payreference" => $paymentLog->transaction_id,
                        ];

                         Mail::to($paymentLog->email)->send(
                            new PrepaidPaymentMail($emailData));
 
                        //return $newResponse;
                      }
                        
 
                     }
                 });

                 $this->info('***** REQUERY API :: All payments processed successfully *************');
                 
                 return $data; // Return the data collected from payments
             //return $data;
         } catch (\Exception $e) {
             \Log::error($e->getMessage());

             $this->info('***** REQUERY API :: Error Processing Payment *************');

            // return $this->sendError('Error', "We are experiencing issues retrieving tokens from ibedc  " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
         }

    }

}
