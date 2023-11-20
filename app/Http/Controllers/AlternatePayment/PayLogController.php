<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentModel;
use App\Jobs\PrepaidPaymentJob;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use App\Services\PostpaidService;
use App\Services\PrepaidService;

class PayLogController extends BaseApiController
{
    public function processPayment(Request $request){

        if (!$request->payment_status) {
            return $this->sendError('Error', "Error Initiating Payment", Response::HTTP_BAD_REQUEST);
        }

        $checkRef =  PaymentModel::where("transaction_id", $request->payment_status['txnref'])->first();

        if(!$request->payment_status['MeterNo'] || !$request->payment_status['account_type'] || !$request->payment_status['phone'] || !$request->payment_status['amount']){
            return $this->sendError('Error', "Please send all required information", Response::HTTP_BAD_REQUEST);
        }


        if($request->payment_status['payRef'] == 'undefined' || $request->payment_status['payRef'] == 'NULL' ){
            return $this->sendError('Error', "Kindly Complete Your Payment", Response::HTTP_BAD_REQUEST);
        }

        $flutterData = [
            'SECKEY' => 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
            "txref" => $checkRef->transaction_id
        ];

        $flutterUrl = env('FLUTTER_WAVE_URL');

        $iresponse = Http::post($flutterUrl, $flutterData);
        $flutterResponse = $iresponse->json(); 


        if (!isset($flutterResponse['status']) || ($flutterResponse['status'] != "success" && (!isset($flutterResponse['data']['status']) || $flutterResponse['data']['status'] != 'successful'))) {
            return $this->sendError('Invalid Payment', "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
        }


        if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {
            $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                'providerRef' => $flutterResponse['data']['flwref'],
            ]);
        }

        if($checkRef && $request->payment_status['payRef'] && $request->payment_status['resp']){

            // setService)->getFeederEleven(); 
            switch ($checkRef->account_type) {
                case 'Postpaid':
                    return  (new PostpaidService)->processService($checkRef, $request);
                case 'Prepaid':
                    return  (new PrepaidService)->processService($checkRef, $request);
                default:
                   // throw new \InvalidArgumentException('Invalid payment type');  
                   return $this->sendError('Error', "Invalid Payment Type", Response::HTTP_BAD_REQUEST);
                
            }

        }

    }


    public function getpaymentInfo(Request $request){

        if(!$request->MeterNo || !$request->type){
            return $this->sendError('Error', "Please Send MeterNo", Response::HTTP_BAD_REQUEST);
        }

        if($request->type == 'Prepaid'){
            $checkRef =  PaymentModel::where("meter_no", $request->MeterNo)->orderby("created_at", "desc")->paginate();

            return $this->sendSuccess($checkRef, "Payments Loaded", Response::HTTP_OK);
        }

        return $this->sendError('Error', "Postpaid is not set Yet", Response::HTTP_BAD_REQUEST);
    }


    public function updatePaymentInfo(Request $request){

       
        if(!$request->transaction_id){
            return $this->sendError('Error', "Please Send a transactionID", Response::HTTP_BAD_REQUEST);
        }

         $checkRef =  PaymentModel::where("transaction_id", $request->transaction_id)->first();

        if(!$checkRef->providerRef ||  $checkRef->providerRef  == 'NULL' || $checkRef->providerRef == NULL) {

            $flutterData = [
                'SECKEY' => 'FLWSECK-d1c7523a58aad65d4585d47df227ee25-X',
                "txref" => $request->transaction_id
            ];
    
            $flutterUrl = env('FLUTTER_WAVE_URL');
    
            $iresponse = Http::post($flutterUrl, $flutterData);
            $flutterResponse = $iresponse->json(); 
    
    
            if (!isset($flutterResponse['status']) || ($flutterResponse['status'] != "success" && (!isset($flutterResponse['data']['status']) || $flutterResponse['data']['status'] != 'successful'))) {
                return $this->sendError('Invalid Payment', "Error Verifying Payment", Response::HTTP_BAD_REQUEST);
            }
    
    
            if ($flutterResponse['status'] == "success" && $flutterResponse['data']['status'] == 'successful') {
                $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                    'providerRef' => $flutterResponse['data']['flwref'],
                ]);

                return $this->sendSuccess($checkRef, "Payments Successfully Updated", Response::HTTP_OK);
            }
    
            return $this->sendSuccess($flutterResponse, "Invalid Payment", Response::HTTP_OK);
        }else {
            return $this->sendError('Invalid Reference', "Error Validating Payment", Response::HTTP_BAD_REQUEST);
        }

        return $this->sendSuccess($checkRef, "Payment Already Have Provider Reference", Response::HTTP_OK);
       

    }


}
