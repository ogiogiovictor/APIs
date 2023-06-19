<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentRequest;
use App\Models\TestModel;
use App\Models\PaymentModel;
use App\Helpers\StringHelper;
use App\Models\ZoneECMI;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;


class PaymentProcessingController extends BaseApiController
{
    public function makePayment(PaymentRequest $request){

        $transactionID = StringHelper::generateTransactionReference();


        if($request->account_type == "Prepaid"){
            $zoneECMI = ZoneECMI::where("MeterNo", $request->MeterNo)->orWhere("AccountNo", $request->account_number)->first();
            // password, transaction license, keyfile - 3 things that expires every year for token generation.


         $payment =  PaymentModel::create([
                'email' => $request->email ?? '',
                'transaction_id' => $transactionID,
                'phone' => $request->phone ?? '',
                'amount' => $request->amount,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
               // 'meter_no' => $request->meter_no,
                'status' => "pending",
                'customer_name' => $zoneECMI->Surname.' '. $zoneECMI->OtherNames,
                'date_entered' => Carbon::now()

            ]);

            // Add auth_token and keyfile to the response array
            $response = [
                'payment' => $payment,
                'payment_key_authData' => "G3cf/VTtAHCdHZNxc5GXWRI8z5P0goL2amXWDVFgb6D3XK/QMtZW90TYdl5zffDCNpiZThJzk0+eEU/Y/aYS6fyIOpQZGFrOr8hmvx5869sl2kr5u8qjnM7q5b4ZnTqdKDLtNxr3Qr7anj6YLpox1FOsiyT26mktXL+7SFOaZ15NMtne1z4xrj4R2SndowI/Znsapo7Gfzvp+L7XJyQ8kLYYRk3INjvmRPPQoJg1R0Nnh6EQE3ldIdwylB7GKtr6a71N/yCd4ZtyIcqq1ZNzdWcZyy5eEBAlDIxuECdBqH6hRq2/RbkfARqidNN4Kq0WviSRaRYGbiNjl2W9pNcM8g==",
                'currency' => "NGN",
            ];

            if($payment){
                return $this->sendSuccess($response, "Payment Process Inititated", Response::HTTP_OK);
            }else{
                return $this->sendError('Error', "Error Initating Payment", Response::HTTP_BAD_REQUEST);
            }

        }

    }
}


