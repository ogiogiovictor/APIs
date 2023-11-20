<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentModel;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\PaymentLogJobs;
use Carbon\Carbon;

class PospaidLookup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:postpaid-lookup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Postpaid Lookup to Middleware';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try{

            $this->info('***** POSTPAID API :: Lookup Started *************');
            $checkTransaction = PaymentModel::whereNull('receiptno')
            ->where('account_type', 'Postpaid')
            ->where('response_status', 0)
            ->where('status', 'pending')
            ->whereNotNull('providerRef')
            ->chunk(5, function ($paymentLogs) use (&$paymentData) {

                foreach ($paymentLogs as $paymentLog) {

                    $baseUrl = env('MIDDLEWARE_URL');
                    $addCustomerUrl = $baseUrl . 'vendelect';
            
                    $data = [
                        'meterno' => $paymentLog->account_number,
                        'vendtype' => $paymentLog->account_type,
                        'amount' => $paymentLog->amount, 
                        "provider" => "IBEDC",
                        "custname" => $paymentLog->customer_name,
                        "businesshub" => $paymentLog->BUID,
                        "custphoneno" => $paymentLog->phone,
                        "payreference" => $paymentLog->transaction_id,
                        "colagentid" => "IB001",
                                         
                    ];
            
                    $response = Http::withoutVerifying()->withHeaders([
                        'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
                    ])->post($addCustomerUrl, $data);
            
                    $newResponse =  $response->json();

                    $this->info('***** POSTPAID API :: Processing Postpaid Payment*************');
                    \Log::info('Postpaid Data Log: ' . json_encode($newResponse));


                    if($newResponse['status'] == "true"){ 
                        //Update the status of payment and send the job and send SMS
                        $update = PaymentModel::where("transaction_id", $paymentLog->transaction_id)->update([
                            'response_status' => 1,
                            'status' =>  'success', //"resp": "00",
                            'provider' => 'Flutterwave',
                            'receiptno' =>  Carbon::now()->format('YmdHis'),
                        ]);
                        dispatch(new PaymentLogJobs($paymentLog));
                        \Log::info('Postpaid Payment Successfuly: ' . $newResponse);
                    }

                }

            });

        }catch(\Exception $e){
            $this->info('***** POSTPAID API :: Error Processing Postpaid Payment *************');
            \Log::error("Error Processing Payment: ". $e->getMessage());
        }
    }
}
