<?php

namespace App\Services;

use App\Models\PaymentModel;
use App\Models\ZoneCustomer;
use Illuminate\Support\Facades\Http;
use App\Jobs\PaymentLogJobs;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class PostpaidService extends BaseApiController
{
    public function processService($checkRef, $request)
    {
        $custInfo = ZoneCustomer::where("AccountNo", $checkRef->account_number)->first();

        //$receiptNo = Carbon::now()->format('YmdHis');
        //Update the payment first
        $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
            'provider' => $request->payment_status['provider'] ?? '',
            //'receiptno' =>  $receiptNo, 
            'Descript' => $request->payment_status['desc'],
            'response_status' => 0,    
        ]);

        $baseUrl = env('MIDDLEWARE_URL');
        $addCustomerUrl = $baseUrl . 'vendelect';

        $data = [
            'meterno' => $checkRef->account_number,
            'vendtype' => $checkRef->account_type,
            'amount' => $request->payment_status['apprAmt'], 
            "provider" => "IBEDC",
            "custname" => $checkRef->customer_name,
            "businesshub" => $custInfo->BUID,
            "custphoneno" => $checkRef->phone,
            "payreference" => $checkRef->transaction_id,
            "colagentid" => "IB001",
                             
        ];

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer LIVEKEY_711E5A0C138903BBCE202DF5671D3C18',
        ])->post($addCustomerUrl, $data);

        $newResponse =  $response->json();

        if($newResponse['status'] == "true"){ 
            //Update the status of payment and send the job and send SMS
            $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                'response_status' => 1,
                'status' =>  'success', //"resp": "00",
                'receiptno' =>  Carbon::now()->format('YmdHis'),
            ]);
            dispatch(new PaymentLogJobs($checkRef));
        }

        return $this->sendSuccess($checkRef, "Payment Successfully Completed", Response::HTTP_OK);

    }
}
