<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentRequest;
use App\Models\TestModel;
use App\Models\PaymentModel;
use App\Helpers\StringHelper;
use App\Models\ZoneECMI;
use App\Models\ZoneCustomer;
use App\Models\ZoneBills;
use App\Models\PaymentSource;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class PaymentProcessingController extends BaseApiController
{
    public function makePayment(PaymentRequest $request){
       

        DB::beginTransaction();

        try {

            if($request->account_type == "Prepaid"){
                $payment = $this->createPayment($request);
            } elseif ($request->account_type == "Postpaid") {
                $payment = $this->createPayment($request);
            } else {
                return $this->sendError('Error', "Invalid Account Type", Response::HTTP_BAD_REQUEST);
            }


            if ($payment) {
                DB::commit();
                return $this->sendSuccess($payment, "Payment Process Initiated", Response::HTTP_OK);
            } else {
                DB::rollBack();
                return $this->sendError('Error', "Error Initiating Payment", Response::HTTP_BAD_REQUEST);
            }
    


        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Error', "Error Initiating Payment: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        
        }

    }



    public function processPayment(Request $request){

        if(!$request->payment_status){
            return $this->sendError('Error', "Error Initiating Payment", Response::HTTP_BAD_REQUEST);
        }

        $checkRef =  PaymentModel::where("transaction_id", $request->txnref)->first();
        $custInfo = ZoneCustomer::where("AccountNo", $checkRef->account_number)->first();
        $mainBills =  ZoneBills::where('AccountNo', $checkRef->account_number)->latest('created_at')->first();

       if($checkRef && $request['payRef'] && $request['resp'] = '00'){

            if($checkRef == 'Postpaid'){
                $update = PaymentModel::where("transaction_id", $request->txnref)->update([
                    'status' => 'success',
                    'provider' => 'interswitch',
                    'providerRef' => $request['payRef'],
                    'receiptno' =>  Carbon::now()->format('YmdHis').time()
                ]);

                //Update the Billing Database
                $addPayment = TestModel::create([
                    "PaymentID" =>  $checkRef->transaction_id,
                    "BillID" => $mainBills->BillID,
                    "PaymentTransactionId" =>  $checkRef->providerRef,
                    "receiptnumber" => $checkRef->receiptnumber,
                    "PaymentSource" => $checkRef->payment_source,
                    "MeterNo" => $custInfo->MeterNo,
                    "AccountNo" => $custInfo->AccountNo,
                    "PayDate" => Carbon::now()->format('Y-m-d H:i:s'),
                    "PayMonth" => Carbon::now()->format('m'),
                    "PayYear" => Carbon::now()->format('Y'),
                    "OperatorID" => 1, // to be determine, to meet with joseph
                    "TotalDue" => Carbon::now()->format('m'),
                    "Payments" => $request->apprAmt,
                    "Balance" => '',
                    "Processed" => 0,
                    "ProcessedDate" => 'NULL',
                    "BusinessUnit" => $custInfo->BUID,
                    "DateEngtered" => Carbon::now()->format('m'),
                    "CustomerID" => $custInfo->CustomerID,
                ]);

                //I still have one table to add here

                return $this->sendSuccess($addPayment, "Payment Successfully Completed", Response::HTTP_OK);
            }
        

      }

       
    }



    public function paymentSource() {

      $paySource = PaymentSource::all();
      return $this->sendSuccess($paySource, "PaymentSource Successfully Loaded", Response::HTTP_OK);

    }



    private function createPayment($request){
        $zoneECMI = ZoneECMI::where("MeterNo", $request->MeterNo)->orWhere("AccountNo", $request->account_number)->first();
        $transactionID = StringHelper::generateTransactionReference();
      
        $payment =  PaymentModel::create([
            'email' => $request->email ?? '',
            'transaction_id' => Str::uuid()->toString() ?? $transactionID,
            'phone' => $request->phone ?? '',
            'amount' => $request->amount,
            'account_type' => $request->account_type,
            'account_number' => $request->account_number,
           // 'meter_no' => $request->meter_no,
            'status' => "pending",
            'customer_name' => $zoneECMI->Surname.' '. $zoneECMI->OtherNames,
            'date_entered' => Carbon::now()

        ]);

        return  $response = [
            'payment' => $payment,
            'payment_key_authData' => "G3cf/VTtAHCdHZNxc5GXWRI8z5P0goL2amXWDVFgb6D3XK/QMtZW90TYdl5zffDCNpiZThJzk0+eEU/Y/aYS6fyIOpQZGFrOr8hmvx5869sl2kr5u8qjnM7q5b4ZnTqdKDLtNxr3Qr7anj6YLpox1FOsiyT26mktXL+7SFOaZ15NMtne1z4xrj4R2SndowI/Znsapo7Gfzvp+L7XJyQ8kLYYRk3INjvmRPPQoJg1R0Nnh6EQE3ldIdwylB7GKtr6a71N/yCd4ZtyIcqq1ZNzdWcZyy5eEBAlDIxuECdBqH6hRq2/RbkfARqidNN4Kq0WviSRaRYGbiNjl2W9pNcM8g==",
            'currency' => "NGN",
             "mcode" => "MX19329",
             "pay_item_id" => "Default_Payable_MX19329",

        ];
    }
}





