<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PaymentLogJobs;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentModel;


class CheckPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-payment-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Payment Jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try{

        $this->info('***** POSTPAID API :: Lookup Started *************');

        $today = now()->toDateString();
      
        $checkTransaction = PaymentModel::whereDate('created_at', $today)
        ->where('status', 'pending')
        ->chunk(10, function ($paymentLogs) use (&$paymentData) {

            foreach ($paymentLogs as $paymentLog) {

                
        
                $flutterData = [
                    'SECKEY' => 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
                    "txref" => $paymentLog->transaction_id
                ];

        
                $flutterUrl = env('FLUTTER_WAVE_URL');
        
                $iresponse = Http::post($flutterUrl, $flutterData);
                $flutterResponse = $iresponse->json(); 

               // \Log::info("Log Flutter Response: ".  json_encode($flutterResponse) );

                if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {
                    $update = PaymentModel::where("transaction_id", $paymentLog->transaction_id)->update([
                        'providerRef' => $flutterResponse['data']['flwref'],
                    ]);

                    \Log::info("Success: ". json_encode($flutterResponse));
                }

              
        

            }

        });



        }catch(\Exception $e){
            $this->info('***** CHECKING TOKEN API :: Error Processing CHECK TOKEN PAYMENT *************');
            \Log::error("Error Processing Payment: ". $e->getMessage());
        }
    }
}


// $today = now()->toDateString(); // Get the current date in 'Y-m-d' format

// $pendingPayments = PaymentLog::whereDate('created_at', $today)
//                             ->where('status', 'pending')
//                             ->get();