<?php

namespace App\Services;

use App\Models\PaymentModel;
use App\Models\ZoneECMI;
use Illuminate\Support\Facades\Http;
use App\Jobs\PaymentLogJobs;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Jobs\PrepaidPaymentJob;

class PrepaidService extends BaseApiController
{
    public function processService($checkRef, $request)
    {
        $zoneECMI = ZoneECMI::where("MeterNo", $request->payment_status['MeterNo'])->first();

        if(!$zoneECMI){
            return $this->sendError('Error', "Meter Information Not Found", Response::HTTP_BAD_REQUEST);
        }

        $amount = $request->amount; 
        $customerName = $zoneECMI->Surname.' '. $zoneECMI->OtherNames;
        $phone = $request->payment_status['phone']  ?? $zoneECMI->Mobile;


        $checkExist = PaymentModel::where("transaction_id", $request->payment_status['txnref'])->value("receiptno");
        if($checkExist){
            return $this->sendSuccess($checkExist, "PaymentSource Successfully Loaded", Response::HTTP_OK);
        }else {

            $payment = [
                'meterNo' => $request->payment_status['MeterNo'],
                'account_type' => $request->payment_status['account_type'],
                'amount' => $request->payment_status['amount'],
                'disco_name' => "IBEDC",
                'customerName' => $customerName,
                'BUID' => $zoneECMI->BUID,
                'phone' => $phone,
                'transaction_id' => $checkRef->transaction_id,
                'email' => $checkRef->email,
                'id' => $checkRef->id,
            ];

            //Dispatch a job and send token to customer
            $update = PaymentModel::where("transaction_id", $checkRef->transaction_id)->update([
                'response_status' => 1,
            ]);

            dispatch(new PrepaidPaymentJob($payment))->delay(3);

            return $this->sendSuccess($payment, "Payment Successfully Token will be sent to your email", Response::HTTP_OK);

        }
    }
}
