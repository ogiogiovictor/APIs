<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentModel;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\PrepaidPaymentMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Mail;

class TokenLookup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:token-lookup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lookup Token and ensure the token is valid and sent to the customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('***** TOKENLOOKUP API :: Starting to push Pending Payments *************');
        $paymentData = []; 

        DB::connection()->enableQueryLog();

        try {
            $checkTransaction = PaymentModel::whereNull('receiptno')
                 ->where('account_type', 'Prepaid')
                 ->where('status', 'pending')
                 ->whereNotNull('providerRef')
                 ->chunk(7, function ($paymentLogs) use (&$paymentData) {
                     // Add the payment logs to the data array
                     foreach ($paymentLogs as $paymentLog) {
                        
                         $baseUrl = env('MIDDLEWARE_URL');
                         $addCustomerUrl = $baseUrl . 'vendelect';
         
                         $data = [
                                 'meterno' => $paymentLog->meter_no,
                                 'vendtype' => $paymentLog->account_type,
                                 'amount' => $paymentLog->amount, 
                                 "provider" => "IBEDC",
                                 "custname" => $paymentLog->customer_name,
                                 "businesshub" => $paymentLog->BUID,
                                 "custphoneno" => $paymentLog->phone,
                                 "payreference" => $paymentLog->transaction_id,
                                 "colagentid" => "IB001",
         
                             ];
                     
                             // $response = Http::withHeaders([
                             //     'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                             // ])->post($addCustomerUrl , $data);
 
                             $response = Http::withoutVerifying()->withHeaders([
                                 'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                             ])->post($addCustomerUrl, $data);
                     
                             $newResponse =  $response->json();

                               // Log API request and response for debugging
                        \Log::info('TOKENLOOKUP API Request: ' . json_encode($data));
                        \Log::info('TOKENLOOKUP API Response: ' . json_encode($newResponse));

                        $totalRecords = count($paymentLogs);
                        \Log::info("TOKENLOOKUP API Total Records to Update: " . $totalRecords);

 
                     if($newResponse['status'] == "true"){      
                        
                        $paymentData[] = $data;
                         // Log added data for debugging
                        \Log::info('Added Data: ' . json_encode($data));
 
                         $update = PaymentModel::where("transaction_id", $paymentLog->transaction_id)->update([
                             'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                             'provider' => isset($newResponse['transactionReference'])  ? $newResponse['transactionReference'] : $newResponse['data']['transactionReference'],
                         // 'providerRef' => $newResponse['transactionReference'],
                             'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                             //'BUID' => $paymentLog->BUID,
                             'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] : $newResponse['transaction_status'],
                         ]);

                         //\Log::info("Updated Records: " . $updatedRecords);
 
                         //Send SMS to User
                         $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];

                         $this->info('***** TOKENLOOKUP API :: Sending token to the customer *************');
                         
                         $baseUrl = env('SMS_MESSAGE');

                         $data[] = [
                            'transaction_id' => $paymentLog->transaction_id,
                            // Include other relevant data here
                        ];
                         
                         $idata = [
                             'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                             'sender' => "IBEDC",
                             'to' => $paymentLog->phone,
                             "message" => "Meter Token: $token  Your IBEDC Prepaid payment of $paymentLog->amount for Meter No $paymentLog->meter_no  was successful. REF: $paymentLog->transaction_id. For Support: 07001239999",
                             "type" => 0,
                             "routing" => 3,
                         ];
 
                         $iresponse = Http::asForm()->post($baseUrl, $idata);

                         $this->info('***** TOKENLOOKUP API :: SMS has been sent to the customer *************');

                         $emailData = [
                            'token' => $token,
                            'meterno' => $paymentLog->meter_no,
                            'amount' => $paymentLog->amount,
                            "custname" => $paymentLog->customer_name,
                            "custphoneno" => $paymentLog->phone,
                            "payreference" => $paymentLog->transaction_id,
                        ];

                         Mail::to($paymentLog->email)->send(new PrepaidPaymentMail($emailData));
 
                        return $newResponse;
                      }
                        
 
                     }
                 });
                 \Log::info(DB::getQueryLog());
                 $this->info('***** TOKENLOOKUP API :: All payments processed successfully *************');
                 
                 return $paymentData; //Return the data collected from payments
         } catch (\Exception $e) {
            

             $this->info('***** TOKENLOOKUP API :: Error Processing Payment *************');
             \Log::error($e->getMessage());
            // return response()->json('Error', Response::HTTP_INTERNAL_SERVER_ERROR);
             //return $this->sendError('Error', "We are experiencing issues retrieving tokens from ibedc" . $e->getMessage(), Response::HTTP_BAD_REQUEST);
         }

      
    }
}
