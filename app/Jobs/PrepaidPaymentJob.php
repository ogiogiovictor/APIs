<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\PaymentLogJobs;
use Symfony\Component\HttpFoundation\Response;
use Mail;
use App\Mail\PrepaidPaymentMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException; 
use App\Models\PaymentModel;

class PrepaidPaymentJob implements ShouldQueue
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

        $baseUrl = env('MIDDLEWARE_URL');
        $addCustomerUrl = $baseUrl . 'vendelect';

        $data = [
            'meterno' => $this->payment['meterNo'],
            'vendtype' => $this->payment['account_type'],
            'amount' => $this->payment['amount'], 
            "provider" => $this->payment['disco_name'],
            "custname" => $this->payment['customerName'],
            "businesshub" => $this->payment['BUID'],
            "custphoneno" => $this->payment['phone'],
            "payreference" => $this->payment['transaction_id'],     // StringHelper::generateTransactionReference(),
            "colagentid" => "IB001",
            "email" => $this->payment['email'],

        ];


        $checkifTokenExist = PaymentModel::where("transaction_id", $this->payment['transaction_id'])->first();



        if($checkifTokenExist->status == 'pending' && $checkifTokenExist->providerRef != "" && $checkifTokenExist->receiptno == 'NULL' ){


            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
            ])->post($addCustomerUrl, $data);

            $newResponse =  $response->json();

            if ($newResponse === null) {
                //log the error
                Log::info('NULL RESPONSE: - ', ['MiddlewareError Null Response' =>    $newResponse ]);
                
                $data = [
                    'meterNo' => $this->payment['meterNo'],
                    'amount' => $this->payment['amount'],
                    'response' => $newResponse,
                    "custname" => $this->payment['customerName'],
                    "custphoneno" => $this->payment['phone'],
                    "payreference" => $this->payment['transaction_id'], 
                ];

                Mail::send('email.middleware_error', $data, function ($message) {
                    $message->to('victor.ogiogio@ibedc.com', 'Victor Ogiogio')
                            ->subject('Token Error - Not Received');
                });

            

            } else {

                if (isset($newResponse['status']) && $newResponse['status'] == "true") {

                    $update = PaymentModel::where("transaction_id", $this->payment['transaction_id'])->update([
                        'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                        'provider' => isset($newResponse['transactionReference'])  ? $newResponse['transactionReference'] : $newResponse['data']['transactionReference'],
                    // 'providerRef' => $newResponse['transactionReference'],
                        'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                    // 'BUID' =>  $this->payment['BUID'],
                        'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] : $newResponse['transaction_status'],
                    ]);

                    //Send SMS to User
                    $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];

                    $baseUrl = env('SMS_MESSAGE');
                    $amount = $this->payment['amount'];
                    $transactionID = $this->payment['transaction_id'];
                    $meterNo = $this->payment['meterNo'];

                    $smsdata = [
                        'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                        'sender' => "IBEDC",
                        'to' => $this->payment['phone'],
                        "message" => "Meter Token: $token Your payment of $amount for Meter No $meterNo was successful. REF: $transactionID. For Support: 07001239999",
                        "type" => 0,
                        "routing" => 3,
                    ];

                    Log::info('NULL RESPONSE: - ', ['SMS Response' =>    $smsdata ]);

                    //Meter Token: $token  Your IBEDC Prepaid payment of $paymentLog->amount was successful. REF: $paymentLog->transaction_id. For Support call 07001239999"

                    $emailData = [
                        'token' => $token,
                        'meterno' => $this->payment['meterNo'],
                        'amount' => $this->payment['amount'], 
                        "custname" => $this->payment['customerName'],
                        "custphoneno" => $this->payment['phone'],
                        "payreference" => $this->payment['transaction_id'],    
                    ];

                    Log::info('TOKEN SENT: : - ', ['Generated Successfully' =>     $smsdata ]);

                    $iresponse = Http::asForm()->post($baseUrl, $smsdata);
                    //Send a Successfully Mail to user
                    Mail::to($this->payment['email'])->send(new PrepaidPaymentMail($emailData));
                //  return response()->json(['data' => $newResponse], Response::HTTP_BAD_REQUEST);

                }else {
                
                    $data = [
                        'meterNo' => $this->payment['meterNo'],
                        'amount' => $this->payment['amount'],
                        'response' => $newResponse,
                        "custname" => $this->payment['customerName'],
                        "custphoneno" => $this->payment['phone'],
                        "payreference" => $this->payment['transaction_id'], 
                    ];

                    Log::info('ERROR ACCESS TOKEN', ['TOKEN ERROR' =>   $newResponse ]);

                    Mail::send('email.middleware_error', $data, function ($message) {
                        $message->to('victor.ogiogio@ibedc.com', 'Victor Ogiogio')
                                ->subject('TOKEN ERROR - '. $this->payment['transaction_id']);
                    });
                
                    //Send a mail of the logged response as token was not recieved and response from the middleware with the error message
                }
            }


        } else {


            $baseUrl = env('SMS_MESSAGE');
            $amount = $this->payment['amount'];
            $transactionID = $this->payment['transaction_id'];
            $meterNo = $this->payment['meterNo'];

            $smsdata = [
                'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                'sender' => "IBEDC",
                'to' => $this->payment['phone'],
                "message" => "Meter Token: $checkifTokenExist->receiptno Your payment of $amount for Meter No $meterNo was successful. REF: $transactionID. For Support: 07001239999",
                "type" => 0,
                "routing" => 3,
            ];

            Log::info('NULL RESPONSE: - ', ['SMS Response' =>    $smsdata ]);


            $emailData = [
                'token' => $checkifTokenExist->receiptno,
                'meterno' => $this->payment['meterNo'],
                'amount' => $this->payment['amount'], 
                "custname" => $this->payment['customerName'],
                "custphoneno" => $this->payment['phone'],
                "payreference" => $this->payment['transaction_id'],    
            ];

            Mail::to($this->payment['email'])->send(new PrepaidPaymentMail($emailData));


        }



           

           

          

        
       


    }
}
