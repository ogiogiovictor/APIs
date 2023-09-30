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

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
        ])->post($addCustomerUrl, $data);

        $newResponse =  $response->json();

        if ($newResponse === null) {
            //log the error
            Log::info('The Response coming from middleware is null', ['MiddlewareError' =>   $response ]);
            //Send a message as notification that token was not recieved with the meter number and date of payment
            // $to = [
            //     "fortune.odesanya@ibedc.com",
            //     "somto.anowai@ibedc.com",
            //     "victor.ogiogio@ibedc.com",
            //     "frank.obasogie@ibedc.com"
            // ];
            // $subject = "Failed No Response from Middleware". $this->payment['meterNo']. ' '. $this->payment['amount'];
            // $body = "The Response from the middleware is null for this meterno" . $newResponse;

            // Mail::raw($body, function ($body) use ($to, $subject) {
            //     $body->to($to)->subject($subject);
            // });

            $data = [
                'meterNo' => $this->payment['meterNo'],
                'amount' => $this->payment['amount'],
                'response' => $newResponse,
                "custname" => $this->payment['customerName'],
                "custphoneno" => $this->payment['phone'],
                "payreference" => $this->payment['transaction_id'], 
            ];

            Mail::send('emails.middleware_error', $data, function ($message) {
                $message->to('victor.ogiogio@ibedc.com', 'Victor Ogiogio')
                        ->cc('fortune.odesanya@ibedc.com', 'Fortune Odesanya')
                        ->bcc('somto.anowai@ibedc.com', 'Somto Anowai')
                        ->subject('Token Error - Not Received');
            });

           


        } else {

            if (isset($newResponse['status']) && $newResponse['status'] == "true") {

                $update = PaymentModel::where("transaction_id", $this->payment['transaction_id'])->update([
                    'status' => $newResponse['status'] == "true" ?  'success' : 'failed', //"resp": "00",
                    'provider' => isset($newResponse['transactionReference'])  ? $newResponse['transactionReference'] : $newResponse['data']['transactionReference'],
                // 'providerRef' => $newResponse['transactionReference'],
                    'receiptno' =>   isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'],  //Carbon::now()->format('YmdHis').time()
                    'BUID' => $buid,
                    'Descript' =>  isset($newResponse['message']) ? $newResponse['message'] : $newResponse['transaction_status'],
                ]);

                 //Send SMS to User
                 $token =  isset($newResponse['recieptNumber']) ? $newResponse['recieptNumber'] : $newResponse['data']['recieptNumber'];

                 $baseUrl = env('SMS_MESSAGE');

                $smsdata = [
                    'token' => "p42OVwe8CF2Sg6VfhXAi8aBblMnADKkuOPe65M41v7jMzrEynGQoVLoZdmGqBQIGFPbH10cvthTGu0LK1duSem45OtA076fLGRqX",
                    'sender' => "IBEDC",
                    'to' => $phone,
                    "message" => "IBEDC - Your Payment Token is $token",
                    "type" => 0,
                    "routing" => 3,
                ];

                $emailData = [
                    'token' => $token,
                    'meterno' => $this->payment['meterNo'],
                    'amount' => $this->payment['amount'], 
                    "custname" => $this->payment['customerName'],
                    "custphoneno" => $this->payment['phone'],
                    "payreference" => $this->payment['transaction_id'],    
                ];

                $iresponse = Http::asForm()->post($baseUrl, $smsdata);
                //Send a Successfully Mail to user
                Mail::to($this->payment->email)->cc(["fortune.odesanya@ibedc.com, somto.anowai@ibedc.com,victor.ogiogio@ibedc.com, frank.obasogie@ibedc.com"])->send(
                    new PrepaidPaymentMail($emailData));
              //  return response()->json(['data' => $newResponse], Response::HTTP_BAD_REQUEST);


            }else {
                Log::info('The Response coming from middleware is null', ['MiddlewareError' =>   $response ]);
                // $to = [
                //     "fortune.odesanya@ibedc.com",
                //     "somto.anowai@ibedc.com",
                //     "victor.ogiogio@ibedc.com",
                //     "frank.obasogie@ibedc.com"
                // ];
                // $subject = "Middleware Response ". $this->payment['meterNo']. ' '. $this->payment['amount'];
                
                // //$body = isset($response) ? $response : "The Response comfing from the middleware is null". $response;
                // $body = "We could not get response from the middleware for this request:-" . $response;
                // Mail::raw($body, function ($body) use ($to, $subject) {
                //     $body->to($to)->subject($subject);
                // });

                $data = [
                    'meterNo' => $this->payment['meterNo'],
                    'amount' => $this->payment['amount'],
                    'response' => $newResponse,
                    "custname" => $this->payment['customerName'],
                    "custphoneno" => $this->payment['phone'],
                    "payreference" => $this->payment['transaction_id'], 
                ];

                Mail::send('emails.middleware_error', $data, function ($message) {
                    $message->to('victor.ogiogio@ibedc.com', 'Victor Ogiogio')
                            ->cc('fortune.odesanya@ibedc.com', 'Fortune Odesanya')
                            ->bcc('somto.anowai@ibedc.com', 'Somto Anowai')
                            ->subject('Token Error - Not Received');
                });
            
                //Send a mail of the logged response as token was not recieved and response from the middleware with the error message
            }
        }


    }
}
